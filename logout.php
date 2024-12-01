<?php
session_start();
session_destroy();
header("Location: sign-up.php");
exit();
?>