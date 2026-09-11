<?php

// Session guard for the receptionist portal.
// Reception staff log in through the `users` table using the dedicated
// 'receptionist' role (per SRS section 4, RBAC table — Receptionist is
// a distinct role from Admin, with its own login and reduced privileges).
//
// Every backend/reception/*.php file should call session_start() itself
// BEFORE requiring this file (same convention as patient_auth.php), so
// we only start a session here if one isn't already active.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"])
) {
    header("Content-Type: application/json");
    http_response_code(401);
    echo json_encode(["error" => "not_logged_in"]);
    exit();
}

if ($_SESSION["role"] !== "receptionist") {
    header("Content-Type: application/json");
    http_response_code(403);
    echo json_encode(["error" => "not_authorized"]);
    exit();
}

?>