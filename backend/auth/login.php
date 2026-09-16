<?php

session_start();

require_once "../config/database.php";
require_once "../includes/csrf.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid Request");
}

require_csrf();

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

        // This shared login form accepts both patients and receptionists.
        // Any other role (super_admin, admin, doctor) is not permitted here.
        if ($user["role"] === "patient") {

            $pSql = "SELECT id, mrn, full_name
                     FROM patients
                     WHERE user_id = ?
                     LIMIT 1";

            $pStmt = mysqli_prepare($conn, $pSql);
            mysqli_stmt_bind_param($pStmt, "i", $user["id"]);
            mysqli_stmt_execute($pStmt);

            $pResult = mysqli_stmt_get_result($pStmt);

            if (mysqli_num_rows($pResult) !== 1) {
                echo "patient_profile_missing";
                mysqli_stmt_close($pStmt);
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                exit;
            }

            $patient = mysqli_fetch_assoc($pResult);

            // Prevent session fixation: issue a fresh session id on login
            session_regenerate_id(true);

            $_SESSION["logged_in"]  = true;
            $_SESSION["user_id"]    = $user["id"];
            $_SESSION["email"]      = $user["email"];
            $_SESSION["role"]       = $user["role"];
            $_SESSION["patient_id"] = $patient["id"];
            $_SESSION["mrn"]        = $patient["mrn"];
            $_SESSION["name"]       = $patient["full_name"];

            mysqli_stmt_close($pStmt);

            echo "success:patient";

        } elseif ($user["role"] === "receptionist") {

            // Prevent session fixation: issue a fresh session id on login
            session_regenerate_id(true);

            $_SESSION["logged_in"] = true;
            $_SESSION["user_id"]   = $user["id"];
            $_SESSION["email"]     = $user["email"];
            $_SESSION["role"]      = $user["role"];

            echo "success:receptionist";

        } else {

            // super_admin, admin, doctor, etc. are not handled by this
            // shared login form.
            echo "not_permitted_here";

        }

    } else {

        echo "wrong_password";

    }

} else {

    echo "user_not_found";

}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>