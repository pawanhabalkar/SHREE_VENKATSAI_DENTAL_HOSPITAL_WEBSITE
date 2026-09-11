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

// Confirm the patient exists first.
$checkStmt = mysqli_prepare($conn, "SELECT id FROM patients WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($checkStmt, "i", $patient_id);
mysqli_stmt_execute($checkStmt);
if (mysqli_num_rows(mysqli_stmt_get_result($checkStmt)) !== 1) {
    echo json_encode(["error" => "patient_not_found"]);
    mysqli_stmt_close($checkStmt);
    mysqli_close($conn);
    exit();
}
mysqli_stmt_close($checkStmt);

// Invoice number: e.g. INV-000001, following the same style as the MRN.
$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM invoices");
$total = mysqli_fetch_assoc($countResult)["total"];
$invoice_number = "INV-" . str_pad($total + 1, 6, "0", STR_PAD_LEFT);

$appointmentParam = $appointment_id > 0 ? $appointment_id : null;

$sql = "INSERT INTO invoices
            (patient_id, appointment_id, invoice_number, total_amount, discount, net_amount, paid_amount, due_amount, status, invoice_date)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "database_error"]);
    exit();
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

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "result" => "success",
        "invoice_id" => mysqli_insert_id($conn),
        "invoice_number" => $invoice_number,
        "net_amount" => $net_amount,
        "due_amount" => $due_amount
    ]);
} else {
    echo json_encode(["error" => "invoice_creation_failed"]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>