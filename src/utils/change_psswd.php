<!DOCTYPE html>
<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/init.php';

if ($_SERVER["REQUEST_METHOD"] == "POST"){
        $filter_psswd= "/^[A-Za-z0-9_\.\$#!%&?]+$/";

        $password = $_POST["new_psswd"] ?? '';
        $password_old = $_POST["old_psswd"] ?? '';

        $psswd_length = strlen($password);
        
        if (!strcmp($password, $password_old)){
                $_SESSION["account_error"] = "New password can't be the same as old.";
                header("Location: /src/utils/change_psswd.php");
                exit();
        }
        else if ($psswd_length < 5 || $psswd_length > 20){
                $_SESSION["account_error"] = "New password must be between 5 to 20 chars.";
                header("Location: /src/utils/change_psswd.php");
                exit();
        }
        else if (!preg_match($filter_psswd, $password_old) || !preg_match($filter_psswd, $password)){
                $_SESSION["account_error"] = "Password chars: a-z, 0-9, ._$#%&?";
                header("Location: /src/utils/change_psswd.php");
                exit();
        }
        else {
                $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
                $stmt->bindParam(':username', $_SESSION["usrname"]);
                $stmt->execute();

                if($stmt->rowCount() <= 0){
                        $_SESSION["account_error"] = "Username does not exist (what?)";
                        header("Location: /src/utils/change_psswd.php");
                        exit();
                }
                else {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (password_verify($password_old . $row["salt"], $row["password"])){
                                $salt = (string)rand(10000, 99999);
                                $password = password_hash($password . $salt, PASSWORD_DEFAULT);

                                $stmt = $db->prepare("UPDATE users 
                                                      SET salt = :salt,
                                                          password = :password
                                                      WHERE username = :username");
                                $stmt->bindParam(':username', $_SESSION["usrname"]);
                                $stmt->bindParam(':salt', $salt);
                                $stmt->bindParam(':password', $password);

                                $stmt->execute();

                                header("Location: /src/utils/logout.php");
                                exit();
                        }
                        else {
                                $_SESSION["account_error"] = "Incorrect password for " . $_SESSION["usrname"];
                                header("Location: /src/utils/change_psswd.php");
                                exit();
                        }

                }
        }
}
?>

<html>
        <head>
                <title>Change password-Dovskyi</title>
        </head>
        <body>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>
                <div class="container">
                        <div class="row">
                        <div class="col2">
                        <div class="container">
                        <form action="/src/utils/change_psswd.php" method="post">
                        <h2 class="section_header"><?php
                                echo "Password change: " . $_SESSION["usrname"];
                                ?> 
                                </h2>
                                <label><p class="p_defined"><b>Current password:</b></p></label>
                                <div class="row">
                                        <input id="old_password" class="well" type="password" name="old_psswd" minlength="5" maxlength="20" required>
                                        <button type="button" class="psswd_toggle" onclick="password_toggle(this, 'old_password')">&#9675;</button>
                                </div>
                                <label><p class="p_defined"><b>New password:</b></p></label>
                                <div class="row">
                                        <input id="new_password" class="well" type="password" name="new_psswd" minlength="5" maxlength="20" required>
                                        <button type="button" class="psswd_toggle" onclick="password_toggle(this, 'new_password')">&#9675;</button>
                                </div>
                                <label><p class="p_defined"><b>You will be logged out!</b></p></label>
                                <button class="button_user" name="create_button"><p>Change</p></button>
                        </form>
                        <div class="row"><p class="error_text"><?php
                                echo $_SESSION["account_error"];
                                $_SESSION["account_error"] = "";
                                ?></p>
                        </div>
                        </div>
                        <script src="/js/sanitize_input.js?v=0.9"></script>
                        <script src="/js/password_toggle.js?v=0.6"></script>
                        <script>
                                sanitize_psswd('old_password');
                                sanitize_psswd('new_password');
                        </script>
                        </div>
                        <div class="col1"><img class="confectioner" src="/misc/static/confectioner_psswdchange.png?v=1"></div>
                        </div>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
</html>
