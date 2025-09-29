<?php
session_start();
session_destroy();
echo "<script>redirectTo('index.php');</script>";
?>
<script>
    function redirectTo(url) {
        window.location.href = url;
    }
</script>
