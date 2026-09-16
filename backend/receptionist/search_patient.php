<?php

session_start();

require_once "reception_auth.php";
require_once "../config/database.php";

header("Content-Type: application/json");

// Accept the search term from either GET or POST so this can be called
// from a simple ?query= link or from a JS fetch() POST.
$query = trim($_GET["query"] ?? $_POST["query"] ?? "");

if ($query === "") {
    echo json_encode(["error" => "empty_query"]);
    exit();
}

// Search by MRN (exact-ish), mobile number, or name (partial match).
$like = "%" . $query . "%";

$sql = "SELECT
            p.id,
            p.mrn,
            p.full_name,
            p.mobile,
            p.date_of_birth,
            p.gender,
            p.address,
            p.area,
            p.email
        FROM patients p
        WHERE p.mrn LIKE ?
           OR p.mobile LIKE ?
           OR p.full_name LIKE ?
        ORDER BY p.full_name ASC
        LIMIT 20";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "database_error"]);
    exit();
}

mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$patients = [];
while ($row = mysqli_fetch_assoc($result)) {
    $patients[] = $row;
}

echo json_encode(["patients" => $patients]);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>