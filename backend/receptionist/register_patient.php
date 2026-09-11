<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "invalid_request"]);
    exit();
}

$full_name      = trim($_POST["full_name"] ?? "");
$mobile         = trim($_POST["mobile"] ?? "");
$date_of_birth  = trim($_POST["date_of_birth"] ?? "");
$gender         = trim($_POST["gender"] ?? "");
$email          = trim($_POST["email"] ?? "");
$password       = $_POST["password"] ?? "";
$area           = trim($_POST["area"] ?? "");
$address        = trim($_POST["address"] ?? "");

if (empty($full_name) || empty($mobile)) {
    echo json_encode(["error" => "missing_required_fields"]);
    exit();
}

// `users.email` is NOT NULL + should be unique. Walk-in patients often
// don't give an email at the desk, so fall back to a placeholder built
// from the mobile number. If a real email is given, use it as-is.
if ($email === "") {
    $email = "walkin_" . $mobile . "@no-email.local";
}

// Use the password the receptionist set on the desk form so the
// patient can actually log in later. Fall back to random only if
// somehow missing (defensive — form requires it).
$passwordToUse = $password !== "" ? $password : bin2hex(random_bytes(6));
$passwordHash = password_hash($passwordToUse, PASSWORD_DEFAULT);

mysqli_begin_transaction($conn);

try {

    // 1. Create the login (users) row.
    $userSql = "INSERT INTO users (email, password, role, status)
                VALUES (?, ?, 'patient', 'active')";
    $userStmt = mysqli_prepare($conn, $userSql);

    if (!$userStmt) {
        throw new Exception("database_error");
    }

    mysqli_stmt_bind_param($userStmt, "ss", $email, $passwordHash);

    if (!mysqli_stmt_execute($userStmt)) {
        // Most likely cause: duplicate email (e.g. same mobile walk-in twice).
        throw new Exception("email_already_registered");
    }

    $user_id = mysqli_insert_id($conn);
    mysqli_stmt_close($userStmt);

    // 2. Generate the next MRN, e.g. SVMSDH-000001, matching the format
    //    used previously by the front-end's localStorage-based counter.
    $maxResult = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(mrn, 8) AS UNSIGNED)) AS max_num FROM patients");
    $maxNum = mysqli_fetch_assoc($maxResult)["max_num"] ?? 0;
    $mrn = "SVMSDH-" . str_pad($maxNum + 1, 6, "0", STR_PAD_LEFT);

    // 3. Create the patient profile row.
    $patientSql = "INSERT INTO patients
                        (user_id, mrn, full_name, mobile, date_of_birth, gender, address, area, email)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $patientStmt = mysqli_prepare($conn, $patientSql);

    if (!$patientStmt) {
        throw new Exception("database_error");
    }

    // Nullable fields: pass null instead of an empty string where appropriate.
    $dobParam = $date_of_birth !== "" ? $date_of_birth : null;
    $genderParam = $gender !== "" ? $gender : null;

    mysqli_stmt_bind_param(
        $patientStmt,
        "issssssss",
        $user_id,
        $mrn,
        $full_name,
        $mobile,
        $dobParam,
        $genderParam,
        $address,
        $area,
        $email
    );

    if (!mysqli_stmt_execute($patientStmt)) {
        throw new Exception("database_error");
    }

    $patient_id = mysqli_insert_id($conn);
    mysqli_stmt_close($patientStmt);

    mysqli_commit($conn);

    echo json_encode([
        "result" => "success",
        "patient_id" => $patient_id,
        "mrn" => $mrn,
        "full_name" => $full_name
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);
    echo json_encode(["error" => $e->getMessage()]);

}

mysqli_close($conn);
?>