<?php
session_start();

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid Request");
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if (empty($email) || empty($password)) {
    exit("Please enter email and password.");
}

$sql = "SELECT * FROM users
        WHERE email = ?
        AND status = 'active'
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    exit("Database Error");
}

mysqli_stmt_bind_param($stmt, "s", $email);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 1) {

    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user["password"])) {

        $_SESSION["logged_in"] = true;
        $_SESSION["user_id"]   = $user["id"];
        $_SESSION["email"]     = $user["email"];
        $_SESSION["role"]      = $user["role"];

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