<?php

/*
    PUBLIC ONLINE BOOKING (no login required)
    -------------------------------------------
    Used by book-appointment/book-appointment.html — a brand-new or
    returning visitor booking from the public website, not logged
    into the Patient Portal.

    Per SRS 4.3.1: a patient profile + permanent unique MRN is
    created automatically on first booking. A returning patient is
    matched by mobile number — no duplicate profile is created.
*/

session_start();

require_once "../config/database.php";
require_once "../includes/csrf.php";
require_once "../includes/booking_validation.php";
require_once "../includes/sequence.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["result" => "error", "error" => "invalid_request"]);
    exit();
}

require_csrf();

$full_name = trim($_POST["full_name"] ?? "");
$mobile    = trim($_POST["mobile"] ?? "");
$email     = trim($_POST["email"] ?? "");
$age       = trim($_POST["age"] ?? "");
$gender    = trim($_POST["gender"] ?? "");
$password  = $_POST["password"] ?? "";
$area      = trim($_POST["area"] ?? "");
$address   = trim($_POST["address"] ?? "");
$department_id = intval($_POST["department_id"] ?? 0);
$doctor_id     = intval($_POST["doctor_id"] ?? 0);
$appointment_date = trim($_POST["appointment_date"] ?? "");
$appointment_time = trim($_POST["appointment_time"] ?? "");
$reason = trim($_POST["reason"] ?? "");

if (empty($full_name) || empty($mobile) || empty($department_id)
    || empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
    echo json_encode(["result" => "error", "error" => "missing_fields"]);
    exit();
}

if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
    echo json_encode(["result" => "error", "error" => "invalid_mobile"]);
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

if (strlen($reason) > 255) {
    $reason = substr($reason, 0, 255);
}

// Approximate date_of_birth from the "age" field the public form
// collects (the patients table only has date_of_birth, same as the
// receptionist desk form — this keeps both booking paths storing
// age information the same way instead of silently discarding it).
$dobFromAge = null;
if ($age !== "" && ctype_digit($age) && intval($age) > 0 && intval($age) < 130) {
    $dobFromAge = (intval(date("Y")) - intval($age)) . "-01-01";
}

mysqli_begin_transaction($conn);

try {

    // 1. Look for an existing patient by mobile number (dedup — SRS 4.3.1 AC1)
    $findSql = "SELECT id, mrn, full_name FROM patients WHERE mobile = ? LIMIT 1 FOR UPDATE";
    $findStmt = mysqli_prepare($conn, $findSql);
    mysqli_stmt_bind_param($findStmt, "s", $mobile);
    mysqli_stmt_execute($findStmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($findStmt));
    mysqli_stmt_close($findStmt);

    if ($existing) {

        // Returning patient — reuse the existing profile. We do NOT
        // let a public, unauthenticated booking request overwrite an
        // existing patient's stored details (name/DOB/etc.) — that
        // would let anyone who knows a patient's mobile number tamper
        // with their record. Only the mobile number is used to match.
        $patient_id = $existing["id"];
        $mrn = $existing["mrn"];

    } else {

        // New visitor — create login + patient profile, same pattern
        // as the receptionist's register_patient.php.
        $userEmail = $email !== "" ? $email : ("public_" . $mobile . "@no-email.local");

        // Use the password the patient set on the booking form so they
        // can actually log in later. Fall back to a random one only if
        // somehow no password was sent (defensive, shouldn't happen
        // since the form requires it).
        $passwordToUse = $password !== "" ? $password : bin2hex(random_bytes(6));
        $passwordHash = password_hash($passwordToUse, PASSWORD_DEFAULT);

        $userSql = "INSERT INTO users (email, password, role, status)
                    VALUES (?, ?, 'patient', 'active')";
        $userStmt = mysqli_prepare($conn, $userSql);
        mysqli_stmt_bind_param($userStmt, "ss", $userEmail, $passwordHash);

        if (!mysqli_stmt_execute($userStmt)) {
            throw new Exception("email_already_registered");
        }

        $user_id = mysqli_insert_id($conn);
        mysqli_stmt_close($userStmt);

        $mrn = generate_sequential_code($conn, "patients", "mrn", "SVMSDH-", 8, 6);

        $genderParam = $gender !== "" ? $gender : null;

        $patientSql = "INSERT INTO patients
                            (user_id, mrn, full_name, mobile, gender, email, area, address, date_of_birth)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $patientStmt = mysqli_prepare($conn, $patientSql);
        mysqli_stmt_bind_param(
            $patientStmt,
            "isssssss s",
            $user_id,
            $mrn,
            $full_name,
            $mobile,
            $genderParam,
            $userEmail,
            $area,
            $address,
            $dobFromAge
        );

        if (!mysqli_stmt_execute($patientStmt)) {
            throw new Exception("database_error");
        }

        $patient_id = mysqli_insert_id($conn);
        mysqli_stmt_close($patientStmt);

    }

    // 2. Slot conflict check — same doctor, same date/time,
    //    not cancelled/no-show. Row-locked so two public bookings
    //    can't race onto the same slot.
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

    // 3. Book the appointment.
    $apptSql = "INSERT INTO appointments
                (patient_id, patient_name, doctor_id, department_id, appointment_date, appointment_time, reason, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Booked')";
    $apptStmt = mysqli_prepare($conn, $apptSql);
    mysqli_stmt_bind_param(
        $apptStmt,
        "isiisss",
        $patient_id,
        $full_name,
        $doctor_id,
        $department_id,
        $appointment_date,
        $appointment_time,
        $reason
    );

    if (!mysqli_stmt_execute($apptStmt)) {
        throw new Exception("booking_failed");
    }

    $appointment_id = mysqli_insert_id($conn);
    mysqli_stmt_close($apptStmt);

    mysqli_commit($conn);

    echo json_encode([
        "result" => "success",
        "appointment_id" => $appointment_id,
        "mrn" => $mrn
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);
    // Only ever return our own known error codes — never the raw
    // exception/DB message — to avoid leaking internal details.
    $knownErrors = [
        "email_already_registered", "database_error", "slot_conflict", "booking_failed"
    ];
    $error = in_array($e->getMessage(), $knownErrors, true) ? $e->getMessage() : "booking_failed";
    echo json_encode(["result" => "error", "error" => $error]);

}

mysqli_close($conn);
?>
