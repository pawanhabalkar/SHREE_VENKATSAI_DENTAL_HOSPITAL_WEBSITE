<?php

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";
require_once "../includes/csrf.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid Request");
}

require_csrf();

$patient_id = $_SESSION["patient_id"];
$notification_id = intval($_POST["notification_id"] ?? 0);

if (empty($notification_id)) {
    echo "missing_id";
    exit;
}

// Ownership check again — can't mark someone else's notification as read
$sql = "UPDATE notifications
        SET is_read = 1
        WHERE id = ? AND patient_id = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $notification_id, $patient_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) >= 1) {
    echo "success";
} else {
    echo "not_found_or_already_read";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>