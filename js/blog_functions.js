function edit_post(button){
        const container = button.closest(".blog_js_wrapper");

        const blog_container = container.querySelector(".blog_container");
        const blog_header = container.querySelector(".blog_header");
        const blog_body = container.querySelector(".blog_body");
        const blog_comments = container.querySelector(".blog_comments")

        if (verify_user(button.dataset.blog_id)) {

                blog_container.style.display = 'none';

                const form_string = `<form class="edit_blog_form">
                                <div class="error_div" id="blog_edit_error" style="display:none;"></div>
                                <div class="blog_editor_header"><b>Editing a blog...</b></div>
                                <div class="row blog_input_container" style="margin:0;">
                                        <input type="text" id="blog_title_edit" class="title_input" maxlength="200" required placeholder="New title..."></input>
                                        <button class="submit_blog" type="button" onclick="edit_blog_cancel(this)" style="background-image: url('/misc/icons/cancel.png');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                        <button class="submit_blog" type="button" onclick="edit_blog_submit(this)" style="background-image: url('/misc/icons/submit.png');background-position: center;background-repeat: no-repeat;background-size: cover; border-style: solid; border-width: 0 0 0 1px; border-color: var(--light-blue-dark); "></button>
                                </div>
                                <textarea id="edit_text_area"></textarea>
                        </form>`
                container.insertAdjacentHTML('beforeend', form_string);

                const easyMDE = new EasyMDE({
                        element: document.getElementById('edit_text_area'),
                        spellChecker: false,
                        minHeight: "70px",
                        placeholder: 'Ctrl+P for preview.\n(Math can\'t be previewed without reloading the page).'
                });
        }
        else {

        }
}

function edit_blog_cancel(button){
        const container = button.closest(".blog_js_wrapper");
        const blog_container = container.querySelector(".blog_container");
        const form = container.querySelector(".edit_blog_form");

        blog_container.style.display = '';
        form.remove();

}

function delete_post(button){

}

function toggle_blog(button){
        const container = button.closest(".blog_container");

        const blog_header = container.querySelector(".blog_header");
        const blog_body = container.querySelector(".blog_body");
        const blog_comments = container.querySelector(".blog_comments")

        if (blog_body.style.display != 'none'){
                blog_body.style.display = 'none';
                blog_comments.style.display = 'none';
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
                container.style.margin = '';
                button.style.backgroundImage = 'url(\'/misc/icons/collapse.png\')';
        }
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

                        var new_blog = `
                        <div class="blog_container">
                                        <div class="blog_header">
                                                <h2 class="blog_title">${title}</h2>
                                                <div class="row">
                                                        <span class="author_name"><b>${String(response.role).charAt(0).toUpperCase() + String(response.role).slice(1)}::${response.author}</b></span>
                                                        <span class="post_date">Posted: ${response.post_date}</span>
                                                        <div style="margin: 0 0 0 auto;">
                                                                <button class="modify_post" style="background-image: url('/misc/icons/trashbin.ico');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                                <button class="modify_post" style="background-image: url('/misc/icons/edit.png?v=1');background-position: center;background-repeat: no-repeat;background-size: cover;"></button>
                                                        </div>
                                                </div>
                                        </div>
                                        <div class="blog_body">
                                                ${response.content}
                                        </div>
                                        <div class="blog_comments">
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
