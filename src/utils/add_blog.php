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

$title_length = strlen($title);
$content_length = strlen($content);

$username = !empty($_SESSION["usrname"]) ? $_SESSION["usrname"]: "anonym";
$user_role = NULL;
$blog_id = NULL;

if ($title_length <= 0 || $titile_length > 200 || $content_length <= 0 || $content_length > 4000) {
        $error = "Title can't be more than 200 chars.<br>Content can't be more than 4000 chars.";
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

                $stmt = $db->prepare("INSERT INTO blogs (user_id, title, content, post_date)
                                      VALUES (:user_id, :title, :content, :post_date);");

                $stmt->bindParam(":user_id", $user_id);
                $stmt->bindParam(":title", $title);
                $stmt->bindParam(":content", $content);
                $stmt->bindParam(":post_date", date("Y-m-d H:i:s"));

                $stmt->execute();
                $blog_id = $db->lastInsertId();
        }
        else {
                $error = "Could not find associated account";
        }


}

$response = [
        "error" => $error,
        "content" => $Parsedown->text($content),
        "blog_id" => $blog_id,
        "author" => $username,
        "role" => $user_role,
        "post_date" => date("Y-m-d H:i:s")];

header('Content-Type: application/json');
echo json_encode($response);
exit;

?>
