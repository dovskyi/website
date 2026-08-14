<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

if ($_SESSION["role"] !== "root") {
        header("Location: /index.php");
        exit();
}

$date_reg = '/^([0-9]{2})?[0-9]{2}(-)(1[0-2]|0?[1-9])\2(3[01]|[12][0-9]|0?[1-9])$/';
$error = '';

$existingTags = [];
$categories = ["medium", "tool", "subject", "type", "size", "misc"];
$pholders = '?' . str_repeat(', ?', count($categories) - 1);

$stmt = $db->prepare("SELECT * FROM tags
        WHERE tags.category IN (".$pholders.");");
$stmt->execute($categories);
$existingTags = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT  tags.* FROM tags
        JOIN gallery_entry_has_tag geht ON tags.tag_id = geht.tag_id 
        JOIN gallery_entry ge ON geht.entry_id = ge.entry_id
        WHERE ge.entry_id = :entry_id;");
$stmt->bindValue(":entry_id", $_GET["entry_id"]);
$stmt->execute();
$currentTags = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT
        gallery_entry.*,
        tags_main.*,
        images_main.*,
        patterns.name AS pattern_name,
        images_thumb.path AS thumb_path,
        images_thumb.name AS thumb_name
        FROM gallery_entry
        JOIN patterns ON gallery_entry.pattern = patterns.pattern_id
        JOIN gallery_entry_images AS gei_main ON gallery_entry.entry_id = gei_main.entry_id
        JOIN images AS images_main ON gei_main.img_id = images_main.img_id
        JOIN image_has_tag AS iht_main ON images_main.img_id = iht_main.img_id
        JOIN tags AS tags_main ON iht_main.tag_id = tags_main.tag_id AND tags_main.tag_id = 1000
        JOIN gallery_entry_images AS gei_thumb ON gallery_entry.entry_id = gei_thumb.entry_id
        JOIN images AS images_thumb ON gei_thumb.img_id = images_thumb.img_id
        JOIN image_has_tag AS iht_thumb ON images_thumb.img_id = iht_thumb.img_id
        JOIN tags AS tags_thumb ON iht_thumb.tag_id = tags_thumb.tag_id AND tags_thumb.tag_id = 1001
        WHERE gallery_entry.entry_id = :entry_id;");
$stmt->bindValue(":entry_id", $_GET["entry_id"]);
$stmt->execute();

if ($stmt->rowCount() !== 1){
        $error = empty($error)? "Could not find the entry": $error . "<br>Could not find the entry";
}

$entry_data = $stmt->fetch(PDO::FETCH_ASSOC);

function get_value($param, $entry_data) {
        return ($entry_data[$param] !== null)? $entry_data[$param]: '';
}
if (isset($_POST["add"]) && empty($error)){
        //required for entry
        $title = trim($_POST["title"]);
        $content = trim($_POST["content"]);
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
        //use existing or load new pattern
        $pattern_name = empty(trim($_POST["pattern"]))? NULL: trim($_POST["pattern"]);
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

        //tags to add/delete
        $get_tags = $_POST["tags"]??array();
        $allTags = array_intersect($get_tags, array_column($existingTags, "tag_id"));
        $newTags = array_diff($allTags, array_column($currentTags, "tag_id"));
        $oldTags = array_diff(array_column($currentTags, "tag_id"), $allTags);
        foreach ($newTags as $tag){
                $addTags[] = "(:entry_id, :tag".$tag.")";
                $bindAddParams[":tag".$tag] = $tag;
        }
        if (!empty($addTags)) {
                $insertString = implode(",", $addTags);
        }
        foreach($oldTags as $tag) {
                $removeTags[] = "?";
                $bindRemoveParams[] = $tag;
        }
        if (!empty($removeTags)) {
                $removeString = "(".implode(",", $removeTags).")";
        }
        echo "new: <br>";
        foreach($newTags as $tag) {
                echo $tag."<br>";
        }
        echo "old: <br>";
        foreach($oldTags as $tag) {
                echo $tag."<br>";
        }


        if (empty($error)){
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
                        //update gallery entry
                        $stmt = $db->prepare("UPDATE gallery_entry 
                                SET title = :title, content = :content, post_date = :post_date, creation_date = :creation_date, 
                                dimensions = :dimensions, location = :location, medium = :medium, extras = :extras, pattern = :pattern, colors = :colors
                                WHERE entry_id = :entry_id;");
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
                        $stmt->bindValue(":entry_id", $_GET["entry_id"]);
                        $stmt->execute();

                        if (!empty($insertString)) {
                                $stmt = $db->prepare("INSERT INTO gallery_entry_has_tag (entry_id, tag_id) VALUES ".$insertString.";");
                                $stmt->bindParam(":entry_id", $_GET["entry_id"]);
                                foreach ($bindAddParams as $param => $value) {
                                        $stmt->bindValue($param, $value);
                                }
                                $stmt->execute();
                        }
                        if (!empty($removeString)) {
                                $stmt = $db->prepare("DELETE FROM gallery_entry_has_tag WHERE entry_id = ".$_GET["entry_id"]." AND tag_id IN ".$removeString.";");
                                $stmt->execute($bindRemoveParams);
                        }

                        $db->commit();

                        echo "<br>Entry updated";

                } catch (Exception $e) {
                        $db->rollBack();
                        echo "Failed: " . $e->getMessage();
                }
        }
} 

?>
<html>
        <head>
                <title>edit gallery entry</title>
        </head>
        <body>
<script>
let opt = null;
</script>
<h1>Edit Gallery Entry</h1> 
<form action="" method="post" enctype="multipart/form-data">
        <p><?php echo $error; $error = '';?></p>
        <textarea placeholder="title" name="title"><?php echo get_value("title", $entry_data);?></textarea><br>
        <textarea placeholder="content" name="content"><?php echo get_value("content", $entry_data);?></textarea><br>
        <textarea placeholder="dimensions" name="dimensions"><?php echo get_value("dimensions", $entry_data);?></textarea><br>
        <textarea placeholder="medium" name="medium"><?php echo get_value("medium", $entry_data);?></textarea><br>
        <textarea placeholder="location" name="location" ><?php echo get_value("location", $entry_data);?></textarea><br>
        <textarea placeholder="extras" name="extras"><?php echo get_value("extras", $entry_data);?></textarea><br>
        <div id="old_p_container">
                <span>Reuse pattern:</span><br>
                <textarea placeholder="pattern" name="pattern"><?php echo get_value("pattern_name", $entry_data);?></textarea>
        </div>
        <div id="new_p_container" style="display:none;">
                <span>New pattern:</span><br>
                <textarea placeholder="html string" name="svg_string"></textarea><br>
                <textarea placeholder="new pattern name" name="new_pattern_name"></textarea>
        </div>
        <button id="toggle_pattern" type="button"><---></button><br><br>
        <span>Colors:</span>
        <div id="color_container">
<?php $color_array = json_decode($entry_data["colors"]);
foreach ($color_array as $color) { ?>
<input type="color" name="colors[]" value="<?php echo $color; ?>"></input>
<?php } ?>
        </div>
        <button id="add_color" type="button">+++</button>
        <button id="remove_color" type="button">---</button><br>

        <input type="date" name="creation_date" value="<?php echo get_value("creation_date", $entry_data);?>"></input><br><br>
        <button type="submit" name="add">Submit</button>
        <?php foreach($categories as $category){ ?>
        <h3><?php echo $category;?></h3>
        <?php foreach($existingTags as $tag){ if($tag["category"] === $category) {?>
        <input type="checkbox" id="<?php echo $tag["tag_id"];?>" name="tags[]" value="<?php echo $tag["tag_id"];?>">
        <label for="<?php echo $tag["tag_id"];?>"><?php echo $tag["name"];?></label>
        <script>
        opt = document.getElementById("<?php echo $tag["tag_id"];?>")
        opt.checked = <?php echo in_array($tag["tag_id"], array_column($currentTags, "tag_id"))? "true": "false"; ?>;
</script>

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
        if (color_div.childElementCount > 2) {
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
