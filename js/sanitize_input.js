function sanitize(ele){
        const input = document.getElementById(ele);
        const allowedKeys = new Set([
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '_', '.',
                'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
                'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Enter']);
        input.addEventListener('keydown', function(event) {
                if (allowedKeys.has(event.key)){
                        return;
                }
                if ((event.ctrlKey || event.metaKey) && allowedKeys.has(event.key.toLowerCase())) {
                        return;
                }

                event.preventDefault();
        });

        input.addEventListener('paste', function(event) {
                const content = event.clipboardData.getData('text');
                const content_arr = content.split('');
                if (!content_arr.every(function(char) {
                        return allowedKeys.has(char);})){
                        event.preventDefault();
                }
        });
}

function sanitize_psswd(ele){
        const input = document.getElementById(ele);
        const allowedKeys = new Set([
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
                '_', '.', '$', '#', '!', '%', '&', '?',
                'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
                'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
                'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Enter']);
        input.addEventListener('keydown', function(event) {
                if (allowedKeys.has(event.key)){
                        return;
                }
                if ((event.ctrlKey || event.metaKey) && allowedKeys.has(event.key.toLowerCase())) {
                        return;
                }

                event.preventDefault();
        });

        input.addEventListener('paste', function(event) {
                const content = event.clipboardData.getData('text');
                const content_arr = content.split('');
                if (!content_arr.every(function(char) {
                        return allowedKeys.has(char);})){
                        event.preventDefault();
                }
        });
}

