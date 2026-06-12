<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; margin: 0; }
    .header { display: table; width: 100%; margin-bottom: 30px; }
    .header-left { display: table-cell; vertical-align: top; width: 60%; }
    .header-right { display: table-cell; vertical-align: top; text-align: right; }
    .company-name { font-size: 20px; font-weight: bold; color: #1a1a2e; }
    .doc-title { font-size: 22px; font-weight: bold; color: #4f46e5; margin: 0; }
    .doc-number { font-size: 12px; color: #666; }
    .divider { border: none; border-top: 2px solid #4f46e5; margin: 15px 0; }
    .bill-to { margin-bottom: 20px; }
    .bill-to h4 { font-size: 10px; text-transform: uppercase; color: #999; margin: 0 0 5px 0; letter-spacing: 1px; }
    .bill-to p { margin: 2px 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    thead th { background: #4f46e5; color: white; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; }
    thead th.right { text-align: right; }
    tbody td { padding: 8px 10px; border-bottom: 1px solid #eee; }
    tbody td.right { text-align: right; }
    .totals-table { width: 50%; margin-left: auto; }
    .totals-table td { padding: 5px 10px; }
    .totals-table .total-row td { font-weight: bold; font-size: 13px; border-top: 2px solid #4f46e5; padding-top: 8px; }
    .payments-section { margin-top: 20px; }
    .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 10px; }
    .badge-paid { background: #d1fae5; color: #065f46; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .footer { margin-top: 40px; border-top: 1px solid #eee; padding-top: 10px; font-size: 10px; color: #999; text-align: center; }
</style>
</head>
<body>

<?php
$invoiceNo  = 'INV-' . date('Y') . '-' . str_pad($project['id'], 4, '0', STR_PAD_LEFT);
$contracted = (float)$project['contracted_amount'];
$totalPaid  = (float)$project['total_paid'];
$balance    = $contracted - $totalPaid;
$deliveryTotal = array_sum(array_column($project['delivery_items'], 'total_price'));
?>

<div class="header">
    <div class="header-left">
        <div class="company-name">MunyiraHQ</div>
        <div style="color:#666; margin-top:4px;">Financial Management System</div>
    </div>
    <div class="header-right">
        <div class="doc-title">INVOICE</div>
        <div class="doc-number"><?= $invoiceNo ?></div>
        <div style="margin-top:6px; color:#666;">
            Date: <?= date('d F Y') ?><br>
            Due: <?= !empty($project['due_date']) ? date('d F Y', strtotime($project['due_date'])) : 'Upon receipt' ?>
        </div>
    </div>
</div>

<hr class="divider">

<div class="bill-to">
    <h4>Bill To</h4>
    <p><strong><?= esc($project['party_name']) ?></strong></p>
    <?php if (!empty($project['phone'])): ?>
        <p><?= esc($project['phone']) ?></p>
    <?php endif; ?>
    <?php if (!empty($project['email'])): ?>
        <p><?= esc($project['email']) ?></p>
    <?php endif; ?>
    <?php if (!empty($project['address'])): ?>
        <p><?= esc($project['address']) ?></p>
    <?php endif; ?>
</div>

<p><strong>Project:</strong> <?= esc($project['title']) ?></p>
<?php if (!empty($project['description'])): ?>
    <p style="color:#666;"><?= esc($project['description']) ?></p>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Item / Description</th>
            <th class="right">Qty</th>
            <th class="right">Unit Price (KES)</th>
            <th class="right">Total (KES)</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($project['delivery_items'] as $i => $item): ?>
        <tr>
            <td style="color:#999;"><?= $i + 1 ?></td>
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
        <td style="text-align:right;"><?= number_format($deliveryTotal, 2) ?></td>
    </tr>
    <tr>
        <td style="color:#666;">Contracted Amount</td>
        <td style="text-align:right;"><?= number_format($contracted, 2) ?></td>
    </tr>
    <tr>
        <td style="color:#666;">Amount Paid</td>
        <td style="text-align:right; color:#059669;"><?= number_format($totalPaid, 2) ?></td>
    </tr>
    <tr class="total-row">
        <td>Balance Due (KES)</td>
        <td style="text-align:right; color:<?= $balance > 0 ? '#dc2626' : '#059669' ?>;">
            <?= number_format($balance, 2) ?>
        </td>
    </tr>
</table>

<?php if (!empty($project['payments'])): ?>
<div class="payments-section">
    <h4 style="font-size:10px; text-transform:uppercase; color:#999; letter-spacing:1px;">Payment History</h4>
    <table>
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
</div>
<?php endif; ?>

<div style="margin-top:20px; padding:10px; background:#f9fafb; border-radius:4px;">
    <strong>Status:</strong>
    <?php if ($balance <= 0): ?>
        <span class="badge badge-paid">FULLY PAID</span>
    <?php else: ?>
        <span class="badge badge-pending">BALANCE DUE: KES <?= number_format($balance, 2) ?></span>
    <?php endif; ?>
</div>

<div class="footer">
    Generated by MunyiraHQ · <?= date('d F Y H:i') ?> · <?= $invoiceNo ?>
</div>

</body>
</html>
