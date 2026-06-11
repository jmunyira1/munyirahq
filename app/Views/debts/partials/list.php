<?php

use App\Models\Debt as DebtModel;

function _debtPartyInitials(array $debt): string
{
    $parts = explode(' ', trim($debt['party_name'] ?? ''));
    $i = strtoupper($parts[0][0] ?? '?');
    if (count($parts) >= 2) $i .= strtoupper(end($parts)[0]);
    return $i;
}

function _debtGenderPrefix(array $debt): string
{
    if (!($debt['is_person'] ?? false)) return '';
    if (($debt['gender'] ?? null) === null || $debt['gender'] === '') return '';
    return $debt['gender'] == 1 ? 'Ms. ' : 'Mr. ';
}

?>

<div class="table-responsive shadow-sm rounded">
    <table class="table table-hover align-middle mb-0">
        <thead class="text-uppercase fs-7">
        <tr>
            <th class="ps-4">Party</th>
            <th>Type</th>
            <th>Due Date</th>
            <th class="text-end">Principal</th>
            <th class="text-end">Balance</th>
            <th class="text-end">Progress</th>
            <th class="text-end pe-4">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($debts)): ?>
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-cash-stack fs-2 d-block mb-2 opacity-25"></i>
                    <?= $includeSettled ? 'No debt records found.' : 'No outstanding debts.' ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($debts as $debt):
                $paid    = (float)$debt['total_principal'] - (float)$debt['current_balance'];
                $pct     = (float)$debt['total_principal'] > 0
                        ? round(($paid / (float)$debt['total_principal']) * 100)
                        : 0;
                $overdue = !empty($debt['due_date'])
                        && $debt['status'] == 0
                        && strtotime($debt['due_date']) < time();
                ?>
                <tr class="<?= $debt['status'] == 1 ? 'text-muted' : '' ?>">

                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-initials bg-light text-secondary rounded-circle d-flex
                                    align-items-center justify-content-center border fw-bold small"
                                 style="width:38px;height:38px;flex-shrink:0;">
                                <?= _debtPartyInitials($debt) ?>
                            </div>
                            <div>
                            <span class="fw-semibold d-block <?= $debt['status'] == 1 ? 'text-decoration-line-through' : 'text-dark' ?>">
                                <?= esc(_debtGenderPrefix($debt) . $debt['party_name']) ?>
                            </span>
                                <?php if ($debt['status'] == 1): ?>
                                    <span class="badge text-bg-success fw-normal">Paid</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>

                    <td>
                    <span class="badge fw-normal <?= DebtModel::typeBadgeClass($debt['debt_type']) ?>">
                        <?= DebtModel::typeLabel($debt['debt_type']) ?>
                    </span>
                    </td>

                    <td class="small <?= $overdue ? 'text-danger fw-semibold' : 'text-muted' ?>">
                        <?php if (!empty($debt['due_date'])): ?>
                            <?= $overdue ? '<i class="bi bi-exclamation-circle me-1"></i>' : '' ?>
                            <?= date('d M Y', strtotime($debt['due_date'])) ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-end small text-muted">
                        <?= number_format((float)$debt['total_principal'], 2) ?>
                    </td>

                    <td class="text-end fw-bold <?= $debt['status'] == 1 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format((float)$debt['current_balance'], 2) ?>
                    </td>

                    <td class="text-end" style="min-width:120px;">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar <?= $debt['status'] == 1 ? 'bg-success' : 'bg-warning' ?>"
                                     role="progressbar"
                                     style="width:<?= $pct ?>%">
                                </div>
                            </div>
                            <span class="small text-muted" style="width:32px;"><?= $pct ?>%</span>
                        </div>
                    </td>

                    <td class="text-end pe-4">
                        <div class="d-inline-flex gap-2">
                            <?php if ($debt['status'] == 0): ?>
                                <button class="btn btn-sm btn-outline-warning"
                                        hx-get="<?= url_to('transaction.form') ?>?type=debt_payment&debt_id=<?= $debt['id'] ?>"
                                        hx-target="#debt-modal-body"
                                        hx-swap="innerHTML"
                                        data-bs-toggle="modal"
                                        data-bs-target="#debtModal"
                                        title="Record Payment">
                                    <i class="bi bi-credit-card"></i>
                                </button>
                            <?php endif; ?>

                            <button class="btn btn-sm btn-outline-secondary"
                                    hx-get="<?= url_to('debt.edit', $debt['id']) ?>"
                                    hx-target="#debt-modal-body"
                                    hx-swap="innerHTML"
                                    data-bs-toggle="modal"
                                    data-bs-target="#debtModal"
                                    title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <button class="btn btn-sm btn-outline-danger"
                                    hx-post="<?= base_url('debt/destroy/' . $debt['id']) ?>"
                                    hx-confirm="Delete this debt? This cannot be undone."
                                    hx-target="#debts-list-container"
                                    hx-swap="innerHTML"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>