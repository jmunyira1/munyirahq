<?php
$income     = $balanceData['income'] ?? 0;
$categories = $balanceData['categories'] ?? [];
$totalSpent = $summary['expense'] ?? 0;
$totalAvail = $income - $totalSpent;
?>

    <div class="row g-3 mb-4">

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">
                        <i class="bi bi-arrow-down-circle text-success me-1"></i> Income
                    </div>
                    <div class="fw-bold fs-5 text-success">
                        <?= number_format($income, 2) ?>
                    </div>
                    <div class="text-muted" style="font-size:0.7rem;"><?= esc($month) ?></div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">
                        <i class="bi bi-arrow-up-circle text-danger me-1"></i> Spent
                    </div>
                    <div class="fw-bold fs-5 text-danger">
                        <?= number_format($totalSpent, 2) ?>
                    </div>
                    <?php if ($income > 0): ?>
                        <div class="progress mt-1" style="height:4px;">
                            <div class="progress-bar bg-danger"
                                 style="width:<?= min(100, round(($totalSpent/$income)*100)) ?>%"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">
                        <i class="bi bi-wallet2 me-1 <?= $totalAvail >= 0 ? 'text-primary' : 'text-danger' ?>"></i>
                        Available
                    </div>
                    <div class="fw-bold fs-5 <?= $totalAvail >= 0 ? 'text-primary' : 'text-danger' ?>">
                        <?= number_format($totalAvail, 2) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted mb-1">
                        <i class="bi bi-credit-card text-warning me-1"></i> Debt Owed
                    </div>
                    <div class="fw-bold fs-5 text-warning">
                        <?= number_format($totalDebtOwed, 2) ?>
                    </div>
                    <div class="text-muted" style="font-size:0.7rem;">
                        <?= count($debts) ?> active debt(s)
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-2 mb-4">
        <?php foreach ($accounts as $account): ?>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-2 px-3">
                        <div class="small text-muted text-truncate"><?= esc($account['account_name']) ?></div>
                        <div class="fw-semibold <?= (float)$account['current_balance'] < 0 ? 'text-danger' : '' ?>">
                            <?= number_format((float)$account['current_balance'], 2) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php if (empty($categories)): ?>
    <div class="text-center text-muted py-5">
        <i class="bi bi-tags fs-2 d-block mb-2 opacity-25"></i>
        No categories set up yet.
        <a href="<?= url_to('categories') ?>">Add categories</a> to see budget breakdown.
    </div>
<?php else: ?>

    <div class="d-flex flex-column gap-3">
        <?php foreach ($categories as $parent):
            $poolPct = $income > 0 ? min(100, round(($parent['pool_spent'] / max(1, $parent['pool'])) * 100)) : 0;
            $poolAvailPct = 100 - $poolPct;
            ?>

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-bottom-0 py-2 px-4"
                     style="cursor:pointer;"
                     data-bs-toggle="collapse"
                     data-bs-target="#cat-<?= $parent['id'] ?>">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-chevron-down text-muted small"></i>
                            <span class="fw-semibold"><?= esc($parent['name']) ?></span>
                            <span class="text-muted small">
                        <?= number_format($parent['allocation_percentage'] * 100, 1) ?>% of income
                    </span>
                        </div>
                        <div class="d-flex align-items-center gap-4 small">
                    <span class="text-muted">
                        Pool: <strong>KES <?= number_format($parent['pool'], 2) ?></strong>
                    </span>
                            <span class="text-danger">
                        Spent: <strong>KES <?= number_format($parent['pool_spent'], 2) ?></strong>
                    </span>
                            <span class="<?= $parent['pool_available'] >= 0 ? 'text-success' : 'text-danger' ?>">
                        Available: <strong>KES <?= number_format($parent['pool_available'], 2) ?></strong>
                    </span>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height:4px;">
                        <div class="progress-bar <?= $poolPct >= 100 ? 'bg-danger' : ($poolPct >= 75 ? 'bg-warning' : 'bg-primary') ?>"
                             role="progressbar" style="width:<?= $poolPct ?>%"></div>
                    </div>
                </div>

                <div class="collapse show" id="cat-<?= $parent['id'] ?>">
                    <?php if (empty($parent['children'])): ?>
                        <div class="card-body py-3 text-muted small ps-5">
                            No subcategories yet.
                            <a href="<?= url_to('category.form') ?>?parent_id=<?= $parent['id'] ?>">Add one</a>
                        </div>
                    <?php else: ?>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light text-uppercase text-muted" style="font-size:0.7rem;">
                                <tr>
                                    <th class="ps-5">Subcategory</th>
                                    <th>Account</th>
                                    <th class="text-end">Allocated</th>
                                    <th class="text-end">Spent</th>
                                    <th class="text-end">Available</th>
                                    <th style="width:140px;">Usage</th>
                                    <th class="pe-4 text-end">Items</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($parent['children'] as $sub):
                                    $availClass = $sub['available'] >= 0 ? 'text-success' : 'text-danger';
                                    $barClass   = $sub['pct_used'] >= 100 ? 'bg-danger'
                                        : ($sub['pct_used'] >= 75 ? 'bg-warning' : 'bg-success');
                                    ?>
                                    <tr>
                                        <td class="ps-5">
                                            <i class="bi bi-arrow-return-right text-muted me-2"></i>
                                            <span class="fw-semibold"><?= esc($sub['name']) ?></span>
                                        </td>
                                        <td>
                                <span class="badge text-bg-light border text-dark fw-normal">
                                    <i class="bi bi-wallet2 me-1"></i>
                                    <?= esc($sub['account_name'] ?? '—') ?>
                                </span>
                                        </td>
                                        <td class="text-end text-muted">
                                            <?= number_format($sub['allocated'], 2) ?>
                                        </td>
                                        <td class="text-end text-danger">
                                            <?= number_format($sub['spent'], 2) ?>
                                        </td>
                                        <td class="text-end fw-bold <?= $availClass ?>">
                                            <?= number_format($sub['available'], 2) ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height:6px;">
                                                    <div class="progress-bar <?= $barClass ?>"
                                                         style="width:<?= $sub['pct_used'] ?>%"></div>
                                                </div>
                                                <span class="text-muted" style="width:30px;">
                                        <?= $sub['pct_used'] ?>%
                                    </span>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-secondary"
                                                    hx-get="<?= url_to('budget_items.list', $sub['id']) ?>"
                                                    hx-target="#items-<?= $sub['id'] ?>"
                                                    hx-swap="innerHTML"
                                                    hx-trigger="click once"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#items-collapse-<?= $sub['id'] ?>"
                                                    title="Budget items">
                                                <i class="bi bi-list-check"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td colspan="8" class="p-0">
                                            <div class="collapse" id="items-collapse-<?= $sub['id'] ?>">
                                                <div id="items-<?= $sub['id'] ?>"
                                                     hx-trigger="refreshBudgetItems_<?= $sub['id'] ?> from:body"
                                                     hx-get="<?= url_to('budget_items.list', $sub['id']) ?>"
                                                     hx-swap="innerHTML"
                                                     class="p-3">
                                                    <div class="text-muted small">
                                                        <div class="spinner-border spinner-border-sm me-1"></div>
                                                        Loading…
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php if (!empty($pendingItems)): ?>
    <div class="mt-4">
        <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:0.75rem;">
            <i class="bi bi-clock me-1"></i> Pending Budget Items
        </h6>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light text-uppercase text-muted" style="font-size:0.7rem;">
                    <tr>
                        <th class="ps-4">Item</th>
                        <th>Category</th>
                        <th>Account</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end pe-4">Due</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pendingItems as $item):
                        $overdue = !empty($item['due_date']) && strtotime($item['due_date']) < time();
                        ?>
                        <tr>
                            <td class="ps-4 fw-semibold"><?= esc($item['name']) ?></td>
                            <td class="text-muted">
                                <?= esc($item['parent_name']) ?> › <?= esc($item['category_name']) ?>
                            </td>
                            <td>
                        <span class="badge text-bg-light border text-dark fw-normal">
                            <?= esc($item['account_name'] ?? '—') ?>
                        </span>
                            </td>
                            <td>
                        <span class="badge <?= $item['item_type'] === 'recurring' ? 'text-bg-info' : 'text-bg-secondary' ?> fw-normal">
                            <?= $item['item_type'] === 'recurring'
                                ? ucfirst($item['recurrence'] ?? '')
                                : 'One-off' ?>
                        </span>
                            </td>
                            <td class="text-end fw-bold">
                                <?= number_format((float)$item['amount'], 2) ?>
                            </td>
                            <td class="text-end pe-4 <?= $overdue ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                <?= !empty($item['due_date'])
                                    ? ($overdue ? '<i class="bi bi-exclamation-circle me-1"></i>' : '')
                                    . date('d M Y', strtotime($item['due_date']))
                                    : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>