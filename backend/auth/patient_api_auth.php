<?php

// Session guard for the patient portal's AJAX/JSON API endpoints
// (backend/patient/*.php, backend/public/cancel_appointment.php).
//
// Every one of those files calls session_start() itself BEFORE
// requiring this, so we only start a session here if one isn't
// already active.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    !isset($_SESSION["patient_id"])
) {
    // These endpoints are consumed via fetch() and always expect
    // JSON, so respond the same way reception_auth.php does
    // (JSON + 401) instead of an HTTP redirect, which fetch()
    // doesn't follow the way a normal page navigation would.
    header("Content-Type: application/json");
    http_response_code(401);
    echo json_encode(["error" => "not_logged_in"]);
    exit();
}

if ($_SESSION["role"] !== "patient") {
    header("Content-Type: application/json");
    http_response_code(403);
    echo json_encode(["error" => "not_authorized"]);
    exit();
}

?>
