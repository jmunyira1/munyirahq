<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 15px;
            color: #333;
            margin: 0;
            padding: 10px;
            background: #fff;
        }
        .invoice-box {
            border: 1px solid #bbb;
            padding: 25px 40px;
            border-radius: 6px;
        }
        .header, .meta, .client-info {
            width: 100%;
            margin-bottom: 10px;
        }
        .header td, .meta td {
            vertical-align: top;
        }
        .brand-name {
            font-size: 30px;
            font-weight: 600;
            letter-spacing: 1px;
            color: #000;
        }
        .header .right {
            text-align: right;
            font-size: 26px;
            text-transform: uppercase;
            font-weight: bold;
            color: #000;
        }
        hr {
            border: none;
            border-top: 1px solid #aaa;
            margin: 12px 0;
        }
        .meta td {
            font-size: 16px;
            color: #444;
        }
        .meta strong {
            font-weight: bold;
            color: #000;
            font-size: 16px;
        }
        .meta .right {
            text-align: right;
        }
        .client-info {
            margin-top: 12px;
            font-size: 16px;
        }
        .client-info p {
            margin: 0 0 5px;
            line-height: 1.4;
        }
        .client-info strong {
            font-weight: bold;
            font-size: 17px;
        }
        .description-section {
            margin: 12px 0;
            font-size: 15px;
            background: #f5f5f5;
            padding: 12px 15px;
            border-left: 4px solid #333;
            border-radius: 2px;
        }
        .description-label {
            margin: 0 0 6px;
            font-size: 16px;
        }
        .description-text {
            margin: 0;
            color: #2a2a2a;
            line-height: 1.6;
        }
        table.items {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 12px;
            font-size: 15px;
            border-radius: 6px;
            overflow: hidden;
        }
        table.items th, table.items td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        table.items th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }
        table.items .right {
            text-align: right;
        }
        .totals-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .totals-table td {
            padding: 6px 10px;
            border: none;
            font-size: 15px;
        }
        .totals-table .total-row td {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .contact-info {
            font-size: 15px;
            color: #333;
            margin-top: 15px;
        }
        .contact-info p {
            margin: 0 0 5px;
            line-height: 1.4;
        }
        .footer {
            text-align: center;
            font-size: 13px;
            color: #555;
            line-height: 1.5;
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>

<?php
$invoiceNo     = 'INV-' . date('Y') . '-' . str_pad($project['id'], 4, '0', STR_PAD_LEFT);
$contracted    = (float)$project['contracted_amount'];
$totalPaid     = (float)$project['total_paid'];
$balance       = $contracted - $totalPaid;
$deliveryTotal = array_sum(array_column($project['delivery_items'], 'total_price'));
?>

<div class="invoice-box">
    <table class="header">
        <tr>
            <td class="brand-name">MunyiraHQ</td>
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
            <td style="color:#666;">Subtotal</td>
            <td style="text-align:right;">KES <?= number_format($deliveryTotal, 2) ?></td>
        </tr>
        <tr>
            <td style="color:#666;">Contracted Amount</td>
            <td style="text-align:right;">KES <?= number_format($contracted, 2) ?></td>
        </tr>
        <tr>
            <td style="color:#666;">Amount Paid</td>
            <td style="text-align:right; color:#059669;">KES <?= number_format($totalPaid, 2) ?></td>
        </tr>
        <tr class="total-row">
            <td>Balance Due</td>
            <td style="text-align:right; color:<?= $balance > 0 ? '#dc2626' : '#059669' ?>;">
                KES <?= number_format($balance, 2) ?>
            </td>
        </tr>
    </table>

    <?php if (!empty($project['payments'])): ?>
        <hr>
        <div class="client-info">
            <p><strong>Payment History:</strong></p>
        </div>
        <table class="items" style="margin-top: 5px;">
            <thead>
            <tr>
                <th>Date</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="right">Amount (KES)</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($project['payments'] as $pay): ?>
                <tr>
                    <td><?= date('d M Y', strtotime($pay['payment_date'])) ?></td>
                    <td><?= esc($pay['method'] ?? '—') ?></td>
                    <td><?= esc($pay['reference'] ?? '—') ?></td>
                    <td class="right" style="color:#059669;"><?= number_format((float)$pay['amount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="margin-top:20px; padding:10px; background:#f9fafb; border: 1px solid #ddd; border-radius:4px;">
        <strong>Status:</strong>
        <?php if ($balance <= 0): ?>
            <span class="badge badge-paid">FULLY PAID</span>
        <?php else: ?>
            <span class="badge badge-pending">BALANCE DUE: KES <?= number_format($balance, 2) ?></span>
        <?php endif; ?>
    </div>

    <hr>

    <div class="contact-info">
        <p><strong>Contact Us:</strong></p>
        <p>
            MunyiraHQ<br>
            Phone: +254 711 318 428<br>
            Email: jmunyira1@gmail.com
        </p>
    </div>

    <div class="footer">
        <p>Thank you for your business.<br>
            This is a computer-generated document. No signature is required.<br>
            <small style="color: #888;">Generated via MunyiraHQ on <?= date('d M Y H:i') ?> · <?= $invoiceNo ?></small></p>
    </div>
</div>

</body>
</html>