<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$departments = [];
$deptResult = mysqli_query($conn, "SELECT id, department_name FROM departments WHERE status = 'active'");
while ($row = mysqli_fetch_assoc($deptResult)) {
    $departments[] = $row;
}

$doctors = [];
$docResult = mysqli_query($conn, "SELECT id, doctor_name, department_id, specialization FROM doctors WHERE status = 'active'");
while ($row = mysqli_fetch_assoc($docResult)) {
    $doctors[] = $row;
}

echo json_encode([
    "departments" => $departments,
    "doctors" => $doctors
]);

mysqli_close($conn);
?>