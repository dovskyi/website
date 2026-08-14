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

        global $img_width, $img_height;
        $img_width = $newwidth;
        $img_height = $newheight;

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
$categories = ["language"];
$pholders = '?' . str_repeat(', ?', count($categories) - 1);

$stmt = $db->prepare("SELECT * FROM tags
        WHERE tags.category IN (".$pholders.");");
$stmt->execute($categories);
$existingTags = $stmt->fetchAll(PDO::FETCH_ASSOC);

$date_reg = '/^([0-9]{2})?[0-9]{2}(-)(1[0-2]|0?[1-9])\2(3[01]|[12][0-9]|0?[1-9])$/';
$error = '';

if (isset($_POST["add"])){
        //required for entry
        $title = trim($_POST["title"]);
        $content = trim($_POST["content"]);
        $abstract = trim($_POST["abstract"]);
        $brief = trim($_POST["brief"]);
        //Optional parameters
        if ($_FILES['img_file']['error'] === UPLOAD_ERR_OK && $_FILES['img_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $image = $_FILES['img_file'];
                $name = basename($image["name"]);
                $path = "/pictures/projects/";
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path . $name;
                if (file_exists($fullPath)) {
                        $error = "File already exists.";
                }
                $imageFileType = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                $allowedType = ["jpg", "png", "jpeg"];
                if(!in_array($imageFileType, $allowedType)) {
                        $error = "Only JPG, JPEG, PNG files are allowed.";
                }
                $allowed_mime = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!in_array(mime_content_type($image['tmp_name']), $allowed_mime)) {
                        $error = "[Mime] Only JPG, JPEG, PNG files are allowed.";
                }
                if (strlen($name) > 94) {
                        $error = "Name too long";
                }

                if ($image["size"] > 3e+7) {
                        $error = "File is too large.";
                }
                if (empty($error)){
                        $finish = resize_image($image["tmp_name"], $imageFileType, 300, $fullPath);
                        echo $fullPath;
                        if ($finish) {
                                echo "<br>Image has been uploaded.";
                                $imgUpload = true;
                        } else {
                                $error = "<br>Error uploading Image.";
                        }
                } 
        } else {
                $imgUpload = false;
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
                try {
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $db->beginTransaction();

                        //insert article entry
                        $stmt = $db->prepare("INSERT INTO article_entry (title, content, abstract, brief, post_date, modify_date)
                                VALUES (:title, :content, :abstract, :brief, :post_date, :modify_date);");
                        $stmt->bindParam(":title", $title);
                        $stmt->bindParam(":content", $content);
                        $stmt->bindParam(":abstract", $abstract);
                        $stmt->bindParam(":brief", $brief);
                        $stmt->bindValue(":post_date", date("Y-m-d"));
                        $stmt->bindValue(":modify_date", date("Y-m-d"));
                        $stmt->execute();
                        $entry_id = $db->lastInsertId();

                        //insert article tags
                        if (!empty($insertString)) {
                                $stmt = $db->prepare("INSERT INTO article_entry_has_tag (entry_id, tag_id) VALUES ".$insertString.";");
                                $stmt->bindParam(":entry_id", $entry_id);
                                foreach ($bindParams as $param => $value) {
                                        $stmt->bindValue($param, $value);
                                }
                                $stmt->execute();
                        }

                        //insert main image
                        if ($imgUpload) {
                                $stmt = $db->prepare("INSERT INTO images (name, path, insertion_date, width, height)
                                        VALUES (:name, :path, :insertion_date, :img_width, :img_height);");
                                $stmt->bindParam(":name", $name);
                                $stmt->bindParam(":path", $path);
                                $stmt->bindValue(":insertion_date", date("Y-m-d"));
                                $stmt->bindParam(":img_width", $img_width);
                                $stmt->bindParam(":img_height", $img_height);

                                $stmt->execute();
                                $img_id = $db->lastInsertId();

                                //connect images to the gallery entry
                                $stmt = $db->prepare("INSERT INTO article_entry_images (entry_id, img_id)
                                        VALUES (:entry_id, :img_id);");
                                $stmt->bindParam(":entry_id", $entry_id);
                                $stmt->bindParam(":img_id", $img_id);
                                $stmt->execute();

                                //add tags to connected image
                                $stmt = $db->prepare("INSERT INTO image_has_tag (img_id, tag_id)
                                        VALUES (:img_id, 1001);");
                                $stmt->bindParam(":img_id", $img_id);
                                $stmt->execute();
                        }

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
                <title>add article entry</title>
        </head>
        <body>
                <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
                <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>

                <h1>Add Article Entry</h1> 
                <form action="" method="post" enctype="multipart/form-data">
                        <p><?php echo $error; $error = '';?></p>
                        <input type="file" name="img_file"><br>
                        <textarea placeholder="title" name="title"></textarea><br>
                        <textarea placeholder="brief" name="brief"></textarea><br>
                        <textarea placeholder="abstract" name="abstract"></textarea><br>
                        <textarea id="content_div" placeholder="content" name="content"></textarea><br>


                        <button type="submit" name="add">Submit</button><br><br>

                        <?php foreach($categories as $category){ ?>
                        <h3><?php echo $category;?></h3>
                        <?php foreach($existingTags as $tag){ if($tag["category"] === $category) {?>
                        <input type="checkbox" id="<?php echo $tag["tag_id"];?>" name="tags[]" value="<?php echo $tag["tag_id"];?>">
                        <label for="<?php echo $tag["tag_id"];?>"><?php echo $tag["name"];?></label>
                        <?php }}} ?>
                </form>
                <a href="/src/projects.php">Back</a>
<script>
const easyMDE = new EasyMDE({
element: document.getElementById('content_div'),
        spellChecker: true,
        minHeight: "70px",
        maxHeight: "200px",
        placeholder: 'content',
        forceSync: true
});
</script>

        </body>
