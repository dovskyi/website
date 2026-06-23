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

$content = trim($data["content"]);
$blog_id = $data["blog_id"];

$content_length = strlen($content);

$username = !empty($_SESSION["usrname"]) ? $_SESSION["usrname"]: "anonym";
$user_role = NULL;

if ($content_length <= 0 || $content_length > 1000) {
        $error = "Content can't be more than 1000 chars.";
}
else {
        $stmt = $db->prepare("SELECT * FROM users
                              WHERE username = :username");

        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $user_id = $row["user_id"];
                $user_role = $row["role"];

                $stmt = $db->prepare("INSERT INTO comments (blog_id, user_id, content, post_date)
                                      VALUES (:blog_id, :user_id, :content, :post_date);");

                $stmt->bindParam(":blog_id", $blog_id);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->bindParam(":content", $content);
                $stmt->bindParam(":post_date", date("Y-m-d H:i:s"));

                $stmt->execute();
                $comment_id = $db->lastInsertId();
        }
        else {
                $error = "Could not find associated account";
        }


}

$response = [
        "error" => $error,
        "content" => htmlspecialchars($content),
        "comment_id" => $comment_id,
        "username" => $username,
        "role" => $user_role,
        "post_date" => date("Y-m-d H:i:s")];

header('Content-Type: application/json');
echo json_encode($response);
exit;

?>
