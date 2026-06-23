<!DOCTYPE html>
<?php include $_SERVER['DOCUMENT_ROOT'].'/src/utils/init.php'; ?>

<html>
        <head>
                <title>Dovskyi Main</title>
        </head>
        <body>  
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>
                <div class="container">
                        <div class="page_header" style="min-height:180px;">
                                <div class="row">
                                        <div class="header_text_container">
                                                <h1>Welcome to my Archive!</h1>
                                                <h2>feel free to look around, but don't touch anything. exhibition is fragile.</h2>
                                        </div>
                                        <div class="header_img" style="background-image: url('/misc/static/architecture.png');"></div>
                                </div>
                        </div>
                        <div class="row">
                                <div class="infobox info-box-green">
                                        <p class="info_text"><b>Best viewed on desktop.</b> The website runs on my <b>Raspberry PI 4</b>, so might be slow when fetching images/large queries.</b></p>
                                </div>
                        </div>
                        <div class="row">
                                <div class="col1">
                                        <h2 class="section_header">About the Website</h2>
                                        <div class="container">
                                        <p>This is an archive of all my work. My work as an artist, as a programmer, as an engineer and.. just work.</p>
                                        <p>It is also an archive for something a little more important--the process behind it. I feel like there is no platform where I would be able to combine such a wide range of subjects properly, in a fully elaborate manner, so I made my own! Besides the benefit of being fully customizable, I have my own database full of memories that can be accessed at any time.</p>
                                        <p>I am adding new entries every week on various subjects. As I progress through my degree, those entries will get more and more interesting. Even though art is the guest of honor, technical things definitely have a place here. Where else can I write pages of explanations for my programs, derivations and experiments?</p>
                                        <p>To find entries you can use the search box conveniently placed in the top right corner. It will query the database for matches, and if you are lucky you might find what you need!</p>
                                        </div>
                                        <div class="infobox info-box-blue">
                                                <p class="info_text">Spot a mistake, mistype or a bug? Report: I accept emails or carrier pigeons</p>
                                        </div>
                                </div>
                                <div class="col2">
                                        <h2 class="section_header">Announcements</h2>
                                        <div class="well">
                                                <p>Stub</p>
                                        </div>
                                        <h2 class="section_header">Recent blogs</h2>
                                        <div class="well">
                                                <p>Stub</p>
                                        </div>
                                </div>
                        </div>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
</html>
