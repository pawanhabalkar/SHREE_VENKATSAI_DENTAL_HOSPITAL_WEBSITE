<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$query = trim($_GET["query"] ?? "");
$date  = trim($_GET["date"] ?? "");

if ($query === "" && $date === "") {
    echo json_encode(["appointments" => []]);
    exit();
}

$sql = "SELECT
            a.id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            a.reason,
            p.mrn,
            p.full_name AS patient_name,
            p.mobile,
            d.doctor_name,
            dept.department_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        LEFT JOIN doctors d ON a.doctor_id = d.id
        LEFT JOIN departments dept ON a.department_id = dept.id
        WHERE 1 = 1";

$types = "";
$params = [];

if ($query !== "") {
    $sql .= " AND (p.mrn LIKE ? OR p.full_name LIKE ? OR p.mobile LIKE ? OR a.id = ?)";
    $like = "%" . $query . "%";
    $types .= "sssi";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = is_numeric($query) ? intval($query) : 0;
}

if ($date !== "") {
    $sql .= " AND a.appointment_date = ?";
    $types .= "s";
    $params[] = $date;
}

$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC LIMIT 50";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "database_error"]);
    exit();
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

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