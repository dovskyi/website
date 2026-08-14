<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

if ($_SESSION["role"] !== "root") {
        header("Location: /index.php");
        exit();
}
$entry_id = $_GET["entry_id"];

if (isset($_POST["delete"])){
        $username = !empty($_SESSION["usrname"])? $_SESSION["usrname"]: '';

        if (!empty($username)){
                $stmt = $db->prepare("SELECT * FROM users
                        WHERE username = :username;");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $role = $row["role"];

                if ($role === 'root'){
                        $stmt = $db->prepare("SELECT images.path, images.name, images.img_id
                                FROM article_entry
                                JOIN article_entry_images AS aei ON article_entry.entry_id = aei.entry_id 
                                JOIN images ON aei.img_id = images.img_id
                                WHERE article_entry.entry_id = :entry_id;");
                        $stmt->bindParam(":entry_id", $entry_id);
                        $stmt->execute();
                        if ($stmt->rowCount() > 0) {
                                $images_arr = $stmt->fetch(PDO::FETCH_ASSOC);
                                $main_id = $images_arr["img_id"];
                        }

                        try {
                                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $db->beginTransaction();

                                $stmt = $db->prepare("DELETE FROM article_entry WHERE article_entry.entry_id = :entry_id;");
                                $stmt->bindParam(":entry_id", $entry_id);
                                $stmt->execute();
                                if (!empty($images_arr)) {
                                        $stmt = $db->prepare("DELETE FROM images WHERE img_id = :main_id"); 
                                        $stmt->bindParam(":main_id", $main_id);
                                        $stmt->execute();
                                }

                                $db->commit();
                                echo "Deleted entry";

                        } catch (Exception $e) {
                                $db->rollBack();
                                echo "Failed: " . $e->getMessage();
                                $error = "true";
                        }
                        if (empty($error) && !empty($images_arr)) {
                                $file_ptr = $_SERVER["DOCUMENT_ROOT"] . $images_arr["path"] .$images_arr["name"];
                                if (!unlink($file_ptr)) {
                                        echo "<br>Could not remove one of the images";
                                } else {
                                        echo "<br>Removed one of the images";
                                }
                        }
                }
        } 
}

?>
<html>
        <head>
                <title>delete article entry</title>
        </head>
        <body>
                <h1>Delete Article Entry</h1> 
                <form action="" method="post">
                        <button type="submit" name="delete">Delete</button>
                </form>
                <a href="/src/projects.php">Back</a>
        </body>
