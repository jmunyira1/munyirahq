<?php

/**
 * Type → badge class and label
 */
function txBadge(string $type): string
{
    return match($type) {
        'income'       => 'text-bg-success',
        'expense'      => 'text-bg-danger',
        'debt_payment' => 'text-bg-warning',
        'transfer'     => 'text-bg-secondary',
        default        => 'text-bg-light',
    };
}

function txLabel(string $type): string
{
    return match($type) {
        'income'       => 'Income',
        'expense'      => 'Expense',
        'debt_payment' => 'Debt Payment',
        'transfer'     => 'Transfer',
        default        => ucfirst($type),
    };
}

/**
 * Context line below the description — what account, category, or debt
 */
function txContext(array $tx): string
{
    return match($tx['transaction_type']) {
        'income'  => '<i class="bi bi-arrow-down-circle text-success me-1"></i>' . esc($tx['account_name']),
        'expense' => '<i class="bi bi-tag text-muted me-1"></i>'
                . esc($tx['parent_category_name'] ?? '') . ' › ' . esc($tx['category_name'] ?? '—')
                . ' &nbsp;<span class="text-muted">·</span>&nbsp; '
                . '<i class="bi bi-wallet2 text-muted me-1"></i>' . esc($tx['account_name']),
        'debt_payment' => '<i class="bi bi-credit-card text-warning me-1"></i>' . esc($tx['debt_name'] ?? '—')
                . ' &nbsp;from&nbsp; ' . esc($tx['account_name']),
        'transfer' => '<i class="bi bi-arrow-left-right text-secondary me-1"></i>'
                . esc($tx['account_name']) . ' → ' . esc($tx['transfer_to_account_name'] ?? '—'),
        default => '',
    };
}

/**
 * Amount colour and sign
 */
function txAmountClass(string $type): string
{
    return match($type) {
        'income'   => 'text-success',
        'expense',
        'debt_payment',
        'transfer' => 'text-danger',
        default    => '',
    };
}

function txAmountSign(string $type): string
{
    return $type === 'income' ? '+' : '-';
}

?>

    {{-- ── Monthly summary bar ── --}}
    <div class="row g-3 mb-3">
        <?php
        $summaryCards = [
                ['label' => 'Income',       'key' => 'income',       'icon' => 'arrow-down-circle', 'cls' => 'text-success'],
                ['label' => 'Expenses',     'key' => 'expense',      'icon' => 'arrow-up-circle',   'cls' => 'text-danger'],
                ['label' => 'Debt Payments','key' => 'debt_payment', 'icon' => 'credit-card',       'cls' => 'text-warning'],
                ['label' => 'Transfers',    'key' => 'transfer',     'icon' => 'arrow-left-right',  'cls' => 'text-secondary'],
        ];
        ?>
        <?php foreach ($summaryCards as $card): ?>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-<?= $card['icon'] ?> <?= $card['cls'] ?>"></i>
                            <span class="small text-muted"><?= $card['label'] ?></span>
                        </div>
                        <div class="fw-bold fs-6 <?= $card['cls'] ?>">
                            <?= number_format($summary[$card['key']] ?? 0, 2) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    {{-- ── Transaction table ── --}}
    <div class="table-responsive bg-white shadow-sm rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-7 text-muted">
            <tr>
                <th class="ps-4">Date</th>
                <th>Type</th>
                <th>Description / Context</th>
                <th class="text-end pe-4">Amount</th>
                <th class="text-end pe-4">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($transactions)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-receipt fs-2 d-block mb-2 opacity-25"></i>
                        No transactions found for this period.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td class="ps-4 text-muted small" style="white-space:nowrap;">
                            <?= date('d M Y', strtotime($tx['transaction_date'])) ?>
                            <div class="text-muted" style="font-size:0.7rem;">
                                <?= date('H:i', strtotime($tx['transaction_date'])) ?>
                            </div>
                        </td>

                        <td>
                    <span class="badge fw-normal <?= txBadge($tx['transaction_type']) ?>">
                        <?= txLabel($tx['transaction_type']) ?>
                    </span>
                        </td>

                        <td>
                            <?php if (!empty($tx['description'])): ?>
                                <div class="fw-semibold small text-dark"><?= esc($tx['description']) ?></div>
                            <?php endif; ?>
                            <div class="text-muted small"><?= txContext($tx) ?></div>
                        </td>

                        <td class="text-end fw-bold pe-4 <?= txAmountClass($tx['transaction_type']) ?>"
                            style="white-space:nowrap;">
                            <?= txAmountSign($tx['transaction_type']) ?>
                            <?= esc($tx['currency'] ?? 'KES') ?>
                            <?= number_format((float)$tx['amount'], 2) ?>
                        </td>

                        <td class="text-end pe-4">
                            <?php if ($tx['transaction_type'] !== 'transfer'): ?>
                                <button class="btn btn-sm btn-outline-danger"
                                        hx-post="<?= base_url('transaction/destroy/' . $tx['id']) ?>"
                                        hx-confirm="Delete this transaction? The balance effect will be reversed."
                                        hx-target="#transactions-list-container"
                                        hx-swap="innerHTML"
                                        title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php else: ?>
                                <span class="text-muted small" title="Transfers cannot be deleted">
                        <i class="bi bi-lock"></i>
                    </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ── --}}
<?php
$totalPages = (int) ceil($total / $perPage);
if ($totalPages > 1):
    ?>
    <nav class="mt-3 d-flex justify-content-center">
        <ul class="pagination pagination-sm mb-0">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link"
                       hx-get="<?= url_to('transactions.list') ?>?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"
                       hx-target="#transactions-list-container"
                       hx-swap="innerHTML"
                       href="#">
                        <?= $p ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>