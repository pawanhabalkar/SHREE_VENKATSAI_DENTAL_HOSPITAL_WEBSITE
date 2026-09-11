<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

// Reception looks up invoices for whichever patient is at the desk,
// so patient_id comes from the query string, not the session.
$patient_id = intval($_GET["patient_id"] ?? 0);

if (empty($patient_id)) {
    echo json_encode(["error" => "missing_patient_id"]);
    exit();
}

$sql = "SELECT
            i.id,
            i.invoice_number,
            i.appointment_id,
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

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "database_error"]);
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$invoices = [];
while ($row = mysqli_fetch_assoc($result)) {
    $invoices[] = $row;
}

echo json_encode(["invoices" => $invoices]);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>