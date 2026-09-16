<?php

/*
    SHARED APPOINTMENT BOOKING VALIDATION
    -----------------------------------------------------
    Used by every endpoint that creates an appointment
    (public, patient portal, receptionist desk) so the
    three booking flows enforce identical rules.

    The allowed time slots mirror the <select id="appointmentTime">
    options in book-appointment/book-appointment.html:
    09:00-12:00 and 14:00-19:00, in 30-minute steps.
*/

function allowed_appointment_slots() {
    $slots = [];

    foreach ([[9, 0, 12, 0], [14, 0, 19, 0]] as $range) {
        [$startH, $startM, $endH, $endM] = $range;
        $cursor = $startH * 60 + $startM;
        $end = $endH * 60 + $endM;

        while ($cursor <= $end) {
            $h = intdiv($cursor, 60);
            $m = $cursor % 60;
            $slots[] = sprintf("%02d:%02d:00", $h, $m);
            $cursor += 30;
        }
    }

    return $slots;
}

// Normalizes "HH:MM" or "HH:MM:SS" to "HH:MM:SS" and checks it's one
// of the clinic's bookable slots. Returns the normalized string, or
// null if invalid/not a bookable slot.
function normalize_and_check_slot($time) {
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)(:([0-5]\d))?$/', $time, $m)) {
        return null;
    }

    $normalized = $m[1] . ":" . $m[2] . ":00";

    if (!in_array($normalized, allowed_appointment_slots(), true)) {
        return null;
    }

    return $normalized;
}

// Basic "YYYY-MM-DD", not in the past, not absurdly far in the future.
function is_valid_appointment_date($date) {
    $d = DateTime::createFromFormat("Y-m-d", $date);

    if (!$d || $d->format("Y-m-d") !== $date) {
        return false;
    }

    $today = new DateTime("today");
    $maxDate = (clone $today)->modify("+180 days");

    return $d >= $today && $d <= $maxDate;
}

// Confirms the department exists/active, the doctor exists/active,
// and the doctor actually belongs to that department. Returns an
// error code string on failure, or null on success.
function validate_department_and_doctor($conn, $department_id, $doctor_id) {

    $deptStmt = mysqli_prepare($conn, "SELECT id FROM departments WHERE id = ? AND status = 'active' LIMIT 1");
    mysqli_stmt_bind_param($deptStmt, "i", $department_id);
    mysqli_stmt_execute($deptStmt);
    $dept = mysqli_fetch_assoc(mysqli_stmt_get_result($deptStmt));
    mysqli_stmt_close($deptStmt);

    if (!$dept) {
        return "invalid_department";
    }

    $docStmt = mysqli_prepare($conn, "SELECT id, department_id, status FROM doctors WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($docStmt, "i", $doctor_id);
    mysqli_stmt_execute($docStmt);
    $doctor = mysqli_fetch_assoc(mysqli_stmt_get_result($docStmt));
    mysqli_stmt_close($docStmt);

    if (!$doctor) {
        return "invalid_doctor";
    }

    if ($doctor["status"] !== "active") {
        return "doctor_not_active";
    }

    if (intval($doctor["department_id"]) !== intval($department_id)) {
        return "doctor_department_mismatch";
    }

    return null;
}

?>
