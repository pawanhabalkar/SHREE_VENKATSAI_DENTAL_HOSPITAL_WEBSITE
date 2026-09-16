<?php

// Issues (or returns the existing) CSRF token for the current
// session. Called on page load by static HTML pages (login,
// public booking, receptionist desk) that can't embed a PHP-
// rendered token directly the way patient_index.php does.
session_start();

require_once "../includes/csrf.php";

header("Content-Type: application/json");
echo json_encode(["csrf_token" => csrf_token()]);

?>
