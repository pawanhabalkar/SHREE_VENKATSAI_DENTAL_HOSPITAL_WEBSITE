<?php

session_start();

require_once "../auth/patient_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];

$sql = "SELECT
            p.id,
            p.prescription_date,
            p.notes,
            d.doctor_name
        FROM prescriptions p
        LEFT JOIN doctors d ON p.doctor_id = d.id
        WHERE p.patient_id = ?
        ORDER BY p.prescription_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$prescriptions = [];

while ($row = mysqli_fetch_assoc($result)) {
    $row["items"] = [];
    $prescriptions[$row["id"]] = $row;
}

mysqli_stmt_close($stmt);

if (!empty($prescriptions)) {

    $ids = array_keys($prescriptions);
    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $types = str_repeat("i", count($ids));

    $itemSql = "SELECT
                    prescription_id,
                    medicine_name,
                    dosage,
                    frequency,
                    duration,
                    instructions
                FROM prescription_items
                WHERE prescription_id IN ($placeholders)";

    $itemStmt = mysqli_prepare($conn, $itemSql);
    mysqli_stmt_bind_param($itemStmt, $types, ...$ids);
    mysqli_stmt_execute($itemStmt);

    $itemResult = mysqli_stmt_get_result($itemStmt);

    while ($item = mysqli_fetch_assoc($itemResult)) {
        $prescriptions[$item["prescription_id"]]["items"][] = $item;
    }

    mysqli_stmt_close($itemStmt);
}

echo json_encode(array_values($prescriptions));

mysqli_close($conn);
?>