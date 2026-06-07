<!DOCTYPE html>
<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/interpreters.php';

$Parsedown = new ParsedownMath([
    'math' => [
        'enabled' => true
    ]
]);

$perpage = 15;
$offset = 0;

$stmt = $db->prepare("SELECT COUNT(*) FROM blogs;");
$stmt->execute();

$itemmax = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT blogs.*, users.username, users.role
                      FROM blogs
                      INNER JOIN users ON blogs.user_id = users.user_id
                      ORDER BY blogs.post_date DESC
                      LIMIT :offset, :perpage;");

$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':perpage', $perpage, PDO::PARAM_INT);

$stmt->execute();

$blog_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<script src="/js/general.js?v=0.05"></script>

<html>
        <head>
                <title>Blog-Dovskyi</title>
        </head>
        <body>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>
                <link rel="stylesheet" href="/css/blog.css?v=0.84">
                <div class="container">
                        <div class="page_header blog_page_header">
                                <div class="row">
                                <h1>Blog / Guestbook</h1>
                                <div class="under_header_container">
                                        <p style="margin: 0 auto 8px 0;"><b>Permissions:</b></p>
                                        <div class="row">
                                        <p style="margin-right:auto;">user::[name]</p>
                                        <p style="margin-left:auto; text-align:right;">edit, delete, comment, post</p>
                                        </div>
                                        <div class="row">
                                        <p style="margin-right:auto;">guest::anonym</p>
                                        <p style="margin-left:auto; text-align:right;">post</p>
                                        </div>
                                        <p style="margin: 10px 0 0 0;"><b>Markdown</b> for text, <b>LaTeX</b> syntax for math expressions</p>
                                </div>
                                </div>
                        </div>
                        <form id="blog_submit">
                                <div class="error_div" id="blog_submit_error" style="display:none;"></div>
                                <div class="row blog_input_container">
                                        <input type="text" id="blog_title_input" class="title_input" maxlength="200" required placeholder="Title..."></input>                       
                                        <button class="submit_blog" type="submit" style="background-image: url('/misc/icons/submit.png');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                </div>
                                <textarea id="my-text-area"></textarea>
                        </form>
                        <script> const easyMDE = new EasyMDE({
                                                element: document.getElementById('my-text-area'),
                                                spellChecker: false,
                                                minHeight: "70px",
                                                placeholder: 'Ctrl+P for preview.\n(Math can\'t be previewed without reloading the page).'
                        }); 
                        </script>   
                        <script src="/js/blog_functions.js?v=0.16"></script>
                        <div id="blog_field_container">
                        <?php foreach($blog_array as $blog){ ?>
                                <div class="blog_js_wrapper">
                                <div class="blog_container">
                                        <div class="blog_header">
                                                <div class="row">
                                                        <h2 class="blog_title"><?php echo $blog["title"]; ?></h2>
                                                        <button class="modify_post" type="button" onclick="toggle_blog(this)" style="background-image: url('/misc/icons/collapse.png');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                </div>
                                                <div class="row">
                                                        <span class="author_name"><b><?php echo ucfirst($blog["role"])."::".$blog["username"]; ?></b></span>
                                                        <span class="post_date">Posted: <?php echo $blog["post_date"]; ?></span>
                                                        <span class="post_date"><?php if (!empty($blog["modify_date"])) { echo "Modified: ".$blog["modify_date"];} ?></span>
                                                        <div style="margin: 0 0 0 auto;">
                                                                <?php if ($_SESSION["usrname"] === $blog["username"]){
                                                                echo "<button class=\"modify_post\" type=\"button\" onclick=\"delete_post(this)\" data-blog_id=\"". $blog["blog_id"] . "\" style=\"background-image: url('/misc/icons/trashbin.ico');background-position: center;background-repeat: no-repeat;background-size: cover;\"></button>
                                                                <button class=\"modify_post\" type=\"button\" onclick=\"edit_post(this)\" data-blog_id=\"" . $blog["blog_id"] . "\" style=\"background-image: url('/misc/icons/edit.png?v=1');background-position: center;background-repeat: no-repeat;background-size: cover;\"></button>"; }
                                                                ?>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="blog_body">
                                                <?php echo $Parsedown->text($blog["content"]); ?>
                                        </div>
                                        <div class="blog_comments">
                                        </div>
                                </div>
                                </div>
                                
                        <?php } ?>
                        </div>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
</html>
