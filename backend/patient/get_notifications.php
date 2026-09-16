<?php

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

$patient_id = $_SESSION["patient_id"];

$sql = "SELECT
            id,
            title,
            message,
            type,
            is_read,
            created_at
        FROM notifications
        WHERE patient_id = ?
        ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $patient_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$notifications = [];

while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
}

echo json_encode($notifications);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>