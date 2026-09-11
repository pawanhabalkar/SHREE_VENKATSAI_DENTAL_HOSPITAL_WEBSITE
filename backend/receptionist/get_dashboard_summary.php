<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$today = date("Y-m-d");

$summary = [
    "todays_appointments" => 0,
    "waiting_patients" => 0,
    "checked_in_patients" => 0,
    "available_doctors" => 0
];

// Today's total appointments
$sql = "SELECT COUNT(*) AS count FROM appointments WHERE appointment_date = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$summary["todays_appointments"] = intval($row["count"]);
mysqli_stmt_close($stmt);

// Waiting patients today
$sql = "SELECT COUNT(*) AS count FROM appointments
        WHERE appointment_date = ? AND status = 'Waiting'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$summary["waiting_patients"] = intval($row["count"]);
mysqli_stmt_close($stmt);

// Checked-in patients today
$sql = "SELECT COUNT(*) AS count FROM appointments
        WHERE appointment_date = ? AND status = 'Checked-in'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$summary["checked_in_patients"] = intval($row["count"]);
mysqli_stmt_close($stmt);

// Available (active) doctors
$sql = "SELECT COUNT(*) AS count FROM doctors WHERE status = 'active'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$summary["available_doctors"] = intval($row["count"]);

echo json_encode($summary);

mysqli_close($conn);
?>