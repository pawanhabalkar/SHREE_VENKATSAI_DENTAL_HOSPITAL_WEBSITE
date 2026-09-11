<?php

session_start();

require_once "../auth/patient_auth.php";
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid Request");
}

$patient_id = $_SESSION["patient_id"];

$full_name = trim($_POST["full_name"] ?? "");
$mobile    = trim($_POST["mobile"] ?? "");
$address   = trim($_POST["address"] ?? "");
$area      = trim($_POST["area"] ?? "");
$gender    = trim($_POST["gender"] ?? "");

if (empty($full_name) || empty($mobile)) {
    echo "missing_fields";
    exit;
}

if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    echo "invalid_mobile";
    exit;
}

$sql = "UPDATE patients
        SET full_name = ?,
            mobile = ?,
            address = ?,
            area = ?,
            gender = ?,
            updated_at = NOW()
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo "db_error";
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "sssssi",
    $full_name,
    $mobile,
    $address,
    $area,
    $gender,
    $patient_id
);

if (mysqli_stmt_execute($stmt)) {
    echo "success";
} else {
    echo "update_failed";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>