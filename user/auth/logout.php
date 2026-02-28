<?php
session_name('TUNETROVE_USER_SESSION');
session_start();
session_unset();
session_destroy();

header("Location: ../index.php");
exit();
?>
