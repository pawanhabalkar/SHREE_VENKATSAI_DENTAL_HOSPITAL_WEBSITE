<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "invalid_request"]);
    exit();
}

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

// Fetch the invoice to validate the payment and get patient_id + current due.
$invStmt = mysqli_prepare($conn, "SELECT patient_id, net_amount, paid_amount, due_amount, status FROM invoices WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($invStmt, "i", $invoice_id);
mysqli_stmt_execute($invStmt);
$invResult = mysqli_stmt_get_result($invStmt);

if (mysqli_num_rows($invResult) !== 1) {
    echo json_encode(["error" => "invoice_not_found"]);
    mysqli_stmt_close($invStmt);
    mysqli_close($conn);
    exit();
}

$invoice = mysqli_fetch_assoc($invResult);
mysqli_stmt_close($invStmt);

if ($invoice["status"] === "Cancelled") {
    echo json_encode(["error" => "invoice_cancelled"]);
    mysqli_close($conn);
    exit();
}

if ($amount > $invoice["due_amount"]) {
    echo json_encode(["error" => "amount_exceeds_due"]);
    mysqli_close($conn);
    exit();
}

$patient_id = $invoice["patient_id"];
$payment_date = date("Y-m-d H:i:s");

mysqli_begin_transaction($conn);

try {

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