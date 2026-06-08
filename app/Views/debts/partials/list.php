<?php

function _partyInitials(array $debt): string
{
    $parts = explode(' ', trim($debt['name'] ?? ''));
    $i = strtoupper($parts[0][0] ?? '?');
    if (count($parts) >= 2) $i .= strtoupper($parts[count($parts) - 1][0]);
    return $i;
}

function genderLabel(array $debt): string
{
    if (!($debt['is_person'] ?? false)) return '—';
    if (($debt['gender'] ?? null) === null || $debt['gender'] === '') return '—';
    return $debt['gender'] == 1 ? 'Female' : 'Male';
}

function genderPrefix(array $debt): string
{
    if (!($debt['is_person'] ?? false) || ($debt['gender'] ?? null) === null || $debt['gender'] === '') return '';
    return $debt['gender'] == 1 ? 'Ms. ' : 'Mr. ';
}

?>

<div class="table-responsive bg-white shadow-sm rounded">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-uppercase fs-7 text-muted">
        <tr>
            <th scope="col" class="ps-4">Party Name</th>
            <th scope="col">Type</th>
            <th scope="col">Title / Info</th>
            <th scope="col" class="text-end">Amount</th>
            <th scope="col" class="text-end pe-4">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($debts)): ?>
            <tr>
                <td colspan="5" class="text-center text-muted py-5">
                    <i class="bi bi-cash-stack fs-2 d-block mb-2 opacity-25"></i>
                    <span>No records found.</span>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($debts as $debt): ?>
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-initials bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center border fw-bold small" style="width: 38px; height: 38px;">
                                <?= _partyInitials($debt) ?>
                            </div>
                            <div>
                                    <span class="fw-semibold d-block text-dark">
                                        <?= esc(genderPrefix($debt) . $debt['name']) ?>
                                    </span>
                                <?php if (!empty($debt['contacts'])): ?>
                                    <span class="text-muted small">
                                            <i class="bi bi-<?= $debt['contacts'][0]['contact_type'] === 'phone' ? 'telephone' : 'envelope' ?> me-1"></i>
                                            <?= esc($debt['contacts'][0]['contact_value']) ?>
                                        </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>

                    <td>
                            <span class="badge <?= $debt['is_person'] ? 'text-bg-info' : 'text-bg-secondary' ?> fw-normal">
                                <?= $debt['is_person'] ? 'Person' : 'Company' ?>
                            </span>
                    </td>

                    <td class="text-muted small">
                        <?= esc($debt['title'] ?? '—') ?>
                    </td>

                    <td class="text-end fw-bold text-danger">
                        $<?= number_format($debt['amount'] ?? 0, 2) ?>
                    </td>

                    <td class="text-end pe-4">
                        <div class="d-inline-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary"
                                    hx-get="<?= url_to('debt.edit', $debt['id']) ?>"
                                    hx-target="#debt-modal-body"
                                    hx-swap="innerHTML"
                                    data-bs-toggle="modal"
                                    data-bs-target="#debtModal"
                                    title="Edit Party">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                    hx-post="<?= base_url('debt/destroy/' . $debt['id']) ?>"
                                    hx-confirm="Delete this debt record? This cannot be undone."
                                    hx-target="#debts-list"
                                    hx-swap="innerHTML"
                                    title="Delete Debt">
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