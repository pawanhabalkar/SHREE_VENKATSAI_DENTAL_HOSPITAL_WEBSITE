<?php

session_start();

require_once "../auth/patient_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];

$sql = "SELECT
            p.id,
            p.amount,
            p.payment_method,
            p.transaction_reference,
            p.payment_date,
            p.status,
            i.invoice_number
        FROM payments p
        LEFT JOIN invoices i ON p.invoice_id = i.id
        WHERE p.patient_id = ?
        ORDER BY p.payment_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$payments = [];

while ($row = mysqli_fetch_assoc($result)) {
    $payments[] = $row;
}

echo json_encode($payments);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>