<!DOCTYPE html>
<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/init.php'; 
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/recent_blogs.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/src/utils/fetch_announcement.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/interpreters.php';

$Parsedown = new ParsedownMath([
'math' => [
'enabled' => true
]
]);

?>

<html>
        <link rel="stylesheet" href="/css/index.css?v=0.17">
        <link rel="preload" href="/misc/static/sky1.jpg" as="image">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.17.0/dist/katex.min.css" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/katex@0.17.0/dist/katex.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/marked@18.0.6/lib/marked.umd.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/marked-katex-extension@5.1.10/lib/index.umd.js"></script>

        <script>
                marked.use(markedKatex({
                        throwOnError: false,
                        displayMode: true
                }));
        </script>

        <head>
                <title>dovskyi@world</title>
        </head>
        <body>  
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>
                <div class="container">
                        <div class="page_header" style="min-height:180px;">
                                <div class="row">
                                        <div class="header_text_container">
                                                <h1>@world</h1>
                                                <h2>feel free to look around, but don't touch anything. exhibition is fragile.</h2>
                                        </div>
                                        <div class="header_img" style="background-image: url('/misc/static/architecture.png');"></div>
                                </div>
                        </div>
                        <div class="row">
                                <div class="infobox info-box-green">
                                        <p class="info_text"><b>Best viewed on desktop.</b> The website runs on my <b>Raspberry PI 4</b>, might be slow when fetching images/complex queries. Enjoy <3</b></p>
                                </div>
                        </div>
                        <div class="row">
                                <div class="col1">
                                        <h2 class="section_header">About the Website</h2>
                                        <div class="container">
                                                <p>This is an archive [@world] of all my work. My work as an artist, as a programmer, as an engineer and.. just regular work.</p>
                                                <p>It is also an archive for something a little more important--the process behind it. There is no platform where I would be able to neatly track the creation, thought process, sketches, diagrams, for such a wide range of subjects, in an elaborate, uncompressed manner. So I made my own! Besides the benefit of being fully customizable, I have a local database that can be easily backed up at any time.</p>
                                                <p>There exists a blog for less relevant things--you can [and I encourage you to] leave a message in the <a href="/src/blog.php">guestbook!</a> I will treat your entry in the database with great care and thought, it will be my n-th pet.</p>
                                                <p>Have fun in the @world! [I certainly did]</p>
                                        </div>
                                        <div class="infobox info-box-blue">
                                                <p class="info_text">Spot a mistake, vulnerability or a bug? Report: I accept emails or carrier pigeons</p>
                                        </div>
                                        <h2 class="section_header" style="margin-top:25px;">Highlights</h2>
                                        <p>Stub</p>
                                </div>
                                <div class="col2">
                                        <h2 class="section_header">Announcements<?php if ($_SESSION["role"] === 'root'){ echo "<a href='/src/utils/add_announcement.php' style='font-size: 18px;'> *Edit</a>";}?></h2>
                                        <?php foreach ($announcements as $ann) {?>
                                        <div class="well container" <?php if ($ann["persistent"]){ echo "style='background:#fff;'";}?>>
                                                <div class="well_container">
                                                        <p class="well_name" style="font-size: 19px;font-weight: 500;"><?php echo $ann["title"];?></p>
                                                        <p class="well_info"><?php if (!$ann["persistent"]){echo $ann["post_date"];} else {echo "Persistent announcement";}?></p>
                                                        <p style="margin-top:10px;"><?php echo $ann["content"];?></p>
                                                </div>
                                        </div>
                                        <?php } ?>
                                        <h2 class="section_header">Recent [ <?php echo count($blogs); ?> ] blogs</h2>
                                        <div class="well container">
                                                <?php foreach($blogs as $blog){ ?>
                                                <div class="well_container">
                                                        <div class="well_header">
                                                                <p class="well_name"><?php echo $blog["title"]; ?></p>
                                                                <div class="row">
                                                                        <p class="well_info"><?php echo ucfirst($blog["role"]) . "::" . $blog["username"]; ?></p>
                                                                        <div  style="margin-left: auto;">
                                                                                <p class="well_info"><?php echo $blog["post_date"]; ?></p>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                        <div class="well_body overflow_fadeout"><?php echo $Parsedown->text($blog["content"]);?></div>
                                                </div>
                                                <?php } ?>
                                        </div>
                                </div>
                        </div>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
</html>
