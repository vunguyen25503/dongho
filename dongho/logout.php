<?php
session_start();
session_unset(); // Clear session variables
session_destroy(); // Destroy the session
header("Location: dongho.php"); // Redirect to homepage
exit;
?>
