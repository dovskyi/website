function return_back() {
        const entry = document.getElementById("projects_window");
        document.body.classList.remove('no-scroll');

        if (entry) {
                const footer = document.getElementById("page_footer");
                const header = document.getElementById("navbar_tools");
                document.getElementById("library_tools").style.background = '';
                const lib = document.getElementById("left_library");
                lib.innerHTML = `<li class="lib-itm"><a href="https://github.com/dovskyi" target="_blank" style="background:var(--gray-light);">Github</a></li>`;

                document.body.insertAdjacentElement("beforeend", footer);
                document.getElementById("title_header").insertAdjacentElement("afterend", header);

                entry.remove();
        }
        // Source - https://stackoverflow.com/a/4508751
        // Posted by wombleton, modified by community. See post 'Timeline' for change history
        // Retrieved 2026-08-01, License - CC BY-SA 3.0
        history.pushState("", document.title, window.location.pathname + window.location.search);
}

async function fetch_data(entry_id) {
        return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.responseType = 'text';

                const data = entry_id;

                xhr.open('POST', '/src/utils/projects_fetch_entry.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onreadystatechange = function() {
                        if (xhr.readyState == xhr.DONE){
                                if (xhr.status === 200) {
                                        const response = JSON.parse(xhr.responseText);
                                        if (response.error) {
                                                resolve(false);
                                        }
                                        resolve({
                                                title: response.title,
                                                abstract: response.abstract,
                                                post_date: response.post_date,
                                                modify_date: response.modify_date,
                                                content: response.content,
                                                brief: response.brief,
                                                image: response.image,
                                                tags: response.tags
                                        });

                                }
                        }
                };
                xhr.send(JSON.stringify(data));
        });
}

async function render_entry(entry_id) {
        const existing_cont = document.getElementById("projects_window");
        if (existing_cont) {
                existing_cont.remove();
        }

        const entry = await fetch_data(entry_id);
        if (!entry || entry === undefined) {
                console.log("failed to fetch");
                return_back();
                return;
        }

        let edit_button = '';
        let entry_abstract = '';
        let entry_image = '';
        let entry_date = '';
        let entry_tags = '';
        let entry_brief = '';

        if (entry.image !== null) {
                entry_image = `<img src="${entry.image}">`;
        }

        if (entry.brief !== null) {
                entry_brief = `<span>${entry.brief}</span>`;
        }

        if (entry.abstract !== null) {
                entry_abstract = marked.parse(entry.abstract);
        }
        if (entry.modify_date !== null && entry.modify_date !== entry.post_date) {
                entry_date = `<div class="row"><span>Created:</span><span style="margin-left:auto;">${entry.post_date}</span></div>
                              <div class="row"><span>Updated:</span><span style="margin-left:auto;">${entry.modify_date}</span></div>`;
        } else {
                entry_date = `<span>Created:${entry.post_date}</span>`;
        }
        for (const tag of entry.tags) {
                if (tag !== 'program') {
                        if (tag === 'unfinished') {
                                entry_tags += `<span style="background:var(--red);">${tag}</span>`;
                        } else {
                                entry_tags += `<span>${tag}</span>`;

                        }
                }
        }

        if (usr_role === 'root') {
                edit_button = `<li class="lib-itm"><a href="/src/utils/projects_edit.php?entry_id=${entry_id}">EDIT</a></li>
                               <li class="lib-itm"><a href="/src/utils/projects_delete.php?entry_id=${entry_id}">DELETE</a></li>`;
        }

        const entry_string = `
        <div id="projects_window" tabindex="0">
                <div class="container">

                </div>
                <div style="background:var(--gray);padding:20px;">
                <div class="container">
                        <div class="project_entry">
                        <div class="project_head">
                        <div class="project_title">${entry.title}</div>
                        <div class="project_brief">${entry_brief}</div>
                        </div>
                        <div class="row">
                        <div class="project_abstract">${entry_abstract}</div>
                        <div class="project_image">${entry_image}</div>
                        </div>
                        <div class="project_meta row">
                                <div class="project_tags">
                                        ${entry_tags}
                                </div>
                                <div class="project_date">
                                        ${entry_date}
                                </div>
                        </div>
                        </div>
                </div>
                </div>
                <div class="container">
                <div class="row">
                <div class="project_margin">
                        <div id="project_tree" class="project_tree">
                                <span class="tree_title">CONTENTS</span>
                        </div>
                </div>
                <div id="main_content" class="project_entry" style="margin-left:0;">

                        <div class="project_content">
                                ${marked.parse(entry.content)}
                        </div>
                </div>
                </div>
                </div>
                <div class="pre-footer" style="margin-top: 100px;"></div>
        </div>
        `;
        document.body.insertAdjacentHTML("afterbegin", entry_string);
        const click_area = document.getElementById("clickable_area");
        const project_area = document.getElementById("projects_window");
        const content_div = document.getElementById("main_content");
        const tree = document.getElementById("project_tree");

        const content_sections = content_div.querySelectorAll("h1");
        let index = 0;
        for (const section of content_sections) {
                index++;
                section.id = `section_h1-${index}`;

                const heading = document.createElement("span");
                heading.classList.add("table_section");
                heading.textContent = section.textContent;
                heading.dataset.target = section.id;

                tree.appendChild(heading);
        }

        tree.addEventListener("click", function(event) {
                const section = event.target.closest(".table_section")
                if (!section) {
                        console.log("fail");
                        return;
                }

                const target = document.getElementById(section.dataset.target);
                target.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
                target.classList.add("flash_color");
                setTimeout(() => {
                        target.classList.remove("flash_color");
                }, 2000);
        });

        //random messy stuff
        const footer = document.getElementById("page_footer");
        const header = document.getElementById("navbar_tools");
        document.getElementById("library_tools").style.background = "white";

        project_area.insertAdjacentElement("afterbegin", header);
        project_area.insertAdjacentElement("beforeend", footer);

        const lib = document.getElementById("left_library");
        lib.innerHTML = `${edit_button}<li class="lib-itm"><a id="page_return">BACK</a></li>`;

        project_area.focus();

        document.getElementById("page_return").addEventListener("click", function(event) {
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
                return_back();
                document.body.classList.add('no-scroll');
                render_entry(entry_id);
        } else {
                return_back();
        }
});

function listeners_init(entry_data) {
        window.addEventListener('DOMContentLoaded', function(event) {
                document.getElementById("projects_container").addEventListener('click', function(event) {
                        const entry_div = event.target.closest('.entry_container');
                        if (!entry_div) {
                                return;
                        }

                        const entry_id = parseInt(entry_div.dataset.entry_id, 10);

                        document.body.classList.add('no-scroll');
                        render_entry(entry_id);
                });
                document.getElementById("filter_expand").addEventListener('click', function(event) {
                        const button = document.getElementById('filter_expand');
                        const container = document.getElementById("tag_container");
                        if (container.style.display === 'none') {
                                container.style.display = '';
                                aic_rearrange(cols-1, "projects_container");
                                button.querySelector('span').innerHTML = "Collapse";
                        } else {
                                container.style.display = 'none';
                                aic_rearrange(cols, "projects_container");
                                button.querySelector('span').innerHTML = "Expand";
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


        });
}
