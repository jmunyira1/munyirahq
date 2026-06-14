<?php
$contracted = (float)$project['contracted_amount'];
$totalPaid  = (float)$project['total_paid'];
$totalCosts = (float)$project['total_costs'];
$profit     = (float)$project['profit'];
$balanceDue = (float)$project['balance_due'];
$fullyPaid  = $totalPaid >= $contracted;
?>

<div class="row g-3">
    <?php foreach ([
                           ['Contracted',  number_format($contracted, 2), 'text-dark',    'cash-stack'],
                           ['Paid',        number_format($totalPaid, 2),  $fullyPaid ? 'text-success' : 'text-warning', 'arrow-down-circle'],
                           ['Balance Due', number_format($balanceDue, 2), $balanceDue > 0 ? 'text-danger' : 'text-success', 'hourglass-split'],
                           ['Costs',       number_format($totalCosts, 2), 'text-danger',  'arrow-up-circle'],
                           ['Profit',      number_format($profit, 2),     $profit >= 0 ? 'text-success' : 'text-danger', 'graph-up'],
                   ] as [$label, $value, $cls, $icon]): ?>
        <div class="col-6 col-md">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="small text-muted mb-1">
                        <i class="bi bi-<?= $icon ?> me-1"></i><?= $label ?>
                    </div>
                    <div class="fw-bold <?= $cls ?>">KES <?= $value ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>