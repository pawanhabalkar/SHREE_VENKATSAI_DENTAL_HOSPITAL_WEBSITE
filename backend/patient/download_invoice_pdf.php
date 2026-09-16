<?php

/*
    DOWNLOAD INVOICE AS PDF (SRS 4.3.4)
    ----------------------------------------
    Requires the FPDF library. Install once via Composer, from
    your project root:

        composer require setasign/fpdf

    This creates a /vendor folder with autoload.php, which this
    file loads below. If you don't use Composer, download FPDF
    manually from http://www.fpdf.org/ and require its fpdf.php
    directly instead of the autoloader line.
*/

session_start();

require_once "../auth/patient_api_auth.php";
require_once "../config/database.php";
$autoloadPath = __DIR__ . "/../../vendor/autoload.php";
if (!file_exists($autoloadPath)) {
    // Don't let a missing dependency surface a raw PHP fatal error
    // (which could leak the server's file paths) — fail cleanly instead.
    http_response_code(500);
    error_log("FPDF/vendor autoload not found at $autoloadPath — run 'composer require setasign/fpdf'.");
    die("PDF generation is temporarily unavailable. Please try again later.");
}
require_once $autoloadPath;

$patient_id = $_SESSION["patient_id"];
$invoice_id = intval($_GET["invoice_id"] ?? 0);

if (empty($invoice_id)) {
    die("Missing invoice ID.");
}

// Ownership check baked into the WHERE clause — a patient can
// only ever download their own invoice.
$sql = "SELECT i.*, p.full_name, p.mrn
        FROM invoices i
        JOIN patients p ON i.patient_id = p.id
        WHERE i.id = ? AND i.patient_id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $invoice_id, $patient_id);
mysqli_stmt_execute($stmt);
$invoice = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$invoice) {
    die("Invoice not found.");
}

// Pull payments made against this invoice for the breakdown.
$paySql = "SELECT amount, payment_method, payment_date, status
           FROM payments
           WHERE invoice_id = ?
           ORDER BY payment_date ASC";
$payStmt = mysqli_prepare($conn, $paySql);
mysqli_stmt_bind_param($payStmt, "i", $invoice_id);
mysqli_stmt_execute($payStmt);
$payments = mysqli_stmt_get_result($payStmt);

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont("Arial", "B", 16);
$pdf->Cell(0, 10, "SHREE VENKATSAI DENTAL HOSPITAL", 0, 1, "C");

$pdf->SetFont("Arial", "", 11);
$pdf->Cell(0, 8, "Invoice", 0, 1, "C");
$pdf->Ln(5);

$pdf->SetFont("Arial", "", 11);
$pdf->Cell(95, 7, "Invoice Number: " . $invoice["invoice_number"], 0, 0);
$pdf->Cell(95, 7, "Invoice Date: " . $invoice["invoice_date"], 0, 1);

$pdf->Cell(95, 7, "Patient: " . $invoice["full_name"], 0, 0);
$pdf->Cell(95, 7, "MRN: " . $invoice["mrn"], 0, 1);
$pdf->Ln(5);

$pdf->SetFont("Arial", "B", 11);
$pdf->Cell(70, 8, "Total Amount", 1);
$pdf->Cell(60, 8, "Discount", 1);
$pdf->Cell(60, 8, "Net Amount", 1);
$pdf->Ln();

$pdf->SetFont("Arial", "", 11);
$pdf->Cell(70, 8, "Rs. " . number_format($invoice["total_amount"], 2), 1);
$pdf->Cell(60, 8, "Rs. " . number_format($invoice["discount"], 2), 1);
$pdf->Cell(60, 8, "Rs. " . number_format($invoice["net_amount"], 2), 1);
$pdf->Ln(12);

$pdf->SetFont("Arial", "B", 11);
$pdf->Cell(0, 8, "Payment History", 0, 1);

$pdf->SetFont("Arial", "B", 10);
$pdf->Cell(50, 7, "Date", 1);
$pdf->Cell(40, 7, "Amount", 1);
$pdf->Cell(50, 7, "Method", 1);
$pdf->Cell(50, 7, "Status", 1);
$pdf->Ln();

$pdf->SetFont("Arial", "", 10);
$hasPayments = false;

while ($p = mysqli_fetch_assoc($payments)) {
    $hasPayments = true;
    $pdf->Cell(50, 7, $p["payment_date"], 1);
    $pdf->Cell(40, 7, "Rs. " . number_format($p["amount"], 2), 1);
    $pdf->Cell(50, 7, $p["payment_method"], 1);
    $pdf->Cell(50, 7, $p["status"], 1);
    $pdf->Ln();
}

if (!$hasPayments) {
    $pdf->Cell(190, 7, "No payments recorded yet.", 1, 1, "C");
}

$pdf->Ln(8);
$pdf->SetFont("Arial", "B", 12);
$pdf->Cell(0, 8, "Balance Due: Rs. " . number_format($invoice["due_amount"], 2), 0, 1);

mysqli_close($conn);

$pdf->Output("D", "Invoice-" . $invoice["invoice_number"] . ".pdf");
?>