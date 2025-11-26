<?php
session_start();
session_destroy();
?>
<script>
    // Close the current browser tab
    window.close();

    // For browsers that block window.close(), redirect to about:blank
    window.location.href = "about:blank";
</script>
