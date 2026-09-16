<?php

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";
require_once "../includes/csrf.php";
require_once "../includes/booking_validation.php";

// NOTE: this endpoint's response is plain text (not JSON) — patient.js
// reads it with response.text() and checks for a "success" prefix.
// Keeping that contract so the existing front-end keeps working.

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("invalid_request");
}

require_csrf();

$patient_id = $_SESSION["patient_id"];
$patient_name = $_SESSION["name"] ?? "";

$department_id = intval($_POST["department_id"] ?? 0);
$doctor_id     = intval($_POST["doctor_id"] ?? 0);
$appointment_date = trim($_POST["appointment_date"] ?? "");
$appointment_time = trim($_POST["appointment_time"] ?? "");
$reason = trim($_POST["reason"] ?? "");
$notes = trim($_POST["notes"] ?? "");

if (empty($department_id) || empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
    echo "missing_fields";
    exit;
}

if (!is_valid_appointment_date($appointment_date)) {
    echo "invalid_date";
    exit;
}

$normalizedTime = normalize_and_check_slot($appointment_time);
if ($normalizedTime === null) {
    echo "invalid_time_slot";
    exit;
}
$appointment_time = $normalizedTime;

$deptDoctorError = validate_department_and_doctor($conn, $department_id, $doctor_id);
if ($deptDoctorError !== null) {
    echo $deptDoctorError;
    exit;
}

if (strlen($reason) > 255) {
    $reason = substr($reason, 0, 255);
}
if (strlen($notes) > 2000) {
    $notes = substr($notes, 0, 2000);
}

mysqli_begin_transaction($conn);

try {

    // -----------------------------------------------------------
    // SLOT CONFLICT CHECK (with a row lock so two requests can't
    // both pass the check and double-book the same slot).
    // -----------------------------------------------------------
    $conflictSql = "SELECT id FROM appointments
                     WHERE doctor_id = ?
                       AND appointment_date = ?
                       AND appointment_time = ?
                       AND status NOT IN ('Cancelled', 'No-show')
                     LIMIT 1
                     FOR UPDATE";
    $conflictStmt = mysqli_prepare($conn, $conflictSql);
    mysqli_stmt_bind_param($conflictStmt, "iss", $doctor_id, $appointment_date, $appointment_time);
    mysqli_stmt_execute($conflictStmt);
    $conflict = mysqli_fetch_assoc(mysqli_stmt_get_result($conflictStmt));
    mysqli_stmt_close($conflictStmt);

    if ($conflict) {
        throw new Exception("slot_conflict");
    }

    $sql = "INSERT INTO appointments
            (patient_id, patient_name, doctor_id, department_id, appointment_date, appointment_time, reason, notes, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Booked')";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception("db_error");
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
        $notes
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("booking_failed");
    }

    $new_appointment_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    echo "success:" . $new_appointment_id;

} catch (Exception $e) {

    mysqli_rollback($conn);
    // Generic, non-DB-revealing error code — never leak
    // mysqli_stmt_error() to the client.
    echo $e->getMessage();

}

mysqli_close($conn);
?>
