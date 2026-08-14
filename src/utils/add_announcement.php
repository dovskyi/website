<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

if ($_SESSION["role"] !== 'root'){
        header("Location: /index.php");
        exit();
}

if (isset($_GET["add"])){
        $title = trim($_GET["title"]);
        $content = trim($_GET["content"]);
        $persistent = isset($_GET["persistent"])?1:0;

        $username = !empty($_SESSION["usrname"]) ? $_SESSION["usrname"]: '';
        $user_role = NULL;

        $stmt = $db->prepare("SELECT * FROM users
                WHERE username = :username");

        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $row["user_id"];
        $user_role = $row["role"];

        if ($user_role !== 'root'){
                header("Location: /index.php");
                exit();
        } else {
                $stmt = $db->prepare("INSERT INTO announcements (user_id, title, content, post_date, persistent)
                        VALUES (:user_id, :title, :content, :post_date, :persistent);");

                $stmt->bindParam(":user_id", $user_id);
                $stmt->bindParam(":title", $title);
                $stmt->bindParam(":content", $content);
                $stmt->bindParam(":post_date", date("Y-m-d H:i:s"));
                $stmt->bindParam(":persistent", $persistent);

                $stmt->execute();
                header("Location: /index.php");
                exit();
        }
}

?>

<html>
       <head>
                <title>add announcement</title>
        </head>
        <body>
        <h1>Add announcement</h1>
        <p>Only root:: role has access; if you are not, attempt will be rejected :P</p>
        <form action="/src/utils/add_announcement.php" method="GET">
                <textarea name="title"></textarea><br><br>
                <textarea name="content"></textarea><br>
                <input type="checkbox" name="persistent">1/0</input><br>
                <button type="submit" name="add">Submit</button>
        </form>
        </body> 
</html>
