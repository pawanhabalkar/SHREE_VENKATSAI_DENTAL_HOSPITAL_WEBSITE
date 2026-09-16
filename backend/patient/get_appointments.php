<?php

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];

$sql = "SELECT
            a.id,
            a.appointment_date,
            a.appointment_time,
            a.reason,
            a.status,
            d.doctor_name,
            dept.department_name
        FROM appointments a
        LEFT JOIN doctors d ON a.doctor_id = d.id
        LEFT JOIN departments dept ON a.department_id = dept.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$appointments = [];

while ($row = mysqli_fetch_assoc($result)) {
    $appointments[] = $row;
}

echo json_encode($appointments);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>