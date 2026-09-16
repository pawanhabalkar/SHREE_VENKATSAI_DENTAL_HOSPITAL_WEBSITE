<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";
require_once "../includes/csrf.php";
require_once "../includes/booking_validation.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["result" => "error", "error" => "invalid_request"]);
    exit();
}

require_csrf();

$patient_id    = intval($_POST["patient_id"] ?? 0);
$department_id = intval($_POST["department_id"] ?? 0);
$doctor_id     = intval($_POST["doctor_id"] ?? 0);
$appointment_date = trim($_POST["appointment_date"] ?? "");
$appointment_time = trim($_POST["appointment_time"] ?? "");
$reason = trim($_POST["reason"] ?? "");
$status = trim($_POST["status"] ?? "Booked");

$allowedStatuses = ["Booked", "Checked-in", "Waiting", "With Doctor", "Completed", "Cancelled", "No-show"];

if (empty($patient_id) || empty($department_id) || empty($doctor_id)
    || empty($appointment_date) || empty($appointment_time)) {
    echo json_encode(["result" => "error", "error" => "missing_fields"]);
    exit();
}

if (!in_array($status, $allowedStatuses, true)) {
    echo json_encode(["result" => "error", "error" => "invalid_status"]);
    exit();
}

if (!is_valid_appointment_date($appointment_date)) {
    echo json_encode(["result" => "error", "error" => "invalid_date"]);
    exit();
}

$normalizedTime = normalize_and_check_slot($appointment_time);
if ($normalizedTime === null) {
    echo json_encode(["result" => "error", "error" => "invalid_time_slot"]);
    exit();
}
$appointment_time = $normalizedTime;

$deptDoctorError = validate_department_and_doctor($conn, $department_id, $doctor_id);
if ($deptDoctorError !== null) {
    echo json_encode(["result" => "error", "error" => $deptDoctorError]);
    exit();
}

// Confirm the patient exists before we reference them.
$patStmt = mysqli_prepare($conn, "SELECT full_name FROM patients WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($patStmt, "i", $patient_id);
mysqli_stmt_execute($patStmt);
$patientRow = mysqli_fetch_assoc(mysqli_stmt_get_result($patStmt));
mysqli_stmt_close($patStmt);

if (!$patientRow) {
    echo json_encode(["result" => "error", "error" => "patient_not_found"]);
    exit();
}
$patient_name = $patientRow["full_name"] ?? "";

if (strlen($reason) > 255) {
    $reason = substr($reason, 0, 255);
}

mysqli_begin_transaction($conn);

try {

    // -----------------------------------------------------------
    // SLOT CONFLICT CHECK — same doctor, same date/time, not
    // cancelled/no-show. Row-locked so two desk bookings can't
    // race each other onto the same slot.
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
            (patient_id, patient_name, doctor_id, department_id, appointment_date, appointment_time, reason, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception("database_error");
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

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("booking_failed");
    }

    $new_appointment_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    echo json_encode([
        "result" => "success",
        "appointment_id" => $new_appointment_id
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);
    // Never leak mysqli_stmt_error() to the client — only our own
    // known error codes.
    $knownErrors = ["slot_conflict", "database_error", "booking_failed"];
    $error = in_array($e->getMessage(), $knownErrors, true) ? $e->getMessage() : "booking_failed";
    echo json_encode(["result" => "error", "error" => $error]);

}

mysqli_close($conn);
?>
