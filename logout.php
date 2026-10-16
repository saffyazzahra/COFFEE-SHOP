<?php
// logout.php
session_start();
session_unset();   //clear session variables
session_destroy(); //destroys the session on the server

header("Location: login.php");
exit;
?>