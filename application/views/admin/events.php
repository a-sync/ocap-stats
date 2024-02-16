<?php defined('BASEPATH') or exit('No direct script access allowed');
?>
<div id="wip"></div>
<script>
function fillWithEmojis(containerId) {
    const container = document.getElementById(containerId);
    function animate() {
        const emoji = String.fromCodePoint(Math.floor(
            Math.random() * (128512 - 127744 + 1) + 127744
        ));
        container.textContent += emoji;
        requestAnimationFrame(animate);
    }
    requestAnimationFrame(animate);
}
fillWithEmojis('wip');
</script>