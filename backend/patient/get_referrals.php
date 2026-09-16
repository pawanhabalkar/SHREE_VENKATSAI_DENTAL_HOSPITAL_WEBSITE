<?php

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];

$sql = "SELECT
            r.id,
            r.referral_date,
            r.reason,
            r.notes,
            r.status,
            refDoc.doctor_name AS referring_doctor_name,
            recDoc.doctor_name AS referred_doctor_name
        FROM referrals r
        LEFT JOIN doctors refDoc ON r.referring_doctor_id = refDoc.id
        LEFT JOIN doctors recDoc ON r.referred_doctor_id = recDoc.id
        WHERE r.patient_id = ?
        ORDER BY r.referral_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$referrals = [];

while ($row = mysqli_fetch_assoc($result)) {
    $referrals[] = $row;
}

echo json_encode($referrals);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>