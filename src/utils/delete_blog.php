<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

$blog_id = (int)$data["blog_id"];
$username = !empty($_SESSION["usrname"])? $_SESSION["usrname"]: '';
$error = 'true';

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
                        $stmt = $db->prepare("DELETE FROM blogs
                                              WHERE blog_id = :blog_id;");
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
        "error" => $error];

header('Content-Type: application/json');
echo json_encode($response);
exit;

?>
