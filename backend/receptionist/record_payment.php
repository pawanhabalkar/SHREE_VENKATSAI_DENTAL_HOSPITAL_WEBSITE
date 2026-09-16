<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";
require_once "../includes/csrf.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "invalid_request"]);
    exit();
}

require_csrf();

$invoice_id     = intval($_POST["invoice_id"] ?? 0);
$amount         = floatval($_POST["amount"] ?? 0);
$payment_method = trim($_POST["payment_method"] ?? "");
$transaction_reference = trim($_POST["transaction_reference"] ?? "");
$notes          = trim($_POST["notes"] ?? "");

$allowedMethods = ["Cash", "UPI", "Card", "Bank Transfer", "Other"];

if (empty($invoice_id) || $amount <= 0 || $payment_method === "") {
    echo json_encode(["error" => "missing_fields"]);
    exit();
}

if (!in_array($payment_method, $allowedMethods, true)) {
    echo json_encode(["error" => "invalid_payment_method"]);
    exit();
}

$payment_date = date("Y-m-d H:i:s");

mysqli_begin_transaction($conn);

try {

    // Lock the invoice row for the duration of this transaction.
    // Without FOR UPDATE, two payments recorded at (almost) the same
    // moment could both read the same stale due_amount, both pass
    // the "amount <= due_amount" check, and together overpay the
    // invoice — this row lock makes the second request wait for the
    // first to commit before it reads the (now updated) due amount.
    $invStmt = mysqli_prepare(
        $conn,
        "SELECT patient_id, net_amount, paid_amount, due_amount, status
         FROM invoices WHERE id = ? LIMIT 1 FOR UPDATE"
    );
    mysqli_stmt_bind_param($invStmt, "i", $invoice_id);
    mysqli_stmt_execute($invStmt);
    $invResult = mysqli_stmt_get_result($invStmt);

    if (mysqli_num_rows($invResult) !== 1) {
        mysqli_stmt_close($invStmt);
        throw new Exception("invoice_not_found");
    }

    $invoice = mysqli_fetch_assoc($invResult);
    mysqli_stmt_close($invStmt);

    if ($invoice["status"] === "Cancelled") {
        throw new Exception("invoice_cancelled");
    }

    if ($amount > $invoice["due_amount"]) {
        throw new Exception("amount_exceeds_due");
    }

    $patient_id = $invoice["patient_id"];

    // 1. Record the payment.
    $paySql = "INSERT INTO payments
                    (invoice_id, patient_id, amount, payment_method, transaction_reference, payment_date, status, notes)
               VALUES (?, ?, ?, ?, ?, ?, 'Success', ?)";
    $payStmt = mysqli_prepare($conn, $paySql);

    if (!$payStmt) {
        throw new Exception("database_error");
    }

    mysqli_stmt_bind_param(
        $payStmt,
        "iidssss",
        $invoice_id,
        $patient_id,
        $amount,
        $payment_method,
        $transaction_reference,
        $payment_date,
        $notes
    );

    if (!mysqli_stmt_execute($payStmt)) {
        throw new Exception("payment_failed");
    }
    $payment_id = mysqli_insert_id($conn);
    mysqli_stmt_close($payStmt);

    // 2. Update the invoice's paid/due amounts and status.
    $newPaid = $invoice["paid_amount"] + $amount;
    $newDue = $invoice["net_amount"] - $newPaid;
    $newStatus = $newDue <= 0 ? "Paid" : "Partially Paid";

    $updateSql = "UPDATE invoices SET paid_amount = ?, due_amount = ?, status = ? WHERE id = ?";
    $updateStmt = mysqli_prepare($conn, $updateSql);

    if (!$updateStmt) {
        throw new Exception("database_error");
    }

    mysqli_stmt_bind_param($updateStmt, "ddsi", $newPaid, $newDue, $newStatus, $invoice_id);

    if (!mysqli_stmt_execute($updateStmt)) {
        throw new Exception("invoice_update_failed");
    }
    mysqli_stmt_close($updateStmt);

    mysqli_commit($conn);

    echo json_encode([
        "result" => "success",
        "payment_id" => $payment_id,
        "invoice_status" => $newStatus,
        "due_amount" => $newDue
    ]);

} catch (Exception $e) {

    mysqli_rollback($conn);
    echo json_encode(["error" => $e->getMessage()]);

}

mysqli_close($conn);
?>
