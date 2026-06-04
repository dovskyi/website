<!DOCTYPE html>
<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';

$perpage = 15;
$offset = 0;

$stmt = $db->prepare("SELECT COUNT(*) FROM blogs;");
$stmt->execute();

$itemmax = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT blogs.*, users.username, users.role
                      FROM blogs
                      INNER JOIN users ON blogs.user_id = users.user_id
                      LIMIT :offset, :perpage;");

$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':perpage', $perpage, PDO::PARAM_INT);

$stmt->execute();

$blog_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<link rel="stylesheet" href="/css/blog.css?v=0.27">
<html>
        <head>
                <title>Blog-Dovskyi</title>
        </head>
        <body>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>
                <div class="container">
                        <h1 class="greet_text">Blog page</h1>
                        <?php foreach($blog_array as $blog){ ?>
                                <div class="blog_container">
                                        <div class="blog_header">
                                                <h2><?php echo $blog["title"]; ?></h2>
                                                <div class="row">
                                                        <span class="author_name"><b><?php echo ucfirst($blog["role"])."::".$blog["username"]; ?></b></span>
                                                        <span class="post_date">Posted: <?php echo $blog["post_date"]; ?></span>
                                                        <span class="post_date"><?php if (!empty($blog["modify_date"])) { echo "Modified: ".$blog["modify_date"];} ?></span>
                                                        <div style="margin: 0 0 0 auto;">
                                                                <button class="modify_post" style="background-image: url('/misc/icons/trashbin.ico');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                                <button class="modify_post" style="background-image: url('/misc/icons/vista_book_3.ico');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="blog_body">
                                                <?php echo $blog["content"]; ?>
                                        </div>
                                </div>
                                
                        <?php } ?>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
</html>
