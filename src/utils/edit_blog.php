<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/Parsedown.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/ParsedownMath.php';

$Parsedown = new ParsedownMath([
    'math' => [
        'enabled' => true
    ]
]);

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

$title = trim($data["title"]);
$content = trim($data["content"]);
$blog_id = (int)$data["blog_id"];
$username = !empty($_SESSION["usrname"])? $_SESSION["usrname"]: '';
$error = 'true';

$title_length = strlen($title);
$content_length = strlen($content);

if ($title_length <= 0 || $title_length > 200 || $content_length <= 0 || $content_length > 4000) {
        $error = "Title can't be more than 200 chars.<br>Content can't be more than 4000 chars.";
}
if (!empty($username)){
        $stmt = $db->prepare("SELECT * FROM users
                              WHERE username = :username;");

        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $role = $row["role"];

        $stmt = $db->prepare("SELECT blogs.blog_id, users.username, users.role
                              FROM blogs
                              INNER JOIN users ON blogs.user_id = users.user_id
                              WHERE blogs.blog_id = :blog_id;");
        $stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($username === $row["username"] || $role === 'root'){
                        $stmt = $db->prepare("UPDATE blogs
                                              SET title = :title,
                                                  content = :content,
                                                  modify_date = :modify_date
                                              WHERE blog_id = :blog_id;");
                        $stmt->bindParam(':title', $title);
                        $stmt->bindParam(':content', $content);
                        $stmt->bindValue(':modify_date', date("Y-m-d H:i:s"));
                        $stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $error = NULL;
                }
                else {
                        $error = "Could not verify user";
                }
        }
}


$response = [
        "error" => $error,
        "content" => $Parsedown->text($content),
        "moddate" => date("Y-m-d H:i:s")];

header('Content-Type: application/json');
echo json_encode($response);
exit;

?>
