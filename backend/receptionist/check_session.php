<?php

session_start();

require_once "reception_auth.php";
require_once "../includes/csrf.php";

// If we got here, reception_auth.php already confirmed the session
// belongs to a logged-in receptionist. Confirm it back to the JS,
// and hand back a CSRF token for this session so recption.js can
// attach it to every state-changing request it makes afterward.
header("Content-Type: application/json");
echo json_encode([
    "logged_in" => true,
    "email" => $_SESSION["email"],
    "csrf_token" => csrf_token()
]);

?>