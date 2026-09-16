<?php

/*
    APPOINTMENT CANCELLATION + REFUND (SRS 4.3.9)
    -------------------------------------------------
    This is the ONE cancellation endpoint used by the patient
    portal (patient.js). An earlier version of this project had a
    second, near-duplicate implementation under backend/public/ with
    its own refund logic that had drifted out of sync and wasn't
    even wired up to any page — that duplicate has been removed so
    there's a single source of truth for the cancellation/refund
    policy.

    Default refund window: 30 minutes AFTER the scheduled
    appointment time (per SRS — configurable below).

    - Cancelling at or before (appointment time + window) => eligible
      for refund. Any amount already paid on an invoice linked to
      this appointment is refunded: a 'Refunded' payment row is
      recorded and the invoice balances are adjusted.
    - Cancelling after the window => marked non-refundable, no
      refund transaction is created.
*/

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";
require_once "../includes/csrf.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["result" => "error", "error" => "invalid_request"]);
    exit;
}

require_csrf();

$patient_id = $_SESSION["patient_id"];
$appointment_id = intval($_POST["appointment_id"] ?? 0);

// Configurable refund window, in minutes, per SRS 4.3.9.
$REFUND_WINDOW_MINUTES = 30;

if (empty($appointment_id)) {
    echo json_encode(["result" => "error", "error" => "missing_id"]);
    exit;
}

mysqli_begin_transaction($conn);

try {

    // 1. Fetch the appointment — ownership check baked into the
    //    WHERE clause, so a patient can only ever cancel their own
    //    appointment even if they tamper with the appointment_id.
    $apptSql = "SELECT id, appointment_date, appointment_time, status
                FROM appointments
                WHERE id = ? AND patient_id = ?
                LIMIT 1
                FOR UPDATE";
    $apptStmt = mysqli_prepare($conn, $apptSql);
    mysqli_stmt_bind_param($apptStmt, "ii", $appointment_id, $patient_id);
    mysqli_stmt_execute($apptStmt);
    $appointment = mysqli_fetch_assoc(mysqli_stmt_get_result($apptStmt));
    mysqli_stmt_close($apptStmt);

    if (!$appointment) {
        throw new Exception("not_found_or_not_yours");
    }

    if ($appointment["status"] !== "Booked") {
        throw new Exception("cannot_cancel_in_current_status");
    }

    // 2. Determine refund eligibility based on the configured window.
    $appointmentDateTime = new DateTime(
        $appointment["appointment_date"] . " " . $appointment["appointment_time"]
    );
    $refundDeadline = clone $appointmentDateTime;
    $refundDeadline->modify("+{$REFUND_WINDOW_MINUTES} minutes");

    $now = new DateTime();
    $refund_eligible = $now <= $refundDeadline;

    // 3. Cancel the appointment.
    $cancelSql = "UPDATE appointments
                  SET status = 'Cancelled', updated_at = NOW()
                  WHERE id = ? AND patient_id = ?";
    $cancelStmt = mysqli_prepare($conn, $cancelSql);
    mysqli_stmt_bind_param($cancelStmt, "ii", $appointment_id, $patient_id);
    mysqli_stmt_execute($cancelStmt);

    if (mysqli_stmt_affected_rows($cancelStmt) !== 1) {
        throw new Exception("cancel_failed");
    }
    mysqli_stmt_close($cancelStmt);

    $refunded_amount = 0;

    // 4. If eligible, refund any amount already paid on an invoice
    //    linked to this appointment.
    if ($refund_eligible) {

        $invSql = "SELECT id, paid_amount, net_amount
                   FROM invoices
                   WHERE appointment_id = ? AND paid_amount > 0
                   LIMIT 1
                   FOR UPDATE";
        $invStmt = mysqli_prepare($conn, $invSql);
        mysqli_stmt_bind_param($invStmt, "i", $appointment_id);
        mysqli_stmt_execute($invStmt);
        $invoice = mysqli_fetch_assoc(mysqli_stmt_get_result($invStmt));
        mysqli_stmt_close($invStmt);

        if ($invoice) {

            $refunded_amount = $invoice["paid_amount"];

            // Record the refund as a payment transaction so it shows
            // up in the patient's payment history (SRS requirement).
            $payoutSql = "INSERT INTO payments
                              (invoice_id, patient_id, amount, payment_method, payment_date, status, notes)
                          VALUES (?, ?, ?, 'Other', NOW(), 'Refunded', 'Refund for cancelled appointment')";
            $payoutStmt = mysqli_prepare($conn, $payoutSql);
            mysqli_stmt_bind_param(
                $payoutStmt,
                "iid",
                $invoice["id"],
                $patient_id,
                $refunded_amount
            );
            mysqli_stmt_execute($payoutStmt);
            mysqli_stmt_close($payoutStmt);

            // Zero out the invoice's paid amount and mark it cancelled.
            $updateInvSql = "UPDATE invoices
                             SET paid_amount = 0,
                                 due_amount = net_amount,
                                 status = 'Cancelled'
                             WHERE id = ?";
            $updateInvStmt = mysqli_prepare($conn, $updateInvSql);
            mysqli_stmt_bind_param($updateInvStmt, "i", $invoice["id"]);
            mysqli_stmt_execute($updateInvStmt);
            mysqli_stmt_close($updateInvStmt);

        }

    }

    mysqli_commit($conn);

    echo json_encode([
        "result" => "success",
        "refund_eligible" => $refund_eligible,
        "refunded_amount" => $refunded_amount
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);
    $knownErrors = ["not_found_or_not_yours", "cannot_cancel_in_current_status", "cancel_failed"];
    $error = in_array($e->getMessage(), $knownErrors, true) ? $e->getMessage() : "cancel_failed";
    echo json_encode(["result" => "error", "error" => $error]);

}

mysqli_close($conn);
?>
