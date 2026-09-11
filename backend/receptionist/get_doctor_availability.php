<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$doctor_id = intval($_GET["doctor_id"] ?? 0);
$date = trim($_GET["date"] ?? "");

if (empty($doctor_id) || $date === "") {
    echo json_encode(["error" => "missing_fields"]);
    exit();
}

// Doctor's active/inactive status
$sql = "SELECT status FROM doctors WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $doctor_id);
mysqli_stmt_execute($stmt);
$doctor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$doctor) {
    echo json_encode(["error" => "doctor_not_found"]);
    exit();
}

// Already-booked time slots for this doctor on this date
// (excludes cancelled/no-show so those slots free back up)
$sql = "SELECT appointment_time FROM appointments
        WHERE doctor_id = ?
          AND appointment_date = ?
          AND status NOT IN ('Cancelled', 'No-show')";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $doctor_id, $date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$bookedSlots = [];
while ($row = mysqli_fetch_assoc($result)) {
    $bookedSlots[] = substr($row["appointment_time"], 0, 5); // "HH:MM"
}

echo json_encode([
    "doctor_status" => $doctor["status"],
    "booked_slots" => $bookedSlots
]);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>