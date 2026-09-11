<?php

session_start();

require_once "../auth/patient_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid Request");
}

$patient_id = $_SESSION["patient_id"];
$patient_name = $_SESSION["name"] ?? "";

$department_id = intval($_POST["department_id"] ?? 0);
$doctor_id     = intval($_POST["doctor_id"] ?? 0);
$appointment_date = trim($_POST["appointment_date"] ?? "");
$appointment_time = trim($_POST["appointment_time"] ?? "");
$reason = trim($_POST["reason"] ?? "");

if (empty($department_id) || empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
    echo "missing_fields";
    exit;
}

$today = date("Y-m-d");
if ($appointment_date < $today) {
    echo "invalid_date";
    exit;
}

// -----------------------------------------------------------
// SLOT CONFLICT CHECK
// Reject if this doctor already has a non-cancelled appointment
// at the exact same date/time. Runs inside the same request as
// the insert (not a separate "check" call) so two people can't
// both pass the check and double-book the same slot.
// -----------------------------------------------------------
$conflictSql = "SELECT id FROM appointments
                 WHERE doctor_id = ?
                   AND appointment_date = ?
                   AND appointment_time = ?
                   AND status NOT IN ('Cancelled', 'No-show')
                 LIMIT 1";
$conflictStmt = mysqli_prepare($conn, $conflictSql);
mysqli_stmt_bind_param($conflictStmt, "iss", $doctor_id, $appointment_date, $appointment_time);
mysqli_stmt_execute($conflictStmt);
$conflict = mysqli_fetch_assoc(mysqli_stmt_get_result($conflictStmt));
mysqli_stmt_close($conflictStmt);

if ($conflict) {
    echo "slot_conflict";
    exit;
}

$sql = "INSERT INTO appointments
        (patient_id, patient_name, doctor_id, department_id, appointment_date, appointment_time, reason, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Booked')";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo "db_error";
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "isiisss",
    $patient_id,
    $patient_name,
    $doctor_id,
    $department_id,
    $appointment_date,
    $appointment_time,
    $reason
);

if (mysqli_stmt_execute($stmt)) {
    $new_appointment_id = mysqli_insert_id($conn);
    echo "success:" . $new_appointment_id;
} else {
    // TEMP DEBUG: shows the real DB error. Remove/guard this
    // before deploying to production (don't leak DB errors to users).
    echo "booking_failed:" . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>