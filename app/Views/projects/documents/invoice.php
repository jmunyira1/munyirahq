<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 15px;
            color: #000;
            margin: 0;
            padding: 5px;
            background: #fff;
        }
        .invoice-box {
            border: 1px solid #000;
            padding: 15px 20px;
            border-radius: 6px;
        }
        .header, .meta, .client-info {
            width: 100%;
            margin-bottom: 5px;
        }
        .header td, .meta td {
            vertical-align: top;
        }
        .brand-name {
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 1px;
            color: #000;
        }
        .header .right {
            text-align: right;
            font-size: 24px;
            text-transform: uppercase;
            font-weight: bold;
            color: #000;
        }
        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 6px 0;
        }
        .meta td {
            font-size: 15px;
            color: #000;
        }
        .meta strong {
            font-weight: bold;
            color: #000;
            font-size: 15px;
        }
        .meta .right {
            text-align: right;
        }
        .client-info {
            margin-top: 6px;
            font-size: 15px;
            color: #000;
        }
        .client-info p {
            margin: 0 0 3px;
            line-height: 1.2;
        }
        .client-info strong {
            font-weight: bold;
            font-size: 16px;
            color: #000;
        }
        .description-section {
            margin: 6px 0;
            font-size: 14px;
            background: #f5f5f5;
            padding: 8px 10px;
            border-left: 4px solid #000;
            border-radius: 2px;
        }
        .description-label {
            margin: 0 0 3px;
            font-size: 15px;
            color: #000;
        }
        .description-text {
            margin: 0;
            color: #000;
            line-height: 1.4;
        }
        table.items {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 6px;
            font-size: 14px;
            border-radius: 6px;
            overflow: hidden;
        }
        table.items th, table.items td {
            padding: 6px 8px;
            border: 1px solid #000;
        }
        table.items th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: left;
            color: #000;
        }
        table.items .right {
            text-align: right;
        }
        .totals-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .totals-table td {
            padding: 4px 8px;
            border: none;
            font-size: 14px;
            color: #000;
        }
        .totals-table .total-row td {
            font-weight: bold;
            font-size: 15px;
            border-top: 2px solid #000;
            padding-top: 6px;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-paid { background: #d1fae5; color: #000; }
        .badge-pending { background: #fef3c7; color: #000; }
        .contact-info {
            font-size: 14px;
            color: #000;
            margin-top: 8px;
        }
        .contact-info p {
            margin: 0 0 3px;
            line-height: 1.2;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #000;
            line-height: 1.3;
            margin-top: 8px;
            padding-top: 4px;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>

<?php
$invoiceNo     = 'INV-' . date('y') . str_pad($project['id'], 2, '0', STR_PAD_LEFT);
$contracted    = (float)$project['contracted_amount'];
$totalPaid     = (float)$project['total_paid'];
$balance       = $contracted - $totalPaid;
$deliveryTotal = array_sum(array_column($project['delivery_items'], 'total_price'));
?>

<div class="invoice-box">
    <table class="header">
        <tr>
            <td class="brand-name">Logia</td>
            <td class="right">Invoice</td>
        </tr>
    </table>

    <hr>

    <table class="meta">
        <tr>
            <td>Invoice No: <strong><?= $invoiceNo ?></strong></td>
            <td class="right">Date: <strong><?= date('d M Y') ?></strong></td>
        </tr>
        <tr>
            <td></td>
            <td class="right">Due: <strong><?= !empty($project['due_date']) ? date('d M Y', strtotime($project['due_date'])) : 'Upon receipt' ?></strong></td>
        </tr>
    </table>

    <hr>

    <div class="client-info">
        <p><strong>Invoice To:</strong></p>
        <p>
            <?= esc($project['party_name']) ?><br>
            <?php if (!empty($project['email'])): ?><?= esc($project['email']) ?><br><?php endif; ?>
            <?php if (!empty($project['phone'])): ?><?= esc($project['phone']) ?><br><?php endif; ?>
            <?php if (!empty($project['address'])): ?><?= esc($project['address']) ?><?php endif; ?>
        </p>
    </div>

    <hr>

    <div class="description-section">
        <p class="description-label"><strong>Project:</strong> <?= esc($project['title']) ?></p>
        <?php if (!empty($project['description'])): ?>
            <p class="description-text"><?= esc($project['description']) ?></p>
        <?php endif; ?>
    </div>

    <table class="items">
        <thead>
        <tr>
            <th style="width: 5%;">#</th>
            <th>Item / Description</th>
            <th class="right" style="width: 10%;">Qty</th>
            <th class="right" style="width: 20%;">Unit Price (KES)</th>
            <th class="right" style="width: 20%;">Total (KES)</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($project['delivery_items'] as $i => $item): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= esc($item['name']) ?></td>
                <td class="right"><?= $item['quantity'] ?></td>
                <td class="right"><?= number_format((float)$item['unit_price'], 2) ?></td>
                <td class="right"><?= number_format((float)$item['total_price'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal</td>
            <td style="text-align:right;">KES <?= number_format($deliveryTotal, 2) ?></td>
        </tr>
        <tr>
            <td>Contracted Amount</td>
            <td style="text-align:right;">KES <?= number_format($contracted, 2) ?></td>
        </tr>
        <tr>
            <td>Amount Paid</td>
            <td style="text-align:right;">KES <?= number_format($totalPaid, 2) ?></td>
        </tr>
        <tr class="total-row">
            <td>Balance Due</td>
            <td style="text-align:right;">
                KES <?= number_format($balance, 2) ?>
            </td>
        </tr>
    </table>

    <hr>

    <div class="contact-info">
        <p><strong>Contact Us:</strong></p>
        <p>
            Logia<br>
            Phone: +254 711 318 428<br>
            Email: jmunyira1@gmail.com
        </p>
    </div>

    <div class="footer">
        <p>Thank you for your business.<br>
            This is a computer-generated document. No signature is required.<br>
            <small>Generated via Logia on <?= date('d M Y H:i') ?> · <?= $invoiceNo ?></small></p>
    </div>
</div>

</body>
</html>