<?php

session_start();

$_SESSION = [];

session_destroy();

header("Location: ../../login page/login.html");

exit();

?>