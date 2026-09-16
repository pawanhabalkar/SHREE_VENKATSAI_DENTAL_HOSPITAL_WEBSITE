<?php

// Session guard for full PAGE loads in the patient portal
// (currently just patient_index/patient_index.php). A browser
// navigating here with no valid session should be redirected to
// the login page, not shown a JSON error.
//
// AJAX/fetch endpoints under backend/patient/*.php should require
// patient_api_auth.php instead (JSON + 401/403), not this file.
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
