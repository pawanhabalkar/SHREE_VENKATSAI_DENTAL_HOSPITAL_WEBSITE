<?php

// FIX: only start a session if one isn't already active.
// Every backend/patient/*.php file calls session_start() itself
// BEFORE requiring this file, so calling session_start() again here
// unconditionally caused a PHP Notice ("session already active"),
// which was leaking into JSON/text API responses and breaking
// exact string checks like `result === "success"` in patient.js.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    !isset($_SESSION["patient_id"])
) {
    header("Location: ../../login page/login.html");
    exit();
}

if ($_SESSION["role"] !== "patient") {
    header("Location: ../../login page/login.html");
    exit();
}

?>