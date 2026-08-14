<!DOCTYPE html>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/gallery_fetch.php';
?>

<html>
        <head>
                <title>Gallery-Dovskyi</title>
        </head>
        <body>  
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>

                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.17.0/dist/katex.min.css" crossorigin="anonymous">
                <script src="https://cdn.jsdelivr.net/npm/katex@0.17.0/dist/katex.min.js" crossorigin="anonymous"></script>
                <script src="https://cdn.jsdelivr.net/npm/marked@18.0.6/lib/marked.umd.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/marked-katex-extension@5.1.10/lib/index.umd.js"></script>
                <script src="https://unpkg.com/@panzoom/panzoom@4.6.2/dist/panzoom.min.js"></script>

                <script src="/libs/snow/snow.js"></script>

                <script>        
                        marked.use(markedKatex({ 
                                throwOnError: false,
                                displayMode: true 
                        }));

                        snow.start({
                                flakeCount: 1000,
                                stickingRatio: 0.4,
                                wind: 15,
                                monthDayRange: "12/15-01/05" 
                        });
                </script>

                <link rel="stylesheet" href="/css/gallery.css?v=3.32">
                <link rel="stylesheet" href="/css/aero.css?v=0.07">
                <link rel='stylesheet' href="/css/aic_gallery.css">
                <script src="/js/gallery.js?v=2.81"></script>
                <script src="/libs/aic_gallery.js?v=0.46"></script>
                <div class="container" id="page">
                        <div class="row page_header silver_aero aero_shadow">
                                <div class="header_text_container">
                                        <h1>Gallery<?php if($_SESSION["role"]==='root'){echo "<a href='/src/utils/gallery_upload.php' style='font-size:18px;'> *Add</a>";}?></h1>
                                        <h2>And a little bit of aero</h2>
                                </div>
                                <div style="min-width:25%;margin-left:auto;">
                                        <h2 span class="silver_aero_item aero_shadow" style="margin-left:auto;cursor:default;padding:5px;">A panel about tools</h2>
                                        <div id="collapse_tools">
                                                <div class="about_row silver_aero_item hoverable_aero" data-field_id="tools_oil">Oil painting</div>
                                                <div class="about_row silver_aero_item hoverable_aero" data-field_id="tools_ink">Pen and Ink</div>
                                        </div>
                                </div>
                        </div>
                        <div id="tools_container"></div>
                        <div id="tools_oil" class="aero_glass tools_section row" style="display:none;"> 
                                <div class="col1" style="padding:10px;">
                                        <h2 class="section_header">Oil painting</h2>
                                        <p>In my mind, the outdoors is directly associated with plein air. I rarely go hiking without an easel. Oil is the medium I chose for painting in nature, as it brought the most satisfying way of expressing the scenery. Come think of it now, I exclusively use oil to paint live landscapes. It is just too therapeutic. Paintings can say much more about the air, a good plainting freezes the moment itself. When I paint, I am capturing the feeling of being there.</p>
                                        <p>But why specifically oil? My primary subject is plein air landscapes. As landscapes tend to do, they do not remain calm and sunny. Pouring rain, snowfall, surges of wind. Oil can handle them all -- I once painted during a storm, with water all over my palette and canvas. The only predicament I faced was me getting wet, the painting remained unperturbed. Ah, and of course, the snowfall. I have a special place in my heart for Winter, so it would be a shame if the very nature of paint was water based. How can I paint with an icicle?</p>
                                </div>
                                <div class="col2" style="padding:10px;"><img id="easel_img" src="/misc/static/easel_setup.png?v=1"></div>
                        </div>  
                        <div id="tools_ink" class="aero_glass tools_section row" style="display:none;"> 
                                <div class="col1" style="padding:10px;">
                                        <h2 class="section_header">Pen and Ink</h2>
                                        <p>The ink is a lie. Every ink artist openly LIES to you, and you don't even notice it, you don't even suspect it. It is an extremely expressive and minimalist medium that truly forces you to think differently. To not paint the subject, but to paint the effect. To create the illusion of the subject from a mess of lines. Ink doesn't have a standard visual vocabulary, it has its illusion. Values, edges, textures, all expressed by homogeneous strokes, arranged in a matter where pattern recognition brain can identify the subject. Mastering these patterns and successfully creating a believable illusion is the objective of ink artist.</p>
                                        <p>Now, all of this applies to pure ink. I tried using actual tone -- did not like it. I see other artists using it, creating astonoshing effects with it, but for me it kind of ruins the fun a little bit. My objective in an ink work is to solve the subject into an effect that can be expressed with strokes only. That is the thing I enjoy doing, I plan a lot of my ink paintings with value maps.</p>
                                        <p>If you haven't yet guessed, ink is my preferred medium! I use a dip pen with India ink.</p>
                                </div>
                                <div class="col2" style="padding:10px;"><img id="easel_img" src="/misc/static/ink_setup.png?v=2"></div>
                        </div>  
<script>
                                let opt = null;
                                let sec_visible = null;
                                //only used for front end purposes
                                const usr_role = "<?php echo $_SESSION["role"];?>";

                                const entry_data = <?php echo json_encode($entries); ?>;
                                const container = "gallery_container";
                                var col = 3; 

                                listeners_init(entry_data);
                                gallery_init(entry_data, container, col, function(entry){
                                        return `<div class="entry_container" data-entry_id="${entry.entry_id}">
                <img src="${entry.thumb_path}${entry.thumb_name}" data-width=${entry.width} data-height=${entry.height}>
                <span class="entry_title"><b>${entry.title}</b></span>
                <span class="entry_date">${entry.creation_date}</span>
                </div>`;});
                        </script>
                        <div class="row filter_background silver_aero aero_shadow">
                                <span id="filter_header" class="filter_main">Filters & Tags</span>
                                <span id="filter_expand" class="filter_main">[ <span style="color:var(--blue);font-weight:400;">Expand</span> ]</span>
                                <button id="filter_apply" form="filter_form" type="submit" class="filter_action silver_aero_item hoverable_aero aero_shadow">Apply</button>
                                <button id="filter_clear" form="filter_form" name="filter_clear" type="submit" class="filter_action silver_aero_item hoverable_aero_red aero_shadow">Clear</button>
                                <div style="margin-left: auto;">
                                        <form action="" method="GET">
                                                <input type="hidden" name="order" value="<?php echo htmlspecialchars($order);?>">
                                                <input type="hidden" name="order_by" value="<?php echo htmlspecialchars($orderBy);?>">
                                                <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($date_from);?>">
                                                <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($date_to);?>">
                                                <input type="hidden" name="page" value="<?php echo htmlspecialchars($page);?>">
                                                <?php foreach ($tags as $tag){ ?>
                                                <input type="hidden" name="tags[]" value="<?php echo htmlspecialchars($tag); ?>">
                                                <?php } ?>

                                                <?php if ($page > 0) { 
                                                echo '<button id="goto_p1" name="goto_pg1" class="silver_aero_item filter_action hoverable_aero aero_shadow">GOTO pg.1</button>';} ?>
                                                <button id="prev_page" style="<?php echo ($page > 0)? '': "visibility:hidden;"; ?>"name="page_prev" class="silver_aero_item page_nav_buttons hoverable_aero aero_shadow">
                                                        <img src="/misc/icons/chevron_left.svg">
                                                </button>
                                                <span class="page_result">Results: [ <?php echo "pg.".($page+1).": ".$offset."-".min($totalEntries, $offset+$perPage)."/".$totalEntries;?> ]</span>
                                                <button id="next_page"style="<?php echo ($page < $pageMax)? '': "visibility:hidden;"; ?>" name="page_next" class="silver_aero_item page_nav_buttons hoverable_aero aero_shadow">
                                                        <img src="/misc/icons/chevron_right.svg">
                                                </button>
                                        </form>
                                </div>
                        </div>
                        <div class="row">
                                <div id="tag_container" class="aero_glass" style="display:none;">
                                        <form action="" method="GET" id="filter_form" style="width:100%;">
                                                <div class="category_row">
                                                        <div class="category_header row">Order By
                                                                <div class="silver_aero_buttons" style="margin-left:auto;"><button class="silver_aero_item hoverable_aero section_collapse" type="button">+</button> </div>
                                                        </div>
                                                        <div class="category_tags" style="display:none;">
                                                                <select id="order" class="filter_select" name="order">
                                                                        <option value="ASC">ASC</option>
                                                                        <option value="DESC">DESC</option>
                                                                </select><br>
                                                                <select id="order_by" class="filter_select" name="order_by">
                                                                        <option value="creation_date">Date</option>
                                                                        <option value="title">Name</option>
                                                                </select>
                                                        <script>
                                                                document.getElementById("order").value = "<?php echo $order; ?>";
                                                                document.getElementById("order_by").value = "<?php echo $orderBy; ?>";
                                                        </script>

                                                        </div>
                                                </div>
                                                <div class="category_row">
                                                        <div class="category_header row">Date
                                                                <div class="silver_aero_buttons" style="margin-left:auto;"><button class="silver_aero_item hoverable_aero section_collapse" type="button">+</button> </div>
                                                        </div>
                                                        <div class="category_tags category_date" style="display:none;">
                                                                <label>Date from:</label>
                                                                <input type="date" id="date_from" class="filter_date" name="date_from"></input>
                                                                <label>Date to:</label>
                                                                <input type="date" id="date_to" class="filter_date" name="date_to"></input>
                                                        </div>
                                                        <script>
                                                                document.getElementById("date_from").value = "<?php echo !empty($date_from)? $date_from:''; ?>";
                                                                document.getElementById("date_to").value = "<?php echo !empty($date_to)? $date_to:''; ?>";
                                                        </script>
                                                </div>
                                                <?php foreach($categories as $category) { ?>
                                                <div class="category_row">
                                                        <div class="category_header row"><?php echo ucfirst($category[1]); ?>
                                                                <div class="silver_aero_buttons" style="margin-left:auto;"><button class="silver_aero_item hoverable_aero section_collapse" type="button">+</button> </div>
                                                        </div>
                                                        <div class="category_tags" style="display:none;">
                                                                <?php foreach($existingTags as $tag) {
                                                                                   if ($tag["category"] === $category[1]) { ?>

                                                                                   <input type="checkbox" class="tag_opts" name="tags[]" value="<?php echo $tag["tag_id"];?>" id="option<?php echo $tag["tag_id"];?>">
                                                                                   <label for="option<?php echo $tag["tag_id"];?>"><?php echo ucfirst($tag["name"]); ?></label><br>
                                                                                           <script>
                                                                                                   opt = document.getElementById("option<?php echo $tag["tag_id"];?>")
                                                                                                   opt.checked = <?php echo in_array($tag["tag_id"], $tags)? "true": "false"; ?>;
                                                                                                   if (opt.checked === true) { sec_visible = opt; }
                                                                                           </script>
                                                                                           <?php } }?>
                                                                                           <script>
                                                                                           if (sec_visible !== null) {
                                                                                                   sec_visible.closest(".category_tags").style.display = '';
                                                                                           }
                                                                                   </script>
                                                        </div>
                                                </div>
                                                <?php } ?>
                                                <div class="filter_controls row">
                                                        <button id="filter_select" type="button" class="hoverable_aero silver_aero_item aero_shadow filter_action">Select @</button>
                                                        <button id="filter_deselect" type="button" class="hoverable_aero silver_aero_item aero_shadow filter_action">Deselect @</button>
                                                        <button id="filter_expand_categories" type="button" class="hoverable_aero silver_aero_item aero_shadow filter_action">Expand @</button>
                                                        <button id="filter_collapse" type="button" class="hoverable_aero silver_aero_item aero_shadow filter_action">Collapse @</button>
                                                </div>
                                        </form>
                                </div>
                                <div id="gallery_container" class="aero_glass gallery_container"><?php if (!empty($message)){echo "<span class='gall_message'>".$message."</span>";}?></div>
                        </div>
                        <div class="silver_aero filter_background" style="margin-left: auto;">
                                <form action="" method="GET">
                                        <input type="hidden" name="order" value="<?php echo htmlspecialchars($order);?>">
                                        <input type="hidden" name="order_by" value="<?php echo htmlspecialchars($orderBy);?>">
                                        <input type="hidden" name="date_from" value="<?php echo htmlspecialchars($date_from);?>">
                                        <input type="hidden" name="date_to" value="<?php echo htmlspecialchars($date_to);?>">
                                        <input type="hidden" name="page" value="<?php echo htmlspecialchars($page);?>">
                                        <?php foreach ($tags as $tag){ ?>
                                        <input type="hidden" name="tags[]" value="<?php echo htmlspecialchars($tag); ?>">
                                        <?php } ?>

                                        <button id="prev_page" style="<?php echo ($page > 0)? '': "visibility:hidden;"; ?>"name="page_prev" class="silver_aero_item page_nav_buttons hoverable_aero aero_shadow">
                                                <img src="/misc/icons/chevron_left.svg">
                                        </button>
                                        <span class="page_result">Results: [ <?php echo "pg.".($page+1).": ".$offset."-".min($totalEntries, $offset+$perPage)."/".$totalEntries;?> ]</span>
                                        <button id="next_page"style="<?php echo ($page < $pageMax)? '': "visibility:hidden;"; ?>" name="page_next" class="silver_aero_item page_nav_buttons hoverable_aero aero_shadow">
                                                <img src="/misc/icons/chevron_right.svg">
                                        </button>
                                </form>
                        </div>
                </div>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
</html>
