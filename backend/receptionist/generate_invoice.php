<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";
require_once "../includes/csrf.php";
require_once "../includes/sequence.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "invalid_request"]);
    exit();
}

require_csrf();

$patient_id     = intval($_POST["patient_id"] ?? 0);
$appointment_id = intval($_POST["appointment_id"] ?? 0);
$total_amount   = floatval($_POST["total_amount"] ?? 0);
$discount       = floatval($_POST["discount"] ?? 0);

if (empty($patient_id) || $total_amount <= 0) {
    echo json_encode(["error" => "missing_fields"]);
    exit();
}

if ($discount < 0 || $discount > $total_amount) {
    echo json_encode(["error" => "invalid_discount"]);
    exit();
}

$net_amount = $total_amount - $discount;
$paid_amount = 0.00;
$due_amount = $net_amount;
$status = "Unpaid";
$invoice_date = date("Y-m-d");

$appointmentParam = $appointment_id > 0 ? $appointment_id : null;

mysqli_begin_transaction($conn);

try {

    // Confirm the patient exists first.
    $checkStmt = mysqli_prepare($conn, "SELECT id FROM patients WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($checkStmt, "i", $patient_id);
    mysqli_stmt_execute($checkStmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($checkStmt)) !== 1) {
        mysqli_stmt_close($checkStmt);
        throw new Exception("patient_not_found");
    }
    mysqli_stmt_close($checkStmt);

    // If an appointment_id was given, confirm it exists and actually
    // belongs to this patient (prevents an invoice being attached to
    // someone else's appointment).
    if ($appointmentParam !== null) {
        $apptCheckStmt = mysqli_prepare($conn, "SELECT id FROM appointments WHERE id = ? AND patient_id = ? LIMIT 1");
        mysqli_stmt_bind_param($apptCheckStmt, "ii", $appointmentParam, $patient_id);
        mysqli_stmt_execute($apptCheckStmt);
        $apptOk = mysqli_fetch_assoc(mysqli_stmt_get_result($apptCheckStmt));
        mysqli_stmt_close($apptCheckStmt);

        if (!$apptOk) {
            throw new Exception("appointment_mismatch");
        }
    }

    // Invoice number: e.g. INV-000001, following the same style as
    // the MRN. Row-locked (see sequence.php) so two invoices being
    // generated at the same time can't collide on the same number.
    $invoice_number = generate_sequential_code($conn, "invoices", "invoice_number", "INV-", 5, 6);

    $sql = "INSERT INTO invoices
                (patient_id, appointment_id, invoice_number, total_amount, discount, net_amount, paid_amount, due_amount, status, invoice_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception("database_error");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "iisdddddss",
        $patient_id,
        $appointmentParam,
        $invoice_number,
        $total_amount,
        $discount,
        $net_amount,
        $paid_amount,
        $due_amount,
        $status,
        $invoice_date
    );

    if (!mysqli_stmt_execute($stmt)) {
        // Extremely unlikely given the row lock above, but if the
        // invoice_number UNIQUE key ever did collide, fail cleanly
        // instead of leaking the raw DB error.
        throw new Exception("invoice_creation_failed");
    }

    $invoice_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    echo json_encode([
        "result" => "success",
        "invoice_id" => $invoice_id,
        "invoice_number" => $invoice_number,
        "net_amount" => $net_amount,
        "due_amount" => $due_amount
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);
    $knownErrors = ["patient_not_found", "appointment_mismatch", "database_error", "invoice_creation_failed"];
    $error = in_array($e->getMessage(), $knownErrors, true) ? $e->getMessage() : "invoice_creation_failed";
    echo json_encode(["error" => $error]);

}

mysqli_close($conn);
?>
