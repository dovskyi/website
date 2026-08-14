<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

if ($_SESSION["role"] !== "root") {
        header("Location: /index.php");
        exit();
}

function get_value($param, $entry_data) {
        return ($entry_data[$param] !== null)? $entry_data[$param]: '';
}

$existingTags = [];
$categories = ["language", "project_type", "completion_type"];
$pholders = '?' . str_repeat(', ?', count($categories) - 1);

$stmt = $db->prepare("SELECT * FROM tags
        WHERE tags.category IN (".$pholders.");");
$stmt->execute($categories);
$existingTags = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT  tags.* FROM tags
        JOIN article_entry_has_tag aeht ON tags.tag_id = aeht.tag_id
        JOIN article_entry ae ON aeht.entry_id = ae.entry_id
        WHERE ae.entry_id = :entry_id;");
$stmt->bindValue(":entry_id", $_GET["entry_id"]);
$stmt->execute();
$currentTags = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT article_entry.* FROM article_entry
        WHERE article_entry.entry_id = :entry_id");
$stmt->bindValue(":entry_id", $_GET["entry_id"]);
$stmt->execute();

$date_reg = '/^([0-9]{2})?[0-9]{2}(-)(1[0-2]|0?[1-9])\2(3[01]|[12][0-9]|0?[1-9])$/';
$error = '';

if ($stmt->rowCount() !== 1){
        $error = empty($error)? "Could not find the entry": $error . "<br>Could not find the entry";
}

$entry_data = $stmt->fetch(PDO::FETCH_ASSOC);
$entry_id = $_GET["entry_id"];

if (isset($_POST["add"])&&empty($error)){
        //required for entry
        $title = trim($_POST["title"]);
        $content = trim($_POST["content"]);
        $abstract = trim($_POST["abstract"]);
        $brief = trim($_POST["brief"]);

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

                        //insert article entry
                        $stmt = $db->prepare("UPDATE article_entry SET title=:title, content=:content, abstract=:abstract, brief=:brief, modify_date=:modify_date
                                WHERE entry_id=:entry_id;");
                        $stmt->bindParam(":title", $title);
                        $stmt->bindParam(":content", $content);
                        $stmt->bindParam(":abstract", $abstract);
                        $stmt->bindParam(":brief", $brief);
                        $stmt->bindParam(":entry_id", $entry_id);
                        $stmt->bindValue(":modify_date", date("Y-m-d"));
                        $stmt->execute();

                        //updated tags
                        if (!empty($insertString)) {
                                $stmt = $db->prepare("INSERT INTO article_entry_has_tag (entry_id, tag_id) VALUES ".$insertString.";");
                                $stmt->bindParam(":entry_id", $_GET["entry_id"]);
                                foreach ($bindAddParams as $param => $value) {
                                        $stmt->bindValue($param, $value);
                                }
                                $stmt->execute();
                        }
                        if (!empty($removeString)) {
                                $stmt = $db->prepare("DELETE FROM article_entry_has_tag WHERE entry_id = ".$_GET["entry_id"]." AND tag_id IN ".$removeString.";");
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
                <title>edit article entry</title>
        </head>
        <body>
                <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
                <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>

                <h1>Edit Article Entry</h1> 
                <form action="" method="post" enctype="multipart/form-data">
                        <p><?php echo $error; $error = '';?></p>
                        <textarea placeholder="title" name="title"><?php echo get_value("title", $entry_data);?></textarea><br>
                        <textarea placeholder="brief" name="brief"><?php echo get_value("brief", $entry_data);?></textarea><br>
                        <textarea placeholder="abstract" name="abstract"><?php echo get_value("abstract", $entry_data);?></textarea><br>
                        <textarea id="content_div" placeholder="content" name="content"><?php echo htmlspecialchars(get_value("content", $entry_data), ENT_QUOTES, 'UTF-8');?></textarea>


                        <button type="submit" name="add">Submit</button><br><br>

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
