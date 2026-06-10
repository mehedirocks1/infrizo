<?php
require_once '../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_user'])) {
    die("Access Denied.");
}

require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$order_id = $_GET['id'] ?? null;
if (!$order_id) die("No order ID provided.");

// Fetch Order Info
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) die("Order not found.");

// Fetch Order Items
$itemStmt = $pdo->prepare("
    SELECT oi.*, p.name, p.sku 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$itemStmt->execute([$order_id]);
$items = $itemStmt->fetchAll();

// Build HTML for PDF (Inline CSS required for DomPDF)
$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #1e293b; font-size: 14px; }
        .header { border-bottom: 2px solid #0284c7; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #0284c7; letter-spacing: 2px; }
        .details-table { width: 100%; margin-bottom: 30px; }
        .details-table td { padding: 5px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background-color: #f8fafc; padding: 12px; text-align: left; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;}
        .items-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        .total-row { font-weight: bold; font-size: 18px; }
        .footer { margin-top: 50px; font-size: 10px; color: #64748b; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px;}
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">INFRIZO SYSTEMS</div>
        <div style="font-size: 12px; color: #64748b; margin-top: 5px;">OFFICIAL QUOTATION DOCUMENT</div>
    </div>

    <table class="details-table">
        <tr>
            <td width="50%">
                <strong>Quotation To:</strong><br>
                '.htmlspecialchars($order['customer_name']).'<br>
                '.htmlspecialchars($order['customer_email']).'
            </td>
            <td width="50%" style="text-align: right;">
                <strong>Quote Ref:</strong> '.$order['order_number'].'<br>
                <strong>Date:</strong> '.date('F j, Y', strtotime($order['created_at'])).'<br>
                <strong>Status:</strong> Valid for 30 Days
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Asset Description</th>
                <th>Qty</th>
                <th style="text-align:right;">Unit Price</th>
                <th style="text-align:right;">Line Total</th>
            </tr>
        </thead>
        <tbody>';

        foreach($items as $i) {
            $line_total = $i['quantity'] * $i['price_at_order'];
            $html .= '
            <tr>
                <td style="font-family: monospace; font-size: 12px;">'.$i['sku'].'</td>
                <td><strong>'.$i['name'].'</strong></td>
                <td>'.$i['quantity'].'</td>
                <td style="text-align:right;">$'.number_format($i['price_at_order'], 2).'</td>
                <td style="text-align:right;">$'.number_format($line_total, 2).'</td>
            </tr>';
        }

$html .= '
            <tr>
                <td colspan="4" style="text-align:right; padding-top: 20px;" class="total-row">TOTAL ESTIMATE:</td>
                <td style="text-align:right; padding-top: 20px; color: #0284c7;" class="total-row">$'.number_format($order['total_amount'], 2).'</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        This quotation is a system-generated document from Infrizo. Terms and conditions apply upon deployment.
    </div>
</body>
</html>
';

// Setup Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output the PDF
$dompdf->stream($order['order_number'] . "_Quote.pdf", array("Attachment" => false)); // Set true to auto-download