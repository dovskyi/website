<link rel="stylesheet" href="/css/style.css?v=1.30">
<link rel="stylesheet" href="/css/header.css?v=0.17">
<link rel="icon" type="image/x-icon" href="/favicon.png?v=0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bitter:ital,wght@0,100..900;1,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Jim+Nightshade&display=swap" rel="stylesheet">

<header id="page_header">
        <div class="title" id="title_header">
                <div class="container">
                        <a href="/"><img id="logoimg" src="/misc/static/logo.png?v=0"></a>
                </div>
        </div>
        <div id="navbar_tools">
        <nav class="navbar">
                <div class="container">
                        <div class="row">
                        <ul class="navbar-left">
                                <li class="nav-itm"><a href="/">Main</a></li>
                                <!-- <li class="nav-itm"><a href="/src/server.php">Server</a></li> -->
                                <li class="nav-itm"><a href="/src/blog.php">Blog</a></li>
                                <li class="nav-itm"><a href="/src/aboutme.php">About me</a></li>
                                <?php if ($_SESSION["role"] === "root") { echo '<li class="nav-itm"><a href="/elFinder/elfinder.php">MGR</a></li>';}?>
                        </ul>
                        <ul class="navbar-right">
                               <!-- <li class="nav-itm"><a>Tools</a></li>-->
                                <li class="nav-itm" id="userbtn">
                                        <button class="dropbtn" id="user-dropbtn" onclick="dropdown('user-dropdown', 'drop_arrow', 'user-dropbtn')">
<?php
if (empty($_SESSION["usrname"])){
        echo "Guest";
}
else {
        echo $_SESSION["usrname"];
}
?>
                                        <span id="drop_arrow" style="pointer-events: none;">&#x25BC;</span></button>
                                        <div class="dropdown-content" id="user-dropdown">
<?php
if (empty($_SESSION["usrname"])){
        echo "<button><a href=\"/src/register.php\">Create account</a></button>
              <button><a href=\"/src/login.php\">Login</a></button>";
}
else {
        echo "<button><a href=\"/src/utils/logout.php\">Log out</a></button>
              <button><a href=\"/src/utils/change_psswd.php\">Change password</a></button>";
}
?>
                                        </div>
                                        <script src="/js/dropdown.js?v=0.8"></script>
                                        <script> close_onclick("user-dropdown", "drop_arrow", "user-dropbtn");</script>
                                </li>
                        </ul>
                        </div>
                </div>
        </nav>
        <nav id="library_tools" class="library">
                <div class="container">
                        <div class="row">
                        <ul class="library-left">
                                <li class="lib-itm"><a href="/src/gallery.php">Gallery</a></li>
                                <li class="lib-itm"><a href="/src/projects.php">Projects</a></li>
                                <li class="lib-itm"><a href="/src/bonsai.php">Bonsai</a></li>
                        </ul>
                        <ul id="left_library"class="library-left" style="margin-left: auto;">
                                <li class="lib-itm"><a href="https://github.com/dovskyi" target="_blank" style="background:var(--gray-light);">Github</a></li>
                        </ul>
                        </div>
                </div>
        </nav>
        </div>
</header>
