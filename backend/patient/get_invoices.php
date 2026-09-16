<?php

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];

$sql = "SELECT
            i.id,
            i.invoice_number,
            i.total_amount,
            i.discount,
            i.net_amount,
            i.paid_amount,
            i.due_amount,
            i.status,
            i.invoice_date
        FROM invoices i
        WHERE i.patient_id = ?
        ORDER BY i.invoice_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$invoices = [];

while ($row = mysqli_fetch_assoc($result)) {
    $invoices[] = $row;
}

echo json_encode($invoices);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>