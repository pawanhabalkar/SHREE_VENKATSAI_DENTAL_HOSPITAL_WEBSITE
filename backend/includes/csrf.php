<?php

/*
    CSRF PROTECTION HELPER
    -----------------------------------------------------
    Include this AFTER session_start(). It provides:

      csrf_token()            -> string
          Returns the current session's CSRF token,
          generating one the first time it's called.

      verify_csrf_token($tok) -> bool
          Constant-time comparison against the session token.

      require_csrf()
          For state-changing (POST) endpoints. Reads the
          token from $_POST['csrf_token'] (form submissions)
          or the X-CSRF-Token header (fetch calls that send
          JSON), and immediately exits with a 403 JSON error
          if it doesn't match. Safe to call on every
          authenticated POST endpoint in this project.
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function verify_csrf_token($token) {
    if (empty($_SESSION["csrf_token"]) || empty($token) || !is_string($token)) {
        return false;
    }
    return hash_equals($_SESSION["csrf_token"], $token);
}

function require_csrf() {
    $token = $_POST["csrf_token"] ?? ($_SERVER["HTTP_X_CSRF_TOKEN"] ?? "");

    if (!verify_csrf_token($token)) {
        http_response_code(403);
        header("Content-Type: application/json");
        echo json_encode(["result" => "error", "error" => "invalid_csrf_token"]);
        exit();
    }
}

?>
