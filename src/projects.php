<!DOCTYPE html>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/init.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/utils/projects_fetch.php';
?>

<html>
        <head>
                <title>Projects-Dovskyi</title>
        </head>
        <body>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/header.php'; ?>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.17.0/dist/katex.min.css" crossorigin="anonymous">
                <script src="https://cdn.jsdelivr.net/npm/katex@0.17.0/dist/katex.min.js" crossorigin="anonymous"></script>
                <script src="https://cdn.jsdelivr.net/npm/marked@18.0.6/lib/marked.umd.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/marked-katex-extension@5.1.10/lib/index.umd.js"></script>
                <script src="https://unpkg.com/@panzoom/panzoom@4.6.2/dist/panzoom.min.js"></script>

                <script src="/libs/snow/snow.js"></script>
                <script src="/libs/aic_gallery.js?v=1.09"></script>
                <link rel="stylesheet" href="/css/aic_gallery.css?v=0.06">
                <link rel="stylesheet" href="/css/tags.css?v=0.02">
                <link rel="stylesheet" href="/css/projects.css?v=0.92">
                <script src="/js/projects.js?v=0.93"></script>

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

                        let opt = null;
                        let sec_visible = null;
                        //only used for front end purposes
                        const usr_role = "<?php echo $_SESSION["role"];?>";

                        const entry_data = <?php echo json_encode($entries); ?>;
                        const entry_tags = <?php echo json_encode($entry_tags); ?>;
                        const container = "projects_container";
                        const cols = 4;

                        let tag_lookup = {};
                        for (const tag of entry_tags) {
                                let tag_string = '';
                                if (tag.name !== 'program') {
                                if (tag.name === 'unfinished') {
                                        tag_string = `<span style="background:var(--red);">${tag.name}</span>`;
                                } else {
                                        tag_string = `<span>${tag.name}</span>`;
                                }
                                tag_lookup[tag.entry_id] = (tag_lookup[tag.entry_id] || "") + tag_string;
                                }
                        }

                        listeners_init();

                        gallery_init(entry_data, container, cols, function(entry){
                                const picture = (entry.img_name !== null)? 
                                        `<div class="entry_picture">
                                        <img 
                                        data-width=${entry.width}
                                        data-height=${entry.height}
                                        src="${entry.img_path}${entry.img_name}">
                                                 </div>`: '';
                                if (entry.modify_date !== null && entry.modify_date !== entry.post_date) {
                                        entry_date = `<span>${entry.post_date}</span>
                                                      <span class="modified">${entry.modify_date}</span>`;
                                } else {
                                        entry_date = `<span class="modified">${entry.post_date}</span>`;
                                }


                                let tags = (tag_lookup[entry.entry_id] !== undefined)? tag_lookup[entry.entry_id]: '';

                                return `
                                        <div class="entry_container" data-entry_id=${entry.entry_id}>
                                                ${picture}
                                                <div class="entry_title">${entry.title}</div>
                                                <div class="entry_description">${entry.brief}</div>
                                                <div class="entry_dat">
                                                        <div class="entry_lang">
                                                        ${tags}
                                                        </div>
                                                        <div class="entry_date">${entry_date}</div>
                                                </div>

                                        </div>
                `;});
                </script>


                <div class="container">
                        <div class="row page_header">
                                <div class="header_text_container">
                                        <h1>Projects<?php if($_SESSION["role"]==='root'){echo "<a href='/src/utils/projects_upload.php' style='font-size:18px;'> *Add</a>";}?></h1>
                                        <h2>Devlogs, Documentation and Misc</h2>
                                </div>
                        </div>

                        <div class="row filter_background aero_shadow">
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
                                                                        <option value="post_date">Last posted</option>
                                                                        <option value="modify_date">Last modifed</option>
                                                                        <option value="title">Title</option>
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
                                                                <br><p><i>Applied to either post or modify date in ORDER BY</i></p>
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
                                                                                   if ($tag["category"] === $category[0]) { ?>

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
                                <div id="projects_container" class="projects_container"><?php if (!empty($message)){echo "<span class='gall_message'>".$message."</span>";}?></div>
                        </div>
                </div>
                <?php include $_SERVER['DOCUMENT_ROOT'].'/src/footer.php'; ?>
        </body>
</html>
