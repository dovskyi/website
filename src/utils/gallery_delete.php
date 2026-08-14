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
                        $stmt = $db->prepare("SELECT images.path, images.name
                                FROM gallery_entry
                                JOIN gallery_entry_images AS gei ON gallery_entry.entry_id = gei.entry_id 
                                JOIN images ON gei.img_id = images.img_id
                                WHERE gallery_entry.entry_id = :entry_id;");
                        $stmt->bindParam(":entry_id", $entry_id);
                        $stmt->execute();
                        $images_arr = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        $stmt = $db->prepare("SELECT
                                gallery_entry.*,
                                tags_main.*,
                                images_main.img_id AS main_id,
                                images_thumb.img_id AS thumb_id
                                FROM gallery_entry
                                JOIN gallery_entry_images AS gei_main ON gallery_entry.entry_id = gei_main.entry_id
                                JOIN images AS images_main ON gei_main.img_id = images_main.img_id
                                JOIN image_has_tag AS iht_main ON images_main.img_id = iht_main.img_id
                                JOIN tags AS tags_main ON iht_main.tag_id = tags_main.tag_id AND tags_main.tag_id = 1000
                                JOIN gallery_entry_images AS gei_thumb ON gallery_entry.entry_id = gei_thumb.entry_id
                                JOIN images AS images_thumb ON gei_thumb.img_id = images_thumb.img_id
                                JOIN image_has_tag AS iht_thumb ON images_thumb.img_id = iht_thumb.img_id
                                JOIN tags AS tags_thumb ON iht_thumb.tag_id = tags_thumb.tag_id AND tags_thumb.tag_id = 1001
                                WHERE gallery_entry.entry_id = :entry_id;");
                        $stmt->bindParam(":entry_id", $entry_id);
                        $stmt->execute();
                        $image_ids = $stmt->fetch(PDO::FETCH_ASSOC);

                        $main_id = $image_ids["main_id"];
                        $thumb_id = $image_ids["thumb_id"];

                        try {
                                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                $db->beginTransaction();

                                $stmt = $db->prepare("DELETE FROM gallery_entry WHERE gallery_entry.entry_id = :entry_id;");
                                $stmt->bindParam(":entry_id", $entry_id);
                                $stmt->execute();

                                $stmt = $db->prepare("DELETE FROM images WHERE img_id = :main_id OR img_id = :thumb_id;");
                                $stmt->bindParam(":main_id", $main_id);
                                $stmt->bindParam(":thumb_id", $thumb_id);
                                $stmt->execute();

                                $db->commit();
                                echo "Deleted entry";

                        } catch (Exception $e) {
                                $db->rollBack();
                                echo "Failed: " . $e->getMessage();
                                $error = "true";
                        }
                        if (empty($error)) {
                                foreach($images_arr as $img){
                                        $file_ptr = $_SERVER["DOCUMENT_ROOT"] . $img["path"] .$img["name"];
                                        if (!unlink($file_ptr)) {
                                                echo "<br>Could not remove one of the images";
                                        } else {
                                                echo "<br>Removed one of the images";
                                        }
                                }
                        }
                }
        } 
}

?>
<html>
        <head>
                <title>delete gallery entry</title>
        </head>
        <body>
                <h1>Delete Gallery Entry</h1> 
                <form action="" method="post">
                        <button type="submit" name="delete">Delete</button>
                </form>
                <a href="/src/gallery.php">Back</a>
        </body>
