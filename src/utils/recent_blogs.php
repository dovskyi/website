<?php
include $_SERVER['DOCUMENT_ROOT'].'/src/utils/init.php';

$stmt = $db->prepare("SELECT blogs.*, users.username, users.role 
                      FROM blogs INNER JOIN users ON blogs.user_id = users.user_id
                      ORDER BY post_date DESC
                      LIMIT 3;");
$stmt->execute();

$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
