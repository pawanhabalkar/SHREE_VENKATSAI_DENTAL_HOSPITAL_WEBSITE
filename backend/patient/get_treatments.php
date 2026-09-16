<?php

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];

$sql = "SELECT
            t.id,
            t.treatment_name,
            t.tooth_number,
            t.description,
            t.status,
            t.start_date,
            t.end_date,
            d.doctor_name
        FROM treatments t
        LEFT JOIN doctors d ON t.doctor_id = d.id
        WHERE t.patient_id = ?
        ORDER BY t.start_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$treatments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $treatments[] = $row;
}

echo json_encode($treatments);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>