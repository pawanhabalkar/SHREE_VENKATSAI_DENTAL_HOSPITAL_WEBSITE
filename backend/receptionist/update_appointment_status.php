<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";
require_once "../includes/csrf.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "invalid_request"]);
    exit();
}

require_csrf();

$appointment_id = intval($_POST["appointment_id"] ?? 0);
$status         = trim($_POST["status"] ?? "");

// Matches the `appointments.status` enum in the schema. Reception
// typically moves a patient through Checked-in -> Waiting -> With Doctor
// -> Completed, or marks Cancelled / No-show as needed.
$allowedStatuses = [
    "Booked",
    "Checked-in",
    "Waiting",
    "With Doctor",
    "Completed",
    "Cancelled",
    "No-show"
];

if (empty($appointment_id) || $status === "") {
    echo json_encode(["error" => "missing_fields"]);
    exit();
}

if (!in_array($status, $allowedStatuses, true)) {
    echo json_encode(["error" => "invalid_status"]);
    exit();
}

$sql = "UPDATE appointments SET status = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "database_error"]);
    exit();
}

mysqli_stmt_bind_param($stmt, "si", $status, $appointment_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) === 0) {
        echo json_encode(["error" => "appointment_not_found"]);
    } else {
        echo json_encode(["result" => "success"]);
    }
} else {
    echo json_encode(["error" => "update_failed"]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>