<?php

// Automated appointment reminder dispatch.
//
// Intended to run once per day via a scheduled task (Windows Task
// Scheduler or Linux cron calling php.exe on this file). It is NOT
// triggered by a page visit — there is no email/SMS gateway configured
// in this project, so this creates an in-app notification (read by the
// existing patient portal notifications screen) for every appointment
// happening in the next ~24 hours.
//
// Safe to run more than once a day: it only touches appointments
// where reminder_sent = 0, and immediately flips that flag after
// creating the notification, so nobody gets reminded twice.
//
// Deliberately has NO reception_auth.php / session check — a
// scheduled task has no browser session to authenticate.

require_once "../config/database.php";

header("Content-Type: application/json");

// Window: appointments happening between 23 and 25 hours from now.
// A 2-hour window (rather than an exact "tomorrow's date") means this
// still works correctly no matter what time of day the script runs.
$windowStart = date("Y-m-d H:i:s", strtotime("+23 hours"));
$windowEnd   = date("Y-m-d H:i:s", strtotime("+25 hours"));

$sql = "SELECT
            a.id AS appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.patient_id,
            d.doctor_name,
            dept.department_name
        FROM appointments a
        LEFT JOIN doctors d ON a.doctor_id = d.id
        LEFT JOIN departments dept ON a.department_id = dept.id
        WHERE a.reminder_sent = 0
          AND a.status NOT IN ('Cancelled', 'No-show', 'Completed')
          AND TIMESTAMP(a.appointment_date, a.appointment_time) BETWEEN ? AND ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "database_error"]);
    exit();
}

mysqli_stmt_bind_param($stmt, "ss", $windowStart, $windowEnd);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$remindersSent = 0;

while ($appt = mysqli_fetch_assoc($result)) {

    $title = "Appointment Reminder";
    $message = "You have an appointment with " .
        ($appt["doctor_name"] ?? "your doctor") .
        ($appt["department_name"] ? " (" . $appt["department_name"] . ")" : "") .
        " on " . $appt["appointment_date"] .
        " at " . substr($appt["appointment_time"], 0, 5) . ".";

    $insertSql = "INSERT INTO notifications (patient_id, title, message, type, is_read)
                  VALUES (?, ?, ?, 'appointment_reminder', 0)";

    $insertStmt = mysqli_prepare($conn, $insertSql);
    mysqli_stmt_bind_param($insertStmt, "iss", $appt["patient_id"], $title, $message);

    if (mysqli_stmt_execute($insertStmt)) {

        // Mark this appointment as reminded so it's never picked up again.
        $updateStmt = mysqli_prepare($conn, "UPDATE appointments SET reminder_sent = 1 WHERE id = ?");
        mysqli_stmt_bind_param($updateStmt, "i", $appt["appointment_id"]);
        mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);

        $remindersSent++;

    }

    mysqli_stmt_close($insertStmt);

}

echo json_encode([
    "result" => "success",
    "reminders_sent" => $remindersSent,
    "checked_window" => [$windowStart, $windowEnd]
]);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>