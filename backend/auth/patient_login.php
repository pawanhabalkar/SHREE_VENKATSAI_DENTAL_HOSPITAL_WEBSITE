<?php

session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid Request");
}

$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";

if (empty($mobile) || empty($password)) {
    echo "missing_fields";
    exit;
}

if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    echo "invalid_mobile";
    exit;
}

$sql = "SELECT p.id AS patient_id, p.mrn, p.full_name, p.mobile,
               u.id AS user_id, u.password, u.status
        FROM patients p
        INNER JOIN users u ON u.id = p.user_id
        WHERE p.mobile = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    exit("Database Error");
}

mysqli_stmt_bind_param($stmt, "s", $mobile);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 1) {

    $patient = mysqli_fetch_assoc($result);

    if ($patient["status"] !== "active") {
        echo "account_inactive";
        exit;
    }

    if (password_verify($password, $patient["password"])) {

        $_SESSION["user_id"]    = $patient["user_id"];
        $_SESSION["patient_id"] = $patient["patient_id"];
        $_SESSION["mrn"]        = $patient["mrn"];
        $_SESSION["name"]       = $patient["full_name"];
        $_SESSION["role"]       = "patient";

        echo "success";

    } else {
        echo "wrong_password";
    }

} else {
    echo "user_not_found";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
exit;
?>