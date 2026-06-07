function verify_user(id){
        const xhr = new XMLHttpRequest();
        xhr.responseType = "text";
        const data = {blog_id: id}

        xhr.open('POST', '/src/utils/verify_user.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify(data));

        if (xhr.status == 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.valid){
                        return 1;
                }
                else {
                        return 0;
                }
        }
        return 0;
}

