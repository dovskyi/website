function password_toggle(ele, field_id) {
        const field = document.getElementById(field_id);

        if (field.type === "text"){
                field.type = "password";
                ele.innerHTML = "&#9675;"
        }
        else {
                field.type = "text";
                ele.innerHTML = "&#9679;"
        }
}
