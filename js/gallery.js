function return_back() {
        const entry = document.getElementById("main_img_wrapper");
        document.body.classList.remove('no-scroll');
        entry.remove();
        // Source - https://stackoverflow.com/a/4508751
        // Posted by wombleton, modified by community. See post 'Timeline' for change history
        // Retrieved 2026-08-01, License - CC BY-SA 3.0
        history.pushState("", document.title, window.location.pathname + window.location.search);
}

function return_img() {
        document.getElementById("zoom_div").remove();
}

function get_highres(img) {
        const entry = document.getElementById("clickable_area");
        const items = `<div id="zoom_div"><img src="${img}" id="zoom_main_img"><div>`;
        entry.insertAdjacentHTML("beforeend", items);

        //Panzoom on github
        const elem = document.getElementById('zoom_main_img');
        const panzoom = Panzoom(elem, {
                maxScale: 5
        });
        panzoom.pan(10, 10);
        panzoom.zoom(2, { animate: true });
        elem.parentElement.addEventListener('wheel', panzoom.zoomWithWheel);

        entry.addEventListener("click", function(event) {
                event.stopPropagation();
                const zoom_div = document.getElementById("zoom_div");
                if (!zoom_div.contains(event.target)) {
                        return_img();
                }
        });
}

function render_entry(entry_id) {
        const img_cont_div = document.getElementById("main_img_wrapper");
        if (img_cont_div) {
                img_cont_div.remove();
        }
        const curr_idx = entry_data.findIndex(row => row.entry_id === entry_id);
        const entry = entry_data[curr_idx];
        if (entry === undefined) {
                return_back();
        }

        let img_data = '';
        let img_extras = '';
        let button_left = '';
        let button_right = '';
        let edit_button = '';

        if (entry.creation_date !== null) {
                let string =
                        `
                                <div class="img_meta">
                                <div class="data_col1">Date</div>
                                <div class="data_col2">${entry.creation_date}</div>
                                </div>
                                `;
                img_data = img_data + string;
        }
        if(entry.dimensions !== null) {
                let string =
                        `
                                <div class="img_meta">
                                <div class="data_col1">Dimensions</div>
                                <div class="data_col2">${entry.dimensions}</div>
                                </div>
                                `;
                img_data = img_data + string;
        }
        if(entry.medium !== null) {
                let string =
                        `
                                <div class="img_meta">
                                <div class="data_col1">Medium</div>
                                <div class="data_col2">${entry.medium}</div>
                                </div>
                                `;
                img_data = img_data + string;
        }
        if(entry.location !== null ) {
                let string =
                        `
                                <div class="img_meta">
                                <div class="data_col1">Location</div>
                                <div class="data_col2">${entry.location}</div>
                                </div>
                                `;
                img_data = img_data + string;
        }

        if(entry.extras !== null) {
                img_extras =
                        `
                                <div class="img_extras">
                                        <h2 class="extras_section">[ Extras Section ]</h2>
                                        <p class="extras_text">${marked.parse(entry.extras)}</p>
                                </div>
                                `;
        }

        if (curr_idx > 0) {
                button_left = `<button class="next_img left" onclick="render_entry(${entry_data[curr_idx-1]["entry_id"]})"></button>`;
        } else {
                //placeholder so image doesn't shift. Sloppy, but I'm lazy
                button_left = `<button class="next_img left" style="visibility:hidden;"></button>`;
        }
        if (curr_idx < entry_data.length-1) {
                button_right = `<button class="next_img" onclick="render_entry(${entry_data[curr_idx+1]["entry_id"]})"></button>`;

        } else {
                button_right = `<button class="next_img" style="visibility:hidden;"></button>`;
        }

        if (usr_role === 'root') {
                edit_button = `<a href="/src/utils/gallery_edit.php?entry_id=${entry.entry_id}" style="font-size:20px;margin:0 10px 0 10px;"><b>-* Edit Entry</b></a>
                               <a href="/src/utils/gallery_edit_pattern.php?pattern_id=${entry.pattern}" style="font-size:20px;margin:0 10px 0 10px;"><b>-* Edit Pattern</b></a>
                               <a href="/src/utils/gallery_delete.php?entry_id=${entry.entry_id}" style="font-size:20px;margin:0 10px 0 10px;"><b>-* DELETE ENTRY</b></a>`;
        }
        const button_bigres = `<button class="button_bigres" onclick="event.stopPropagation();get_highres('${entry.path}${entry.name}')"><img src="/misc/icons/magnifying_glass.svg"></button>`;

        const entry_string =
                `
                        <div id="main_img_wrapper" tabindex="0" style="top:${window.scrollY}px">
                        <div id="pattern_layer">
                                <div id="clickable_area" class="container gallery_borders">
                                <div class="gallery_nav_container">
                                        <div class="container"
                                        style="background:#fff;min-height: 40px;">
                                        <div class="row">
                                        ${edit_button}
                                        <div style="margin-left:auto;display:flex;justify-content:center;align-items:center;padding:10px;">
                                        <span style="color:var(--gray-darker);margin-right:5px;">[ clicking outside container works too ] -\> </span>
                                        <button id="return_gall" class="back_button"></button>
                                        </div>
                                        </div>
                                        </div>
                                </div>
                                <div class="container align_center main_img_container">
                                        <div class="row" style="width:100%;">
                                        <div class="img_border"style="margin: 0 auto 0 0;">
                                        ${button_left}
                                        </div>
                                        <img id="main_img" src="${entry.path}${entry.name}">
                                        <div class="img_border"style="margin:0 0 0 auto;">
                                        ${button_right}
                                        ${button_bigres}
                                        </div>
                                        </div>
                                </div>
                                <div class="container align_center"
                                style="flex:1">
                                <div class="meta">
                                <div class="main_img_info">
                                        <h1 class="main_img_title">${entry.title}</h1>
                                        <p class="content">${marked.parse(entry.content)}</p>
                                        <div class="main_img_data">
                                                ${img_data}
                                                <div class="bottom_border"></div>
                                        </div>
                                </div>
                                ${img_extras}
                                </div>
                                </div>
                                </div>
                        </div>
                        </div>
                        `;

        document.body.insertAdjacentHTML("afterbegin", entry_string);
        const click_area = document.getElementById("clickable_area");
        const img_area = document.getElementById("main_img_wrapper");
        const pattern = document.getElementById("pattern_layer");
        let colors = [];

        try {
                colors = JSON.parse(entry.colors);
        } catch (error) {
                colors = null;
        }

        if (colors !== null && colors.length > 1) {
                let svg_string = entry.htmlstring;
                colors.forEach((color, idx) => {
                        const regex = new RegExp(`color${idx}`, 'g');
                        svg_string = svg_string.replace(regex, color);
                });
                const svg_bck = encodeURIComponent(svg_string);
                pattern.style.backgroundImage = `url("data:image/svg+xml,${svg_bck}")`;
        } else {
                const svg_bck = encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1 1">
<path d="M0,0h1v1H0" fill="#f9f9f9"/>
</svg>`);
                pattern.style.backgroundImage = `url("data:image/svg+xml,${svg_bck}")`;
        }
        img_area.focus();

        img_area.addEventListener('click', function(event) {
                if (!click_area.contains(event.target)) {
                        if (document.getElementById("zoom_div")) {
                                return_img();
                        } else {
                                return_back();
                        }
                }
        });
        document.getElementById("return_gall").addEventListener("click", function(event) {
                return_back();
        });
        history.pushState(null, null, `#${entry_id}`);


}

window.addEventListener('DOMContentLoaded', function(event) {
        const entry_id = parseInt(window.location.hash.substring(1));
        if (entry_id) {
                document.body.classList.add('no-scroll');
                render_entry(entry_id);
        }
});

addEventListener("hashchange", (event) => {
        const entry_id = parseInt(window.location.hash.substring(1));

        if (entry_id) {
                document.body.classList.add('no-scroll');
                render_entry(entry_id);
        } else {
                return_back();
        }
});

function listeners_init(entry_data) {
        window.addEventListener('DOMContentLoaded', function(event) {
                const gallery_cont = document.getElementById('gallery_container')

                gallery_cont.addEventListener('click', function(event) {
                        const entry_div = event.target.closest('.entry_container');
                        if (!entry_div) {
                                return;
                        }

                        const entry_id = parseInt(entry_div.dataset.entry_id, 10);

                        document.body.classList.add('no-scroll');
                        render_entry(entry_id);
                });
                const tools_container = document.getElementById("tools_container");
                document.getElementById("collapse_tools").addEventListener('click', function(event) {
                        const clicked = event.target.closest(".about_row");
                        if (!clicked) {
                                return;
                        }
                        clicked.classList.toggle("lock_aero_hover");
                        const field = document.getElementById(clicked.dataset.field_id);
                        if (field.style.display === 'none'){
                                field.style.display = '';
                                tools_container.appendChild(field);
                        } else {
                                field.style.display = 'none';
                        }
                });

                document.getElementById("filter_expand").addEventListener('click', function(event) {
                        const button = document.getElementById('filter_expand');
                        const container = document.getElementById("tag_container");
                        const gallery = document.getElementById("gallery_container")
                        if (container.style.display === 'none') {
                                container.style.display = '';
                                button.querySelector('span').innerHTML = "Collapse";
                                aic_rearrange(col-1, "gallery_container");
                        } else {
                                container.style.display = 'none';
                                button.querySelector('span').innerHTML = "Expand";
                                aic_rearrange(col, "gallery_container");
                        }
                });
                document.getElementById("tag_container").addEventListener("click", function(event) {
                        const clicked = event.target.closest(".category_header");
                        if (!clicked) {
                                return;
                        }
                        const category = event.target.closest(".category_row");
                        const tags = category.querySelector(".category_tags");
                        const button = category.querySelector("button");
                        if (tags.style.display === 'none'){
                                tags.style.display = '';
                                button.innerHTML = '-';
                        } else {
                                tags.style.display = 'none';
                                button.innerHTML = '+';
                        }
                });
                document.getElementById("filter_select").addEventListener("click", function(event) {
                        const container = document.getElementById("tag_container");
                        const checkboxes = container.querySelectorAll(".tag_opts");
                        for (const box of checkboxes) {
                                box.checked = true;
                        }
                        const containers = container.querySelectorAll(".category_tags");
                        for (const section of containers) {
                                section.style.display = '';
                        }
                        const buttons = container.querySelectorAll(".section_collapse");
                        for (const button of buttons) {
                                button.innerHTML = '-';
                        }

                });
                document.getElementById("filter_deselect").addEventListener("click", function(event) {
                        const container = document.getElementById("tag_container");
                        const checkboxes = container.querySelectorAll(".tag_opts");
                        for (const box of checkboxes) {
                                box.checked = false;
                        }
                        const containers = container.querySelectorAll(".category_tags");
                        for (const section of containers) {
                                section.style.display = 'none';
                        }
                        const buttons = container.querySelectorAll(".section_collapse");
                        for (const button of buttons) {
                                button.innerHTML = '+';
                        }

                });
                document.getElementById("filter_expand_categories").addEventListener("click", function(event) {
                        const container = document.getElementById("tag_container");
                        const containers = container.querySelectorAll(".category_tags");
                        for (const section of containers) {
                                section.style.display = '';
                        }
                        const buttons = container.querySelectorAll(".section_collapse");
                        for (const button of buttons) {
                                button.innerHTML = '-';
                        }
                });
                document.getElementById("filter_collapse").addEventListener("click", function(event) {
                        const container = document.getElementById("tag_container");
                        const containers = container.querySelectorAll(".category_tags");
                        for (const section of containers) {
                                section.style.display = 'none';
                        }
                        const buttons = container.querySelectorAll(".section_collapse");
                        for (const button of buttons) {
                                button.innerHTML = '+';
                        }
                });
        });
}

