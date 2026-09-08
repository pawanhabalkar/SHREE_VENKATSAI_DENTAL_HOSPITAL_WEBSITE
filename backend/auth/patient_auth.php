<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"])
) {
    header("Location: ../../login.php");
    exit();
}

if ($_SESSION["role"] !== "patient") {
    header("Location: ../../login.php?error=unauthorized");
    exit();
}

?>