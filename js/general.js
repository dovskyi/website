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
