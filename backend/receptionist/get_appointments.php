<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

// Defaults to today's queue, but the front-desk can pass a specific
// date, a status filter, and/or a doctor filter to narrow the list.
$date       = trim($_GET["date"] ?? date("Y-m-d"));
$status     = trim($_GET["status"] ?? "");
$doctor_id  = intval($_GET["doctor_id"] ?? 0);

$conditions = ["a.appointment_date = ?"];
$types = "s";
$params = [$date];

if ($status !== "") {
    $conditions[] = "a.status = ?";
    $types .= "s";
    $params[] = $status;
}

if ($doctor_id > 0) {
    $conditions[] = "a.doctor_id = ?";
    $types .= "i";
    $params[] = $doctor_id;
}

$whereClause = implode(" AND ", $conditions);

$sql = "SELECT
            a.id,
            a.patient_id,
            a.doctor_id,
            a.department_id,
            a.appointment_date,
            a.appointment_time,
            a.reason,
            a.notes,
            a.status,
            p.mrn,
            p.full_name AS patient_name,
            p.mobile AS patient_mobile,
            d.doctor_name,
            dept.department_name
        FROM appointments a
        LEFT JOIN patients p ON a.patient_id = p.id
        LEFT JOIN doctors d ON a.doctor_id = d.id
        LEFT JOIN departments dept ON a.department_id = dept.id
        WHERE $whereClause
        ORDER BY a.appointment_time ASC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "database_error"]);
    exit();
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$appointments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $appointments[] = $row;
}

echo json_encode(["appointments" => $appointments]);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>