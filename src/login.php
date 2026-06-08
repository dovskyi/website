<!DOCTYPE html>
<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/init.php';

if ($_SERVER["REQUEST_METHOD"] == "POST"){
        $filter_usrn = "/^[A-Za-z0-9_\.]+$/";
        $filter_psswd= "/^[A-Za-z0-9_\.\$#!%&?]+$/";

        $username = strtolower(trim($_POST["usrname"] ?? ''));
        $password = trim($_POST["usr_psswd"] ?? '');

        $usr_length = strlen($username);
        $psswd_length = strlen($password);

        if ($usr_length < 3 || $usr_length > 20 || $psswd_length < 5 || $psswd_length > 20){
                $_SESSION["account_error"] = "Username or password parameters are incorrect";
                header("Location: /src/login.php");
                exit();
        }
        else if (!preg_match($filter_usrn, $username) || !preg_match($filter_psswd, $password)){
                $_SESSION["account_error"] = "Username or password parameters are incorrect";
                header("Location: /src/login.php");
                exit();
        }
        else {
                $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();

                if($stmt->rowCount() <= 0){
                        $_SESSION["account_error"] = "Username does not exist";
                        header("Location: /src/login.php");
                        exit();
                }
                else {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (password_verify($password . $row["salt"], $row["password"])){
                                $_SESSION["usrname"] = $username;
                                $_SESSION["role"] = $row["role"];
                                header("Location: /index.php");
                                exit();
                        }
                        else {
                                $_SESSION["account_error"] = "Incorrect password for " . $username;
                                header("Location: /src/login.php");
                                exit();
                        }

                }
        }
}
?>

<html>
        <head>
                <title>Login-Dovskyi</title>
        </head>
        <body>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>
                <div class="container">
                        <div class="row">
                        <div class="col2">
                        <div class="container">
                        <form action="/src/login.php" method="post">
                                <h2 class="text2">Log in</h2>
                                <label><p class="p_defined"><b>Username:</b></p></label>
                                <input id="input_usrname" class="well" type="text" name="usrname" minlength="3" maxlength="20" required>
                                <label><p class="p_defined"><b>Password:</b></p></label>
                                <div class="row">
                                        <input id="input_psswd" class="well" type="password" name="usr_psswd" minlength="5" maxlength="20" required>
                                        <button type="button" class="psswd_toggle" onclick="password_toggle(this, 'input_psswd')">&#9675;</button>
                                </div>
                                <button class="button_user" name="create_button"><p>Log in</p></button>
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
                                sanitize('input_usrname');
                                sanitize_psswd('input_psswd');
                        </script>
                        <div class="container" style="margin: auto;">
                                <p><b>Don't have an account?</b></p>
                                <button class="button_user"><a href="/src/register.php"><p>Create account</p></a></button>
                        </div>
                        </div>
                        <div class="col1"><img class="confectioner" src="/misc/static/confectioner_login.png?v=3"></div>
                        </div>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
</html>
