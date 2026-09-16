<?php

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];

$sql = "SELECT
            mr.id,
            mr.record_type,
            mr.title,
            mr.description,
            mr.file_path,
            mr.record_date,
            d.doctor_name
        FROM medical_records mr
        LEFT JOIN doctors d ON mr.doctor_id = d.id
        WHERE mr.patient_id = ?
        ORDER BY mr.record_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$records = [];

while ($row = mysqli_fetch_assoc($result)) {
    $records[] = $row;
}

echo json_encode($records);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>