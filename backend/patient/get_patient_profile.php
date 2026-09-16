<?php

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];

$sql = "SELECT mrn, full_name, mobile, date_of_birth, gender, address
        FROM patients
        WHERE id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$patient = mysqli_fetch_assoc($result);

if ($patient) {

    // Calculate age from date_of_birth (if set)
    $age = "";
    if (!empty($patient["date_of_birth"])) {
        $dob = new DateTime($patient["date_of_birth"]);
        $today = new DateTime();
        $age = $today->diff($dob)->y;
    }

    echo json_encode([
        "mrn" => $patient["mrn"],
        "full_name" => $patient["full_name"],
        "mobile" => $patient["mobile"],
        "age" => $age,
        "gender" => $patient["gender"],
        "address" => $patient["address"]
    ]);

} else {
    echo json_encode([]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>