<?php
include $_SERVER['DOCUMENT_ROOT'].'/src/utils/init.php';

$stmt = $db->prepare("(SELECT * FROM announcements
                       WHERE persistent = 0
                       ORDER BY post_date DESC 
                       LIMIT 1)
                        UNION ALL
                       (SELECT * FROM announcements
                       WHERE persistent = 1
                       ORDER BY post_date DESC 
                       LIMIT 1);");

$stmt->execute();

$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
