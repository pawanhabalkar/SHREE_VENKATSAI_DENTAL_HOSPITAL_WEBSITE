<?php

session_start();

require_once "../auth/patient_auth.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid Request");
}

$patient_id = $_SESSION["patient_id"];
$appointment_id = intval($_POST["appointment_id"] ?? 0);

if (empty($appointment_id)) {
    echo "missing_id";
    exit;
}

// Ownership check baked into the WHERE clause —
// a patient can only ever cancel THEIR OWN appointment,
// even if they tamper with the appointment_id in the request.
$sql = "UPDATE appointments
        SET status = 'Cancelled', updated_at = NOW()
        WHERE id = ? AND patient_id = ? AND status = 'Booked'";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $appointment_id, $patient_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) === 1) {
    echo "success";
} else {
    echo "cancel_failed_or_not_allowed";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>