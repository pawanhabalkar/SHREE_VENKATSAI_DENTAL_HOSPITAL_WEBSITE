<?php

session_start();

require_once "reception_auth.php";

// If we got here, reception_auth.php already confirmed the session
// belongs to a logged-in receptionist. Just confirm it back to the JS.
header("Content-Type: application/json");
echo json_encode([
    "logged_in" => true,
    "email" => $_SESSION["email"]
]);

?>