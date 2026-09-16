<?php

// Database connection.
//
// NOTE: these are local development defaults (matching a typical
// XAMPP/WAMP setup with an unpassworded root MySQL user). Before
// deploying this to a real server, replace them with a dedicated,
// least-privilege MySQL user + a real password, and set those
// values via environment variables or a config file that is
// NOT committed to source control, instead of hardcoding them here.
$host     = getenv("DB_HOST") ?: "localhost";
$user     = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";
$database = getenv("DB_NAME") ?: "shree_venkatsai_dental_hospital";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    // Never echo mysqli_connect_error() to the browser — it can
    // reveal internal hostnames/paths. Log it server-side instead
    // and show the visitor a generic message.
    error_log("Database connection failed: " . mysqli_connect_error());
    http_response_code(500);
    die("Service temporarily unavailable. Please try again shortly.");
}

mysqli_set_charset($conn, "utf8mb4");

?>
