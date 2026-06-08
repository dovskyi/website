<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

$blog_id = (int)$data["blog_id"];

//we have a guilty until proven innocent mentality here
$error = true;
$title = NULL;
$content = NULL;

$stmt = $db->prepare("SELECT * FROM blogs
                      WHERE blog_id = :blog_id");
$stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $title = $row["title"];
        $content = $row["content"];
        $error = false;
}

$response = ["error" => $error,
             "title" => $title,
             "content" => $content
];

header('Content-Type: application/json');
echo json_encode($response);
exit;

?>
