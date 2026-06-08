<?php

use App\Models\Account as AccountModel;

?>

<div class="table-responsive bg-white shadow-sm rounded">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-uppercase fs-7 text-muted">
        <tr>
            <th class="ps-4">Account</th>
            <th class="text-end">Balance</th>
            <th class="text-end pe-4">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($accounts)): ?>
            <tr>
                <td colspan="5" class="text-center text-muted py-5">
                    <i class="bi bi-wallet2 fs-2 d-block mb-2 opacity-25"></i>
                    <span>No accounts yet. Add one to get started.</span>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($accounts as $account): ?>
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center"
                                 style="width:38px;height:38px;flex-shrink:0;">
                                <i class="bi <?= match($account['account_type']) {
                                    'checking' => 'bi-bank',
                                    'savings'  => 'bi-piggy-bank',
                                    'cash'     => 'bi-cash',
                                    'credit'   => 'bi-credit-card',
                                    default    => 'bi-wallet2',
                                } ?> text-secondary"></i>
                            </div>
                            <span class="fw-semibold text-dark">
                            <?= esc($account['account_name']) ?>
                        </span>
                        </div>
                    </td>



                    <td class="text-end fw-bold <?= (float)$account['current_balance'] < 0 ? 'text-danger' : 'text-success' ?>">
                        <?= number_format((float)$account['current_balance'], 2) ?>
                    </td>

                    <td class="text-end pe-4">
                        <div class="d-inline-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary"
                                    hx-get="<?= url_to('account.edit', $account['id']) ?>"
                                    hx-target="#account-modal-body"
                                    hx-swap="innerHTML"
                                    data-bs-toggle="modal"
                                    data-bs-target="#accountModal"
                                    title="Edit Account">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                    hx-post="<?= base_url('account/destroy/' . $account['id']) ?>"
                                    hx-confirm="Delete '<?= esc($account['account_name']) ?>'? This cannot be undone."
                                    hx-target="#accounts-list-container"
                                    hx-swap="innerHTML"
                                    title="Delete Account">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>

        <?php if (!empty($accounts)): ?>
            <tfoot class="table-light border-top">
            <tr>
                <td colspan="2" class="ps-4 text-muted small fw-semibold text-uppercase">
                    Total across all accounts
                </td>
                <td class="text-end fw-bold">
                    <?php
                    $total = array_sum(array_column($accounts, 'current_balance'));
                    ?>
                    <span class="<?= $total < 0 ? 'text-danger' : 'text-success' ?>">
                        <?= number_format($total, 2) ?>
                    </span>
                </td>
                <td></td>
            </tr>
            </tfoot>
        <?php endif; ?>

    </table>
</div>