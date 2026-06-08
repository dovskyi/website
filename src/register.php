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
        $_SESSION["account_creation"] = $_SESSION["account_creation"] ?? 0;

        if ($_SESSION["account_creation"] >= 2){
                $_SESSION["account_error"] = "You can only create 2 accounts per session.<br>..unless you clear cookies, then I cant do anything :(";
                header("Location: /src/register.php");
                exit();
        }
        else if ($usr_length < 3 || $usr_length > 20 || $psswd_length < 5 || $psswd_length > 20){
                $_SESSION["account_error"] = "Username must be between 3 to 20 chars.<br>Password must be between 5 to 20 chars.";
                header("Location: /src/register.php");
                exit();
        }
        else if (!preg_match($filter_usrn, $username) || !preg_match($filter_psswd, $password)){
                $_SESSION["account_error"] = "Username chars: a-z, 0-9, ._<br>Password chars: a-z, 0-9, ._$#%&?";
                header("Location: /src/register.php");
                exit();
        }
        else {  
                $stmt = $db->prepare("SELECT username FROM users WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();

                if($stmt->rowCount() > 0){
                        $_SESSION["account_error"] = "Username already exists";
                        header("Location: /src/register.php");
                        exit();
                }
                else {
                        $_SESSION["account_creation"] += 1;

                        $salt = (string)rand(10000, 99999);

                        $password = password_hash($password . $salt, PASSWORD_DEFAULT);

                        $stmt = $db->prepare("INSERT INTO users (username, password, salt, role, create_date)
                                VALUES (:username, :password, :salt, :role, :create_date)");
                        $stmt->bindParam(':username', $username);
                        $stmt->bindParam(':password', $password);
                        $stmt->bindParam(':salt', $salt);
                        $stmt->bindValue(':role', 'user');
                        $stmt->bindValue(':create_date', date('Y-m-d'));

                        $stmt->execute();

                        header("Location: /src/login.php");
                        exit();
                }
        }
}
?>

<html>
        <head>
                <title>Create account-Dovskyi</title>
        </head>
        <body>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>
                <div class="container">
                        <div class="row">
                        <div class="col2">
                        <form action="/src/register.php" method="post">
                                <h2 class="text2">Create Account</h2>
                                <label><p class="p_defined"><b>Username:</b></p></label>
                                <input id="input_usrname" class="well" type="text" name="usrname" minlength="3" maxlength="20" required>
                                <label><p class="p_defined"><b>Password:</b></p></label>
                                <div class="row">
                                        <input id="input_psswd" class="well" type="password" name="usr_psswd" minlength="5" maxlength="20" required>
                                        <button type="button" class="psswd_toggle" onclick="password_toggle(this, 'input_psswd')">&#9675;</button>
                                </div>
                                <button type="submit" class="button_user" name="create_button"><p>Create</p></button>
                        </form>
                        <div class="row"><p class="error_text"><?php 
echo $_SESSION["account_error"];
$_SESSION["account_error"] = "";
?></p>
                        </div>
                        <script src="/js/sanitize_input.js?v=0.10"></script>
                        <script src="/js/password_toggle.js?v=0.7"></script>
<script>
sanitize('input_usrname');
sanitize_psswd('input_psswd');
</script>
                        </div>
                        <div class="col1"><img class="confectioner" src="/misc/static/confectioner_register.png?v=2"></div>
                        </div>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
</html>
