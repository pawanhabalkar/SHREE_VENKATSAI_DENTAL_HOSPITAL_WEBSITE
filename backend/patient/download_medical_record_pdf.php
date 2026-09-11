<?php

/*
    DOWNLOAD MEDICAL RECORD AS PDF (SRS 4.3.5)
    ----------------------------------------------
    Same FPDF dependency as download_invoice_pdf.php — see the
    comment at the top of that file for install instructions.
*/

session_start();

require_once "../auth/patient_auth.php";
require_once "../config/database.php";
require_once "../../vendor/autoload.php"; // adjust path if your vendor/ folder is elsewhere

$patient_id = $_SESSION["patient_id"];
$record_id = intval($_GET["record_id"] ?? 0);

if (empty($record_id)) {
    die("Missing record ID.");
}

// Ownership check baked into the WHERE clause — a patient can
// never download another patient's medical record.
$sql = "SELECT r.*, p.full_name, p.mrn, d.doctor_name
        FROM medical_records r
        JOIN patients p ON r.patient_id = p.id
        LEFT JOIN doctors d ON r.doctor_id = d.id
        WHERE r.id = ? AND r.patient_id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $record_id, $patient_id);
mysqli_stmt_execute($stmt);
$record = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$record) {
    die("Medical record not found.");
}

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont("Arial", "B", 16);
$pdf->Cell(0, 10, "SHREE VENKATSAI DENTAL HOSPITAL", 0, 1, "C");

$pdf->SetFont("Arial", "", 11);
$pdf->Cell(0, 8, "Medical Record", 0, 1, "C");
$pdf->Ln(5);

$pdf->SetFont("Arial", "", 11);
$pdf->Cell(95, 7, "Patient: " . $record["full_name"], 0, 0);
$pdf->Cell(95, 7, "MRN: " . $record["mrn"], 0, 1);

$pdf->Cell(95, 7, "Record Date: " . ($record["record_date"] ?? "N/A"), 0, 0);
$pdf->Cell(95, 7, "Doctor: " . ($record["doctor_name"] ?? "N/A"), 0, 1);

$pdf->Cell(0, 7, "Type: " . ($record["record_type"] ?? "N/A"), 0, 1);
$pdf->Ln(5);

$pdf->SetFont("Arial", "B", 12);
$pdf->Cell(0, 8, $record["title"] ?? "Record", 0, 1);

$pdf->SetFont("Arial", "", 11);
$pdf->MultiCell(0, 7, $record["description"] ?? "No description provided.");

mysqli_close($conn);

$pdf->Output("D", "MedicalRecord-" . $record["id"] . ".pdf");
?>