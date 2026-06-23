async function edit_post(button){
        const container = button.closest(".blog_js_wrapper");

        const blog_container = container.querySelector(".blog_container");
        const blog_header = container.querySelector(".blog_header");
        const blog_body = container.querySelector(".blog_body");
        const blog_error = container.querySelector(".error_div");

        blog_error.style.display = 'none';

        //verification purely for visual, user is checked when submitting edit with server
        if (await verify_user(button.dataset.blog_id)) {

                blog_container.style.display = 'none';

                const form_string = `<form class="edit_blog_form">
                                <div class="error_div" id="blog_edit_error" style="display:none;"></div>
                                <div class="blog_editor_header"><b>Editing a blog...</b></div>
                                <div class="row blog_input_container" style="margin:0;">
                                        <input type="text" id="blog_title_edit" class="title_input" maxlength="200" required placeholder="New title..."></input>
                                        <button class="submit_blog" type="button" onclick="edit_blog_cancel(this)" style="background-image: url('/misc/icons/cancel.png');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                        <button class="submit_blog" type="button" data-blog_id="${button.dataset.blog_id}" onclick="edit_blog_submit(this)" style="background-image: url('/misc/icons/submit.png');background-position: center;background-repeat: no-repeat;background-size: cover; border-style: solid; border-width: 0 0 0 1px; border-color: var(--light-blue-dark); "></button>
                                </div>
                                <textarea id="edit_${button.dataset.blog_id}"></textarea>
                        </form>`
                container.insertAdjacentHTML('beforeend', form_string);
                const textareamde = document.getElementById('edit_'+button.dataset.blog_id);
                const title = container.querySelector(".title_input");
                const easyMDE_edit = new EasyMDE({
                        element: document.getElementById('edit_'+button.dataset.blog_id),
                        spellChecker: false,
                        minHeight: "70px",
                        placeholder: 'Ctrl+P for preview.\n(Math can\'t be previewed without reloading the page).'
                });

                textareamde.easyMDE = easyMDE_edit;

                let complete = await load_blog_content(button.dataset.blog_id, easyMDE_edit, title);
                if (!complete){
                        blog_error.style.display = 'flex';
                        blog_error.innerHTML = "Could not fetch blog contents. Returning.";
                        const form = container.querySelector(".edit_blog_form");

                        blog_container.style.display = '';
                        form.remove();
                        return;
                }
        }
        else {
                blog_error.style.display = 'flex';
                blog_error.innerHTML = "You can't interract with this resource";
                return;
        }
}

function delete_comment(button){
        const container = button.closest(".blog_comments");
        const comment_container = button.closest(".comment_container");
        const error = container.querySelector(".error_div");

        const comment_id = button.dataset.comment_id;

        const xhr = new XMLHttpRequest();
        xhr.responseType = "text";

        const data = {
                comment_id: comment_id
                };

        xhr.open('POST', '/src/utils/delete_comment.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onreadystatechange = function() {
                if (xhr.readyState == xhr.DONE){
                        if (xhr.status === 200) {
                                const response = JSON.parse(xhr.responseText);

                                if (response.error) {
                                        error.style.display = 'flex';
                                        error.innerHTML = response.error;
                                        return;
                                }
                                error.style.display = 'none';
                                comment_container.remove();
                                return;
                        }
                        else {
                                error.style.display = 'flex';
                                error.innerHTML = "Server error";
                                return;
                        }
                }
        };
        xhr.send(JSON.stringify(data));
}

function load_comments(button) {
        //Originally everything was supposed to be loaded dynamically,
        //but that would not make sense with my current architecture. This is more efficient.
        const container = button.closest(".blog_comments");

        const comment_section = container.querySelector(".comment_section_wrapper");
        const error = container.querySelector(".error_div");
        const rest = container.querySelector(".comments_rest");

        error.style.display = 'none';
        button.style.display = 'none';
        rest.style.display = '';

        return;
}

function submit_comment(button) {
        const container = button.closest(".blog_comments");

        const comment_section = container.querySelector(".comment_section_wrapper");
        const input = container.querySelector(".comment_text");
        const error = container.querySelector(".error_div");

        const content = input.value;
        const blog_id = button.dataset.blog_id;

        if (content.length <= 0 || content.length > 1000){
                error.style.display = 'flex';
                error.innerHTML = "Content can't be more than 1000 chars.";
                return;
        }

        const xhr = new XMLHttpRequest();
        xhr.responseType = "text";

        const data = {
                content: content,
                blog_id: blog_id
                };

        xhr.open('POST', '/src/utils/submit_comment.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onreadystatechange = function() {
                if (xhr.readyState == xhr.DONE){
                        if (xhr.status === 200) {
                                const response = JSON.parse(xhr.responseText);

                                if (response.error) {
                                        error.style.display = 'flex';
                                        error.innerHTML = response.error;
                                        return;
                                }
                                error.style.display = 'none';
                                input.value = '';
                                if (response.role != 'guest'){
                                        delete_button = `<button class="delete_comm" data-comment_id="${response.comment_id}" onclick="if(confirm(\'Are you sure you want to delete this comment?\')) delete_comment(this);" style="background-image: url('/misc/icons/trash.png?v=0');"></button>`;
                                } else {
                                        delete_button = '';
                                }
                                new_comment = `
                                <div class="comment_container temp_container">
                                <div class="row">
                                                <div class="comment_c1"><span><b>${response.role}::${response.username}</b></span>
                                                <div class="row">
                                                        <span>${response.post_date}</span>${delete_button}
                                                </div>
                                                </div>
                                                <div class="comment_c2">${response.content}</div>
                                </div>
                                </div>
                                `;

                                comment_section.insertAdjacentHTML("beforeend", new_comment);
                                return;
                        }
                        else {
                                error.style.display = 'flex';
                                error.innerHTML = "Server error";
                                return;
                        }
                }
        };
        xhr.send(JSON.stringify(data));
}

function edit_blog_submit(button) {
        const container = button.closest(".blog_js_wrapper");

        const blog_container = container.querySelector(".blog_container");
        const blog_body = container.querySelector(".blog_body");
        const blog_error = container.querySelector(".error_div");
        const form = container.querySelector(".edit_blog_form");
        const blog_header_title = container.querySelector(".blog_title");
        const blog_moddate = container.querySelector(".modify_date");

        const textareamde = form.querySelector('textarea');
        const easyMDE_edit = textareamde.easyMDE;
        const blog_title = container.querySelector(".title_input");

        const title = blog_title.value;
        const content = easyMDE_edit.value();
        const blog_id = button.dataset.blog_id;

        if (title.length <= 0 || title.length > 200 || content.length <= 0 || content.length > 4000){
                blog_error.style.display = 'flex';
                blog_error.innerHTML = "Title can't be more than 200 chars.<br>Content can't be more than 4000 chars.";
                return;
        }

        const xhr = new XMLHttpRequest();
        xhr.responseType = "text";

        const data = {
                title: title,
                content: content,
                blog_id: blog_id
                };

        xhr.open('POST', '/src/utils/edit_blog.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onreadystatechange = function() {
                if (xhr.readyState == xhr.DONE){
                        if (xhr.status === 200) {
                                const response = JSON.parse(xhr.responseText);

                                if (response.error) {
                                        blog_error.style.display = 'flex';
                                        blog_error.innerHTML = response.error;
                                        return;
                                }
                                blog_error.style.display = 'none';
                                blog_container.style.display = '';
                                blog_header_title.innerHTML = title;
                                blog_body.innerHTML = response.content;
                                blog_moddate.innerHTML = "Modified: "+response.moddate;
                                form.remove();
                                return;
                        }
                        else {
                                blog_error.style.display = 'flex';
                                blog_error.innerHTML = "Server error";
                                return;
                        }
                }
        };
        xhr.send(JSON.stringify(data));
}

function load_blog_content(blog_id, easymde, title) {
        return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.responseType = "text";
                const data = {blog_id: blog_id}

                xhr.open('POST', '/src/utils/fetch_blog_data.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onreadystatechange = function(){
                        if (xhr.readyState == XMLHttpRequest.DONE) {
                                if (xhr.status === 200) {
                                        const response = JSON.parse(xhr.responseText);
                                        if (response.error) {
                                                resolve(0);
                                        }
                                        else {
                                                easymde.value(response.content);
                                                title.value = response.title;
                                                resolve(1);
                                        }
                                }
                                else {
                                        resolve(0);
                                }
                        }
                };
                xhr.send(JSON.stringify(data));
        });
}


function edit_blog_cancel(button){
        const container = button.closest(".blog_js_wrapper");
        const blog_container = container.querySelector(".blog_container");
        const form = container.querySelector(".edit_blog_form");
        const blog_error = container.querySelector(".error_div");
        blog_error.style.display = 'none';

        blog_container.style.display = '';
        form.remove();
}

function delete_post(button){
        const container = button.closest(".blog_js_wrapper");
        const blog_id = button.dataset.blog_id;
        const blog_error = container.querySelector(".error_div");
        blog_error.style.display = 'none';

        const xhr = new XMLHttpRequest();
        xhr.responseType = "text";

        const data = {
                blog_id: blog_id
                };

        xhr.open('POST', '/src/utils/delete_blog.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onreadystatechange = function() {
                if (xhr.readyState == xhr.DONE){
                        if (xhr.status === 200) {
                                const response = JSON.parse(xhr.responseText);

                                if (response.error) {
                                        blog_error.style.display = 'flex';
                                        blog_error.innerHTML = response.error;
                                        return;
                                }
                                blog_error.style.display = 'none';
                                container.remove();
                                return;
                        }
                        else {
                                blog_error.style.display = 'flex';
                                blog_error.innerHTML = "Server error";
                                return;
                        }
                }
        };
        xhr.send(JSON.stringify(data));
}

function toggle_blog(button){
        const container = button.closest(".blog_js_wrapper");

        const blog_header = container.querySelector(".blog_header");
        const blog_body = container.querySelector(".blog_body");
        const blog_comments = container.querySelector(".blog_comments");
        const blog_buttons = container.querySelector(".modify_button_wrapper");

        const blog_error = container.querySelector(".error_div");
        blog_error.style.display = 'none';

        if (blog_body.style.display != 'none'){
                blog_body.style.display = 'none';
                blog_comments.style.display = 'none';
                blog_buttons.style.display = 'none';
                blog_header.style.background = 'var(--gray)'
                blog_header.style.borderWidth = '0';
                container.style.margin = '0 0 20px 0';
                button.style.backgroundImage = 'url(\'/misc/icons/expand.png\')';
        }
        else {
                blog_body.style.display = '';
                blog_comments.style.display = '';
                blog_header.style.background = ''
                blog_header.style.borderWidth = '';
                blog_buttons.style.display = '';
                container.style.margin = '';
                button.style.backgroundImage = 'url(\'/misc/icons/collapse.png\')';
        }
}

function toggle_comments(button){
        const container = button.closest(".blog_comments");

        const comment_body = container.querySelector(".comment_body");
        const comment_error = container.querySelector(".error_div");

        comment_error.style.display = 'none';

        if (comment_body.style.display != 'none'){
                comment_body.style.display = 'none';
                button.style.backgroundImage = 'url(\'/misc/icons/expand.png\')';
        }
        else {
                comment_body.style.display = '';
                button.style.backgroundImage = 'url(\'/misc/icons/collapse.png\')';
        }
}

function verify_user(id){
        return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.responseType = "text";
                const data = {blog_id: id}

                xhr.open('POST', '/src/utils/verify_user.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onreadystatechange = function(){
                        if (xhr.readyState == XMLHttpRequest.DONE) {
                                if (xhr.status === 200) {
                                        const response = JSON.parse(xhr.responseText);
                                        if (response.valid){
                                                resolve(1);
                                        }
                                        else {
                                                resolve(0);
                                        }
                                }
                                else {
                                        resolve(0);
                                }
                        }
                };
                xhr.send(JSON.stringify(data));
        });
}

document.getElementById('blog_submit').addEventListener('submit', function(event){
        event.preventDefault();
        const title_field = document.getElementById('blog_title_input');
        const container = document.getElementById('blog_field_container');

        const title = title_field.value;
        const content = easyMDE.value();

        if (title.length <= 0 || title.length > 200 || content.length <= 0 || content.length > 4000){
                const error_div = document.getElementById('blog_submit_error');
                error_div.innerHTML = "Title can't be more than 200 chars.<br>Content can't be more than 4000 chars.";
                error_div.style.display = "block";
                return;
        }

        const xhr = new XMLHttpRequest();
        xhr.responseType = "text";

        const data = {
                title: title,
                content: content};

        xhr.open('POST', '/src/utils/add_blog.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onreadystatechange = function() {
                if (xhr.readyState == xhr.DONE && xhr.status == 200) {
                        const response = JSON.parse(xhr.responseText);

                        if (response.error) {
                                const error_div = document.getElementById('blog_submit_error');
                                error_div.innerHTML = response.error;
                                error_div.style.display = "block";
                                return;
                        }
                        document.getElementById('blog_submit_error').style.display = "none";
                        let edit_buttons = '';
                        if (response.role !== 'guest') {
                                edit_buttons =
                                        `<div class="modify_button_wrapper">
                                                                <button class="modify_post" type="button" onclick="if(confirm('Are you sure you want to delete this blog?')) delete_post(this);" data-blog_id="${response.blog_id}" style="background-image: url('/misc/icons/trashbin.ico');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                                 <button class="modify_post" type="button" onclick="edit_post(this)" data-blog_id="${response.blog_id}" style="background-image: url('/misc/icons/edit.png?v=1');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                        </div>`;
                        }

                        var new_blog = `
                         <div class="blog_js_wrapper">
                         <div class="error_div" id="blog_general_error" style="display:none;"></div>
                        <div class="blog_container">
                                        <div class="blog_header">
                                                <div class="row">
                                                <h2 class="blog_title">${title}</h2>
                                                <button class="collapse_post" type="button" onclick="toggle_blog(this)" style="background-image: url('/misc/icons/collapse.png');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                </div>
                                                <div class="row">
                                                        <span class="author_name"><b>${String(response.role).charAt(0).toUpperCase() + String(response.role).slice(1)}::${response.author}</b></span>
                                                        <span class="post_date">Posted: ${response.post_date}</span>
                                                        <span class="modify_date"></span>
                                                        <div style="margin: 0 0 0 auto;">`+edit_buttons+`
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="blog_body">
                                                ${response.content}
                                        </div>
                                        <div class="blog_comments">
                                        </div>
                        </div>
                        </div>`

                        container.insertAdjacentHTML("afterbegin", new_blog);

                        title_field.value = '';
                        easyMDE.value('');
                }
                else {
                        const error_div = document.getElementById('blog_submit_error');
                        error_div.innerHTML = "Server error, probably on my end";
                        error_div.style.display = "block";
                        return;
                }
        };
        xhr.send(JSON.stringify(data));
});
