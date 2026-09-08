<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "shree_venkatsai_dental_hospital";

$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database
);

if (!$conn) {
    die("Connection Failed : " . mysqli_connect_error());
}