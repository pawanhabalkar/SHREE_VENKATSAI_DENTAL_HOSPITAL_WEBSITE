<?php

session_start();

require_once "../auth/patient_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];

$summary = [];

// Next upcoming appointment
$apptSql = "SELECT
                a.appointment_date,
                a.appointment_time,
                a.status,
                d.doctor_name,
                dept.department_name
            FROM appointments a
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN departments dept ON a.department_id = dept.id
            WHERE a.patient_id = ?
              AND a.appointment_date >= CURDATE()
              AND a.status IN ('Booked', 'Checked-in', 'Waiting', 'With Doctor')
            ORDER BY a.appointment_date ASC, a.appointment_time ASC
            LIMIT 1";

$stmt = mysqli_prepare($conn, $apptSql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$summary["next_appointment"] = mysqli_fetch_assoc($result) ?: null;
mysqli_stmt_close($stmt);

// Unread notifications count
$notifSql = "SELECT COUNT(*) AS unread_count
             FROM notifications
             WHERE patient_id = ? AND is_read = 0";

$stmt = mysqli_prepare($conn, $notifSql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$summary["unread_notifications"] = mysqli_fetch_assoc($result)["unread_count"];
mysqli_stmt_close($stmt);

// Total outstanding balance across all invoices
$dueSql = "SELECT COALESCE(SUM(due_amount), 0) AS total_due
           FROM invoices
           WHERE patient_id = ? AND status != 'Cancelled'";

$stmt = mysqli_prepare($conn, $dueSql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$summary["total_due"] = mysqli_fetch_assoc($result)["total_due"];
mysqli_stmt_close($stmt);

// Total appointment count (all-time)
$countSql = "SELECT COUNT(*) AS total_appointments
             FROM appointments
             WHERE patient_id = ?";

$stmt = mysqli_prepare($conn, $countSql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$summary["total_appointments"] = mysqli_fetch_assoc($result)["total_appointments"];
mysqli_stmt_close($stmt);

// Total medical records count
$recordsSql = "SELECT COUNT(*) AS total_records
               FROM medical_records
               WHERE patient_id = ?";

$stmt = mysqli_prepare($conn, $recordsSql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$summary["total_medical_records"] = mysqli_fetch_assoc($result)["total_records"];
mysqli_stmt_close($stmt);

echo json_encode($summary);

mysqli_close($conn);
?>