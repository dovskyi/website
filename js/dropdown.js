function dropdown(id_drop, id_sym, id_button) {
        let item = document.getElementById(id_drop);
        let sym = document.getElementById(id_sym);
        let button = document.getElementById(id_button);
        if (item.style.display === "block"){
                item.style.display = "none";
                sym.innerHTML = "&#x25BC;";
        }
        else {
                item.style.display = "block";
                sym.innerHTML= "&#x25B2;";
        }
}

function close_onclick(id_drop, id_sym, id_button) {
        let item = document.getElementById(id_drop);
        let sym = document.getElementById(id_sym);
        let button = document.getElementById(id_button);
        document.addEventListener("click", function(event) {
                if (item && event.target !== button) {
                        item.style.display = "none";
                        sym.innerHTML= "&#x25BC;";
                }
        });
}
