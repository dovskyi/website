<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

if ($_SESSION["role"] !== "root") {
        header("Location: /index.php");
        exit();
}

function resize_image($file, $format, $w, $path) {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        //i only need to scale height, width is always const
        $newheight = $w/$r;
        $newwidth = $w;
        $finish = true;

        global $img_width, $img_height, $thumb_width, $thumb_height;
        $img_width = $width;
        $img_height = $height;
        $thumb_width = $newwidth;
        $thumb_height = $newheight;

        switch($format){
        case "jpg":
                $src = imagecreatefromjpeg($file);
                break;
        case "png":
                $src = imagecreatefrompng($file);
                break;
        case "jpeg":
                $src = imagecreatefromjpeg($file);
                break;
        default:
                $finish = false;
                return $finish;
        }
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        switch($format){
        case "jpg":
                $finish = imagejpeg($dst, $path, 85);
                break;
        case "png":
                $finish = imagepng($dst, $path, 7);
                break;
        case "jpeg":
                $finish = imagejpeg($dst, $path, 85);
                break;
        }
        imagedestroy($src);
        imagedestroy($dst);
        return $finish;
}

$existingTags = [];
$categories = ["medium", "tool", "subject", "type", "size", "misc"];
$pholders = '?' . str_repeat(', ?', count($categories) - 1);

$stmt = $db->prepare("SELECT * FROM tags
        WHERE tags.category IN (".$pholders.");");
$stmt->execute($categories);
$existingTags = $stmt->fetchAll(PDO::FETCH_ASSOC);

$date_reg = '/^([0-9]{2})?[0-9]{2}(-)(1[0-2]|0?[1-9])\2(3[01]|[12][0-9]|0?[1-9])$/';
$error = '';
if (isset($_POST["add"]) && $_FILES["img_file"]["error"] === UPLOAD_ERR_OK){
        //required for entry
        $title = trim($_POST["title"]);
        $content = trim($_POST["content"]);
        $name = basename($_FILES["img_file"]["name"]);
        $path = "/pictures/gallery/";
        //Optional parameters
        $dimensions = empty(trim($_POST["dimensions"]))? NULL: trim($_POST["dimensions"]);
        $creation_date = trim($_POST["creation_date"]);
        if (!preg_match($date_reg, $creation_date)){
                $creation_date = date("Y-m-d");
        }
        $extras = empty(trim($_POST["extras"]))? NULL: trim($_POST["extras"]);
        $location = empty(trim($_POST["location"]))? NULL: trim($_POST["location"]);
        $medium = empty(trim($_POST["medium"]))? NULL: trim($_POST["medium"]);
        //colors for a pattern
        foreach($_POST["colors"] as $color) {
                if (!empty($color)) {
                        $color_arr[] = $color;
                }
        }
        if (count($color_arr) < 1) {
                $color_arr = ["#f9f9f9"];
        }

        //use existing or load new pattern
        $pattern_name = empty(trim($_POST["pattern"]))? "default": trim($_POST["pattern"]);
        $new_pattern = $_POST["svg_string"];
        //if both existing and new pattern, always use new
        if (!empty($new_pattern)) {
                $insert_pattern = true;
                if (!empty($_POST["new_pattern_name"])) {
                        $pattern_name = $_POST["new_pattern_name"];
                } else {
                        $error = "New pattern name not provided";
                }
        } else {
                $insert_pattern = false;
        }

        //full file path
        $file = $_SERVER['DOCUMENT_ROOT'] . $path . $name;

        if (file_exists($file)) {
                $error = "File already exists.";
        }

        $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $allowedType = ["jpg", "png", "jpeg"];
        if(!in_array($imageFileType, $allowedType)) {
                $error = "Only JPG, JPEG, PNG files are allowed.";
        }
        $allowed_mime = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array(mime_content_type($_FILES["img_file"]['tmp_name']), $allowed_mime)) {
                $error = "[Mime] Only JPG, JPEG, PNG files are allowed.";
        }
        if (strlen($name) > 94) {
                $error = "Name too long";
        }

        if ($_FILES["img_file"]["size"] > 3e+7) {
                $error = "File is too large.";
        }

        //get added tags
        $get_tags = $_POST["tags"]??array();
        $tags = array_intersect($get_tags, array_column($existingTags, "tag_id"));
        foreach ($tags as $tag){
                $addTags[] = "(:entry_id, :tag".$tag.")";
                $bindParams[":tag".$tag] = $tag;
        }
        if (!empty($addTags)) {
                $insertString = implode(",", $addTags);
        }

        if (empty($error)){
                if (move_uploaded_file($_FILES["img_file"]["tmp_name"], $file)) {
                        echo htmlspecialchars(basename( $_FILES["img_file"]["name"]))." has been uploaded.";
                        $finish = resize_image($file, $imageFileType, 400, $_SERVER['DOCUMENT_ROOT'] . $path . "thumbs/" . "thumb_" . $name);
                        if ($finish) {
                                echo "<br>Thumbnail has been uploaded.";
                        } else {
                                echo "<br>Error uploading thumbnail.";
                        }
                } else {
                        $error = "Error uploading file.";
                }
        } 

        if (empty($error) && $finish === true){
                try {
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $db->beginTransaction();

                        if ($insert_pattern) {
                                //if new pattern, add it first
                                $stmt = $db->prepare("INSERT INTO patterns (name, htmlstring)
                                        VALUES (:name, :htmlstring);");
                                $stmt->bindParam(":name", $pattern_name);
                                $stmt->bindParam(":htmlstring", $new_pattern);
                                $stmt->execute();
                                $pattern_id = $db->lastInsertId();
                        } else {
                                //else find ID of requested pattern
                                $stmt = $db->prepare("SELECT * from patterns 
                                        WHERE name = :name");
                                $stmt->bindParam(":name", $pattern_name);
                                $stmt->execute();
                                $pattern_id = $stmt->fetch(PDO::FETCH_ASSOC)["pattern_id"];
                                if (empty($pattern_id)) {
                                        //default pattern
                                        $pattern_id = 1000;
                                }
                        }

                        //insert main image
                        $stmt = $db->prepare("INSERT INTO images (name, path, insertion_date, width, height)
                                VALUES (:main_name, :main_path, :insertion_date, :img_width, :img_height);");
                        $stmt->bindParam(":main_name", $name);
                        $stmt->bindParam(":main_path", $path);
                        $stmt->bindValue(":insertion_date", date("Y-m-d"));
                        $stmt->bindParam(":img_width", $img_width);
                        $stmt->bindParam(":img_height", $img_height);

                        $stmt->execute();
                        $main_id = $db->lastInsertId();
                        //insert thumbnail image
                        $stmt = $db->prepare("INSERT INTO images (name, path, insertion_date, width, height)
                                VALUES  (:thumb_name, :thumb_path, :insertion_date, :thumb_width, :thumb_height);");
                        $stmt->bindValue(":thumb_name", "thumb_" . $name);
                        $stmt->bindValue(":thumb_path", $path . "thumbs/");
                        $stmt->bindValue(":insertion_date", date("Y-m-d"));
                        $stmt->bindParam(":thumb_width", $thumb_width);
                        $stmt->bindParam(":thumb_height", $thumb_height);

                        $stmt->execute();
                        $thumb_id = $db->lastInsertId();

                        //insert gallery entry
                        $stmt = $db->prepare("INSERT INTO gallery_entry (title, content, post_date, creation_date, dimensions, location, medium, extras, pattern, colors)
                                VALUES (:title, :content, :post_date, :creation_date, :dimensions, :location, :medium, :extras, :pattern, :colors);");
                        $stmt->bindParam(":title", $title);
                        $stmt->bindParam(":content", $content);
                        $stmt->bindParam(":dimensions", $dimensions);
                        $stmt->bindValue(":post_date", date("Y-m-d"));
                        $stmt->bindParam(":creation_date", $creation_date);
                        $stmt->bindParam(":location", $location);
                        $stmt->bindParam(":medium", $medium);
                        $stmt->bindParam(":extras", $extras);
                        $stmt->bindParam(":pattern", $pattern_id);
                        $stmt->bindValue(":colors", json_encode($color_arr));
                        $stmt->execute();
                        $entry_id = $db->lastInsertId();

                        if (!empty($insertString)) {
                                $stmt = $db->prepare("INSERT INTO gallery_entry_has_tag (entry_id, tag_id) VALUES ".$insertString.";");
                                $stmt->bindParam(":entry_id", $entry_id);
                                foreach ($bindParams as $param => $value) {
                                        $stmt->bindValue($param, $value);
                                }
                                $stmt->execute();
                        }

                        //connect images to the gallery entry
                        $stmt = $db->prepare("INSERT INTO gallery_entry_images (entry_id, img_id)
                                VALUES (:entry_id, :main_id),
                                (:entry_id, :thumb_id);");
                        $stmt->bindParam(":entry_id", $entry_id);
                        $stmt->bindParam(":main_id", $main_id);
                        $stmt->bindParam(":thumb_id", $thumb_id);
                        $stmt->execute();

                        //add tags to connected images 
                        $stmt = $db->prepare("INSERT INTO image_has_tag (img_id, tag_id)
                                VALUES (:main_id, 1000),
                                (:thumb_id, 1001);");
                        $stmt->bindParam(":main_id", $main_id);
                        $stmt->bindParam(":thumb_id", $thumb_id);
                        $stmt->execute();

                        $db->commit();

                        echo "<br>Entry added to database";

                } catch (Exception $e) {
                        $db->rollBack();
                        echo "Failed: " . $e->getMessage();
                }
        }
} 

?>
<html>
        <head>
                <title>add gallery entry</title>
        </head>
        <body>
                <h1>Add Gallery Entry</h1> 
                <form action="" method="post" enctype="multipart/form-data">
                        <p><?php echo $error; $error = '';?></p>
                        <input type="file" name="img_file"><br>
                        <textarea placeholder="title" name="title"></textarea><br>
                        <textarea placeholder="content" name="content"></textarea><br>
                        <textarea placeholder="dimensions" name="dimensions"></textarea><br>
                        <textarea placeholder="medium" name="medium"></textarea><br>
                        <textarea placeholder="location" name="location"></textarea><br>
                        <textarea placeholder="extras" name="extras"></textarea><br>
                        <div id="old_p_container">
                                <span>Reuse pattern:</span><br>
                                <textarea placeholder="pattern" name="pattern"></textarea>
                        </div>
                        <div id="new_p_container" style="display:none;">
                                <span>New pattern:</span><br>
                                <textarea placeholder="html string" name="svg_string"></textarea><br>
                                <textarea placeholder="new pattern name" name="new_pattern_name"></textarea>
                        </div>
                        <button id="toggle_pattern" type="button"><---></button><br><br>
                        <span>Colors:</span>
                        <div id="color_container">
                                <input type="color" name="colors[]" value="#3D3846"></input>
                                <input type="color" name="colors[]" value="#3D3846"></input>
                        </div>
                        <button id="add_color" type="button">+++</button>
                        <button id="remove_color" type="button">---</button><br>

                        <input type="date" name="creation_date"></input><br><br>
                        <button type="submit" name="add">Submit</button><br><br>
                        <?php foreach($categories as $category){ ?>
                                <h3><?php echo $category;?></h3>
                        <?php foreach($existingTags as $tag){ if($tag["category"] === $category) {?>
                        <input type="checkbox" id="<?php echo $tag["tag_id"];?>" name="tags[]" value="<?php echo $tag["tag_id"];?>">
                        <label for="<?php echo $tag["tag_id"];?>"><?php echo $tag["name"];?></label>
                        <?php }}} ?>
                </form>
                <a href="/src/gallery.php">Back</a>

<script>
document.getElementById("add_color").addEventListener("click", function(event) {
        const color_div = document.getElementById("color_container");
        color_div.insertAdjacentHTML("beforeend", "<input type='color' name='colors[]'></input>");
});
document.getElementById("remove_color").addEventListener("click", function(event) {
        const color_div = document.getElementById("color_container");
        if (color_div.childElementCount > 1) {
                color_div.removeChild(color_div.lastElementChild);
        }
});
document.getElementById("toggle_pattern").addEventListener("click", function(event) {
        const new_p = document.getElementById("new_p_container");
        const old_p = document.getElementById("old_p_container");
        if (old_p.style.display === 'none') {
                for (const child of new_p.children) {
                        child.value = '';
                }
                old_p.style.display = '';
                new_p.style.display = 'none';
        } else {
                for (const child of old_p.children) {
                        child.value = '';
                }
                new_p.style.display = '';
                old_p.style.display = 'none';
        }
});
</script>

        </body>
