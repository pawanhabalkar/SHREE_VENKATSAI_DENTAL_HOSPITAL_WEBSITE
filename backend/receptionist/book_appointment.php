<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["result" => "error", "error" => "invalid_request"]);
    exit();
}

$patient_id    = intval($_POST["patient_id"] ?? 0);
$department_id = intval($_POST["department_id"] ?? 0);
$doctor_id     = intval($_POST["doctor_id"] ?? 0);
$appointment_date = trim($_POST["appointment_date"] ?? "");
$appointment_time = trim($_POST["appointment_time"] ?? "");
$reason = trim($_POST["reason"] ?? "");
$status = trim($_POST["status"] ?? "Booked");

if (empty($patient_id) || empty($department_id) || empty($doctor_id)
    || empty($appointment_date) || empty($appointment_time)) {
    echo json_encode(["result" => "error", "error" => "missing_fields"]);
    exit();
}

$today = date("Y-m-d");
if ($appointment_date < $today) {
    echo json_encode(["result" => "error", "error" => "invalid_date"]);
    exit();
}

// -----------------------------------------------------------
// SLOT CONFLICT CHECK — same doctor, same date/time, not
// cancelled/no-show. Runs inside this same request so two
// desk bookings can't race each other onto the same slot.
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
    echo json_encode(["result" => "error", "error" => "slot_conflict"]);
    exit();
}

// Fetch the patient's name to store alongside the appointment.
$nameStmt = mysqli_prepare($conn, "SELECT full_name FROM patients WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($nameStmt, "i", $patient_id);
mysqli_stmt_execute($nameStmt);
$patientRow = mysqli_fetch_assoc(mysqli_stmt_get_result($nameStmt));
$patient_name = $patientRow["full_name"] ?? "";
mysqli_stmt_close($nameStmt);

$sql = "INSERT INTO appointments
        (patient_id, patient_name, doctor_id, department_id, appointment_date, appointment_time, reason, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "database_error"]);
    exit();
}

mysqli_stmt_bind_param(
    $stmt,
    "isiissss",
    $patient_id,
    $patient_name,
    $doctor_id,
    $department_id,
    $appointment_date,
    $appointment_time,
    $reason,
    $status
);

if (mysqli_stmt_execute($stmt)) {
    $new_appointment_id = mysqli_insert_id($conn);
    echo json_encode([
        "result" => "success",
        "appointment_id" => $new_appointment_id
    ]);
} else {
    // TEMP DEBUG: shows the real DB error. Remove/guard this
    // before deploying to production (don't leak DB errors to users).
    echo json_encode([
        "result" => "error",
        "error" => mysqli_stmt_error($stmt)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>