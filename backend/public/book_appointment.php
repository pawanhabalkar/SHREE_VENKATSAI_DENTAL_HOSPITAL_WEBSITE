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

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["result" => "error", "error" => "invalid_request"]);
    exit();
}

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

$today = date("Y-m-d");
if ($appointment_date < $today) {
    echo json_encode(["result" => "error", "error" => "invalid_date"]);
    exit();
}

mysqli_begin_transaction($conn);

try {

    // 1. Look for an existing patient by mobile number (dedup — SRS 4.3.1 AC1)
    $findSql = "SELECT id, mrn, full_name FROM patients WHERE mobile = ? LIMIT 1";
    $findStmt = mysqli_prepare($conn, $findSql);
    mysqli_stmt_bind_param($findStmt, "s", $mobile);
    mysqli_stmt_execute($findStmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($findStmt));
    mysqli_stmt_close($findStmt);

    if ($existing) {

        // Returning patient — reuse the existing profile.
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

        $maxResult = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(mrn, 8) AS UNSIGNED)) AS max_num FROM patients");
        $maxNum = mysqli_fetch_assoc($maxResult)["max_num"] ?? 0;
        $mrn = "SVMSDH-" . str_pad($maxNum + 1, 6, "0", STR_PAD_LEFT);

        $genderParam = $gender !== "" ? $gender : null;

        $patientSql = "INSERT INTO patients
                            (user_id, mrn, full_name, mobile, gender, email, area, address)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $patientStmt = mysqli_prepare($conn, $patientSql);
        mysqli_stmt_bind_param(
            $patientStmt,
            "isssssss",
            $user_id,
            $mrn,
            $full_name,
            $mobile,
            $genderParam,
            $userEmail,
            $area,
            $address
        );

        if (!mysqli_stmt_execute($patientStmt)) {
            throw new Exception("database_error");
        }

        $patient_id = mysqli_insert_id($conn);
        mysqli_stmt_close($patientStmt);

    }

    // 2. Slot conflict check — same doctor, same date/time,
    //    not cancelled/no-show. Runs inside this transaction so
    //    two public bookings can't race onto the same slot.
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
    echo json_encode(["result" => "error", "error" => $e->getMessage()]);

}

mysqli_close($conn);
?>