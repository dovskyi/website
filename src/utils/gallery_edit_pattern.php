<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

if ($_SESSION["role"] !== "root") {
        header("Location: /index.php");
        exit();
}

$error = '';
$stmt = $db->prepare("SELECT * FROM patterns WHERE pattern_id = :pattern_id;");
$stmt->bindValue(":pattern_id", $_GET["pattern_id"]);
$stmt->execute();
if ($stmt->rowCount() !== 1){
        $error = empty($error)? "Could not find the pattern": $error . "<br>Could not find the pattern";
}

$pattern_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (isset($_POST["add"]) && empty($error)){
        $new_pattern = $_POST["svg_string"];
        if (!empty($new_pattern)) {
                if (!empty($_POST["new_pattern_name"])) {
                        $pattern_name = $_POST["new_pattern_name"];
                } else {
                        $error = "New/old pattern name not provided";
                }
        } else {
                $error = "Empty pattern";
        }

        if (empty($error)){
                try {
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $db->beginTransaction();

                        $stmt = $db->prepare("UPDATE patterns
                                SET name = :name, htmlstring = :htmlstring WHERE pattern_id = :pattern_id;");
                        $stmt->bindParam(":name", $pattern_name);
                        $stmt->bindParam(":htmlstring", $new_pattern);
                        $stmt->bindParam(":pattern_id", $_GET["pattern_id"]);
                        $stmt->execute();

                        $db->commit();
                        echo "Updated pattern";

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
        <h1>Edit Gallery Entry</h1> 
        <form action="" method="post">
                <p><?php echo $error; $error = '';?></p>
                <textarea placeholder="html string" name="svg_string"><?php echo $pattern_data["htmlstring"];?></textarea><br>
                <textarea placeholder="new pattern name" name="new_pattern_name"><?php echo $pattern_data["name"];?></textarea>
                </div>
                <button type="submit" name="add">Submit</button>
        </form>
        <a href="/src/gallery.php">Back</a>
</body>
