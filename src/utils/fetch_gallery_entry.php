<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

$entry_id = (int)$data;

//we have a guilty until proven innocent mentality here
$error = true;

$stmt = $db->prepare("SELECT tags.*, gallery_entry.*, images.*
        FROM gallery_entry_images AS gei
        JOIN images ON gei.img_id = images.img_id
        JOIN image_has_tag AS iht ON images.img_id = iht.img_id
        JOIN tags ON iht.tag_id = tags.tag_id
        JOIN gallery_entry ON gei.entry_id = gallery_entry.entry_id 
        WHERE gei.entry_id = :entry_id AND tags.tag_id = 1000;");

$stmt->bindParam(':entry_id', $entry_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $error = false;
}

$response = ["error" => $error,
        "title" => $row["title"],
        "content" => $row["content"],
        "dimensions" => $row["dimensions"],
        "post_date" => $row["post_date"],
        "creation_date" => $row["creation_date"],
        "width" => $row["width"],
        "height" => $row["height"],
        "name" => $row["name"],
        "path" => $row["path"],
        "medium" => $row["medium"],
        "img_location" => $row["location"],
        "extras" => $row["extras"]
];

header('Content-Type: application/json');
echo json_encode($response);
exit;

?>
