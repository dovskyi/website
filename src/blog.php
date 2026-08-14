<!DOCTYPE html>
<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/interpreters.php';

$Parsedown = new ParsedownMath([
        'math' => [
                'enabled' => true
        ]
]);

$date_reg = '/^([0-9]{2})?[0-9]{2}(-)(1[0-2]|0?[1-9])\2(3[01]|[12][0-9]|0?[1-9])$/';

$order = $_GET["sort"] ?? '';
$lower_bound = $_GET["date_from"] ?? '';
$upper_bound = $_GET["date_to"] ?? '';
$wheres = NULL;
$page = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
$perpage = 10;
$bind = [];
$conditions = [];
$error = '';



if (isset($_GET["clear_filter"])){
        $order = "DESC";
        $error = '';
        $lower_bound = '';
        $upper_bound = '';
}
else {
        if ($order !== "ASC") {
                $order = "DESC";
        }
        if (preg_match($date_reg, $lower_bound) && $lower_bound > '2026-0-0' && ($lower_bound <= $upper_bound || $upper_bound === '')){
                $conditions[] = "post_date >= :lower_bound";
                $bind[':lower_bound'] = $lower_bound;
        }
        else {
                if ($lower_bound !== ''){
                        $error = ($error !== '')? $error . "<br>Could not match 'from:'--improper value. Bound will be ignored.": "Could not match 'from:'--improper value. Bound will be ignored.";
                }
        }
        if (preg_match($date_reg, $upper_bound)){
                $conditions[] = "post_date <= :upper_bound";
                $bind[':upper_bound'] = $upper_bound;
        }
        else {
                if ($upper_bound !== ''){
                        $error = ($error !== '')? $error . "<br>Could not match 'to:'--improper value. Bound will be ignored.": "Could not match 'to:'--improper value. Bound will be ignored.";
                }
        }

        if (!empty($conditions)){
                $wheres = " WHERE " . implode(" AND ", $conditions);
        }
}

$query = "SELECT COUNT(*) FROM blogs" . $wheres . ";";
$stmt = $db->prepare($query);
$stmt->execute($bind);

$itemmax = $stmt->fetchColumn();
$itemmax = ceil($itemmax/$perpage);

if ($page > $itemmax || $page < 0){
        $page = 1;
}

if (isset($_GET["to_right"]) && isset($_GET["to_left"])){
        $page = $page;
}
else if (isset($_GET["to_right"]) && $page < $itemmax){
        $page +=1;
}
else if (isset($_GET["to_left"]) && $page > 1){
        $page -=1;
}

$offset = $perpage * ($page-1);

$query = "SELECT blogs.*, users.username, users.role 
        FROM blogs
        INNER JOIN users ON blogs.user_id = users.user_id " . $wheres .
        " ORDER BY blogs.post_date " . $order .
        " LIMIT :offset, :perpage;";

$stmt = $db->prepare($query);

foreach ($bind as $key => $value) {
        $stmt->bindValue($key, $value);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':perpage', $perpage, PDO::PARAM_INT);

$stmt->execute();

$blog_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

//fetch all comments for blogs on the current page--I could not find a way to only fetch 2 per blog.

$blog_list = '';
if (!empty($blog_array)){
        $itms = count($blog_array)-1;
        if ($itms > 0) {
                for ($i = 0; $i < $itms; $i++) {
                        $blog_list = $blog_list . $blog_array[$i]["blog_id"] . ' , ';
                }
        }
        $blog_list = $blog_list . $blog_array[$itms]["blog_id"];

$query = "WITH tmp AS (
        SELECT comments.*, users.username, users.role
        FROM comments 
        INNER JOIN users ON comments.user_id = users.user_id
        WHERE blog_id IN ( " . $blog_list . ") 
        )  
        SELECT comm_id, blog_id, content, post_date, username, role
        FROM tmp
        ORDER BY blog_id, post_date;";

$stmt = $db->prepare($query);
$stmt->execute();

$blog_comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<html>
        <head>
                <title>Blog-Dovskyi</title>
        </head>
        <body>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>
                <link rel="stylesheet" href="/css/blog.css?v=2.07">
                <div class="container">
                        <div class="page_header blog_page_header">
                                <div class="row">
                                <div class="container" style="width:70%;">
                                        <h1>Blog / Guestbook</h1>
                                </div>
                                <div class="under_header_container">
                                        <p style="margin: 0 auto 8px 0;"><b>Permissions:</b></p>
                                        <div class="row">
                                        <p style="margin-right:auto;">user::[name]</p>
                                        <p style="margin-left:auto; text-align:right;">edit, delete, comment, post</p>
                                        </div>
                                        <div class="row">
                                        <p style="margin-right:auto;">guest::anonym</p>
                                        <p style="margin-left:auto; text-align:right;">comment, post</p>
                                        </div>
                                        <p style="margin: 10px 0 0 0;"><b>Markdown</b> for text, <b>LaTeX</b> syntax for math expressions</p>
                                </div>
                                </div>
                        </div>
                                        <form method="GET" action="/src/blog.php" class="filter_form">
                                                <div class="container">
                                                <div class="error_text" style="margin: auto 0 10px 15px;"><?php echo $error; $error = ''; ?></div>
                                                <div class="row">
                                                <span style="font-family:'Bitter', 'Open Sans'; margin: 0 10px 0 13px;"><b>Order by: </b></span>
                                                <select class="sort_filter" name="sort" selected="ASC">
                                                        <option <?php echo ($order==='DESC') ? "selected":''?>>DESC</option>
                                                        <option <?php echo ($order==='ASC') ? "selected":''?>>ASC</option>
                                                </select>
                                                <span>from:</span>
                                                <input type="date" class="date_filter" name="date_from" min="2026-0-0" value='<?php if(!empty($lower_bound)){ echo $lower_bound;}?>'></input>
                                                <span>to:</span>
                                                <input type="date" class="date_filter" name="date_to" value='<?php if(!empty($upper_bound)){ echo $upper_bound;}?>'></input>
                                                <button class="filter_button" name="set_filter" style="margin-left: 10px"><b>Go</b></button>
                                                <button class="filter_button" name="clear_filter"><b>Clear</b></button>
                                                <button class="filter_button" name="goto_p1" style="margin-left: auto;"><b>GOTO page 1</b></button>
                                                </div>
                                                </div>
                                        </form>
                        <form class="pages" method="GET" action="/src/blog.php">
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($order); ?>">
                                <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($lower_bound); ?>">
                                <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($upper_bound); ?>">
                                <input type="hidden" name="page" value="<?php echo htmlspecialchars($page); ?>">

                                <?php if ($page > 1){ echo '<button class="page_nav" name="to_left"></button>';}?>
                                <span class="page_num" name="page_num">PAGE: <?php echo $page ?></span>
                                <?php if ($page < $itemmax) {echo '<button class="page_nav right" name="to_right"></button>';} ?>
                        </form>
                        <form id="blog_submit" style="display:<?php if($page !== 1) {echo 'none';} ?>">
                                <div class="error_div" id="blog_submit_error" style="display:none;"></div>
                                <div class="row blog_input_container">
                                        <input type="text" id="blog_title_input" class="title_input" maxlength="200" required placeholder="Title..."></input>                       
                                        <button class="submit_blog" type="submit" style="background-image: url('/misc/icons/submit.png');background-position: center;background-repeat: no-repeat;background-size: cover;" aria-label="submit"></button>
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
                        <script src="/js/blog_functions.js?v=0.66"></script>
                        <div id="blog_field_container">
                        <?php foreach($blog_array as $blog){ ?>
                                <div class="blog_js_wrapper">
                                <div class="error_div" id="blog_general_error" style="display:none;"></div>
                                <div class="blog_container">
                                        <div class="blog_header">
                                                <div class="row">
                                                        <h2 class="blog_title"><?php echo htmlspecialchars($blog["title"]); ?></h2>
                                                        <button class="collapse_post" type="button" onclick="toggle_blog(this)" style="background-image: url('/misc/icons/collapse.png');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                </div>
                                                <div class="row">
                                                        <span class="author_name"><b><?php echo ucfirst($blog["role"])."::".$blog["username"]; ?></b></span>
                                                        <span class="post_date">Posted: <?php echo $blog["post_date"]; ?></span>
                                                        <span class="modify_date"><?php if (!empty($blog["modify_date"])) { echo "Modified: ".$blog["modify_date"];} ?></span>
                                                        <div style="margin: 0 0 0 auto;">
                                                        <div class="modify_button_wrapper">
<?php if ($_SESSION["usrname"] === $blog["username"] || $_SESSION["role"] === 'root'){
echo "<button class=\"modify_post\" type=\"button\" onclick=\"if(confirm('Are you sure you want to delete this blog?')) delete_post(this);\" data-blog_id=\"". $blog["blog_id"] . "\" style=\"background-image: url('/misc/icons/trashbin.ico');background-position: center;background-repeat: no-repeat;background-size: cover;\"></button>
        <button class=\"modify_post\" type=\"button\" onclick=\"edit_post(this)\" data-blog_id=\"" . $blog["blog_id"] . "\" style=\"background-image: url('/misc/icons/edit.png?v=1');background-position: center;background-repeat: no-repeat;background-size: cover;\"></button>"; }
?>
                                                        </div>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="blog_body">
                                                <?php echo $Parsedown->text(htmlspecialchars($blog["content"])); ?>
                                        </div>
                                        <div class="blog_comments">
<?php
$current_comments = [];
foreach($blog_comments as $comment) {
        if ($comment["blog_id"] === $blog["blog_id"]){
                $current_comments[] = $comment;
        }
}
?>
                                                <div class="comment_header">
                                                <span class="comment_title"><b>Comments [ <?php echo count($current_comments); ?> ]</b></span>
                                                        <button class="collapse_comments" type="button" onclick="toggle_comments(this)" style="background-image: url('/misc/icons/collapse.png');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                </div>
                                                <div class="comment_body">
                                                        <div class="comment_section_wrapper">
<?php 
$i=0;
foreach($current_comments as $comment) {
        if ($i<= 1){
                if ($_SESSION["role"] === 'root' || ($comment["role"] === 'user' && $comment["username"] === $_SESSION["usrname"])){
                        $button_string = '<button class="delete_comm"  data-comment_id="'.$comment["comm_id"].'" onclick="if(confirm(\'Are you sure you want to delete this comment?\')) delete_comment(this);" style="background-image: url(\'/misc/icons/trash.png?v=0\');"></button>';
                } else {
                        $button_string = '';
                }
                echo '
                                                                <div class="comment_container">
                                                                        <div class="row">
                                                                                <div class="comment_c1">
                                                                                        <span><b>'. $comment["role"] ."::" . $comment["username"] . '</b></span>
                                                                                        <div class="row">
                                                                                        <span>' . $comment["post_date"] . '</span>' . $button_string . '</div>
                                                                                </div>
                                                                                <div class="comment_c2">' . $comment["content"] . '</div>
                                                                        </div>
                                                                </div>';
                $i++; 
        } else {
                if ($i === 2) {echo "<div class='comments_rest' style='display: none;'>";}
                $i++;
                if ($_SESSION["role"] === 'root' || ($comment["role"] === 'user' && $comment["username"] === $_SESSION["usrname"])){
                        $button_string = '<button class="delete_comm"  data-comment_id="'.$comment["comm_id"].'" onclick="if(confirm(\'Are you sure you want to delete this comment?\')) delete_comment(this);" style="background-image: url(\'/misc/icons/trash.png?v=0\');"></button>';
                } else {
                        $button_string = '';
                }
                echo '
                                                                <div class="comment_container">
                                                                        <div class="row">
                                                                                <div class="comment_c1">
                                                                                        <span><b>'. $comment["role"] ."::" . $comment["username"] . '</b></span>
                                                                                        <div class="row">
                                                                                        <span>' . $comment["post_date"] . '</span>' . $button_string . '</div>
                                                                                </div>
                                                                                <div class="comment_c2">' . $comment["content"] . '</div>
                                                                        </div>
                                                                </div>';

        }
        if ($i === count($current_comments) && $i>=2){
                echo "</div>";
        }
}
?>
                                                        </div>
<?php
if (count($current_comments) > 2){
        echo '<button class="more_comments" data-blog_id="'. $blog["blog_id"] . '" onclick="load_comments(this)">Load [ ' . count($current_comments)-2 . ' ] more comment(s)</button>';
}
?>
                                                        <div class="error_div" style="display: none;"></div>
                                                        <form class="submit_comment_form">
                                                                <div class="row">
                                                                        <textarea class="comment_text" placeholder="Comment..."></textarea>
                                                                        <div style="margin-left: auto">
                                                                        <button class="submit_comment" type="button" data-blog_id="<?php echo $blog["blog_id"]; ?>" onclick="submit_comment(this)" style="background-image: url('/misc/icons/comment_reply.png');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                                        </div>
                                                                </div>
                                                        </form>
                                                </div>

                                        </div>
                                </div>
                                </div>

                        <?php } ?>
                        </div>
                        <form class="pages" method="GET" action="/src/blog.php">
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($order); ?>">
                                <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($lower_bound); ?>">
                                <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($upper_bound); ?>">
                                <input type="hidden" name="page" value="<?php echo $page; ?>">

                                <?php if ($page > 1){ echo '<button class="page_nav" name="to_left"></button>';}?>
                                <span class="page_num" name="page_num">PAGE: <?php echo $page ?></span>
                                <?php if ($page < $itemmax) {echo '<button class="page_nav right" name="to_right"></button>';} ?>
                        </form>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
