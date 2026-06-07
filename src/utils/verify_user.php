<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

$blog_id = $data["blog_id"];
$username = !empty($_SESSION["usrname"])? $_SESSION["usrname"]: '';

$valid = false;

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
        $stmt->bindParam(':blog_id', $blog_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($username === $row["username"] || $role === 'root'){
                        $valid = true;
                }
        }
}

$response = ["valid" => $valid];

header('Content-Type: application/json');
echo json_encode($response);
exit;

?>
