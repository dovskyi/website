<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

$entry_id = (int)$data;

$error = true;

try {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->beginTransaction();

        $stmt = $db->prepare("SELECT article_entry.*, images.path, images.name
                FROM article_entry
                LEFT JOIN article_entry_images AS aei ON article_entry.entry_id = aei.entry_id
                LEFT JOIN images ON aei.img_id = images.img_id
                LEFT JOIN image_has_tag AS iht ON images.img_id = iht.img_id
                LEFT JOIN tags ON iht.tag_id = tags.tag_id AND tags.tag_id = 1000
                WHERE article_entry.entry_id = :entry_id");
        $stmt->bindParam(':entry_id', $entry_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
                $entry = $stmt->fetch(PDO::FETCH_ASSOC);
                $error = false;
        }

        $stmt = $db->prepare("SELECT tags.name FROM article_entry ae
                JOIN article_entry_has_tag aeht ON ae.entry_id = aeht.entry_id 
                JOIN tags ON aeht.tag_id = tags.tag_id
                WHERE ae.entry_id = :entry_id AND tags.category IN ('language', 'project_type', 'completion_type');");
        $stmt->bindParam(":entry_id", $entry_id);
        $stmt->execute();
        $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $db->commit();

} catch (Exception $e) {
        $db->rollBack();
        echo "Error: " . $e->getMessage();
}

if (!$error) {
        $response = ["error" => $error,
                "title" => $entry["title"],
                "brief" => $entry["brief"],
                "content" => $entry["content"],
                "abstract" => $entry["abstract"],
                "post_date" => $entry["post_date"],
                "modify_date" => $entry["modify_date"],
                "tags" => $tags,
                "image" => ($entry["name"] !== NULL)? $entry["path"].$entry["name"]: NULL
        ];
} else {
        $response = ["error" => $error];
}

header('Content-Type: application/json');
echo json_encode($response);
exit;

?>
