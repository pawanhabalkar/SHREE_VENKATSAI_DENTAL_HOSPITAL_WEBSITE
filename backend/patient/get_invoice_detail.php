<?php

session_start();

require_once "../auth/patient_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];
$invoice_id = intval($_GET["invoice_id"] ?? 0);

if (empty($invoice_id)) {
    echo json_encode(["error" => "missing_id"]);
    exit;
}

// Ownership check: only return this invoice if it belongs to the logged-in patient
$sql = "SELECT * FROM invoices WHERE id = ? AND patient_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $invoice_id, $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    echo json_encode(["error" => "not_found"]);
    exit;
}

$invoice = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$paySql = "SELECT amount, payment_method, transaction_reference, payment_date, status
           FROM payments
           WHERE invoice_id = ?
           ORDER BY payment_date DESC";

$payStmt = mysqli_prepare($conn, $paySql);
mysqli_stmt_bind_param($payStmt, "i", $invoice_id);
mysqli_stmt_execute($payStmt);
$payResult = mysqli_stmt_get_result($payStmt);

$payments = [];
while ($row = mysqli_fetch_assoc($payResult)) {
    $payments[] = $row;
}

$invoice["payments"] = $payments;

echo json_encode($invoice);

mysqli_stmt_close($payStmt);
mysqli_close($conn);
?>