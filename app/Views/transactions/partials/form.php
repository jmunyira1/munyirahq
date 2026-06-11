<?php
/**
 * Adaptive transaction form.
 *
 * $type          — active type: income | expense | transfer | debt_payment
 * $accounts      — all Account rows
 * $subcategories — all subcategory rows (with account_name, parent_name joined)
 * $debts         — active (unpaid) Debt rows
 * $preDebtId     — pre-selected debt id (from Debts page "Pay" button)
 * $preAccountId  — pre-selected source account id (from Accounts page "Transfer" button)
 * $preDebt       — the debt row if $preDebtId is set
 * $preAccount    — the account row if $preAccountId is set
 */
?>

<div hx-swap-oob="innerHTML:#transaction-modal-title">
    <?= match($type) {
        'income'       => 'Record Income',
        'expense'      => 'Record Expense',
        'transfer'     => 'Transfer Between Accounts',
        'debt_payment' => 'Record Debt Payment',
        default        => 'New Transaction',
    } ?>
</div>

<form id="transaction-form"
      hx-post="<?= url_to('transaction.store') ?>"
      hx-encoding="multipart/form-data"
      hx-target="this"
      hx-swap="none">

    <?= csrf_field() ?>

    <div class="row g-3">

        <div class="col-12">
            <div class="btn-group w-100" role="group">
                <?php
                $types = [
                        'expense'      => ['label' => 'Expense',      'icon' => 'arrow-up-circle',   'cls' => 'btn-danger'],
                        'income'       => ['label' => 'Income',       'icon' => 'arrow-down-circle', 'cls' => 'btn-success'],
                        'debt_payment' => ['label' => 'Debt Payment', 'icon' => 'credit-card',       'cls' => 'btn-warning'],
                        'transfer'     => ['label' => 'Transfer',     'icon' => 'arrow-left-right',  'cls' => 'btn-secondary'],
                ];
                foreach ($types as $tKey => $tMeta):
                    $active = $type === $tKey;
                    ?>
                    <button type="button"
                            class="btn btn-sm <?= $active ? $tMeta['cls'] : 'btn-outline-secondary' ?>"
                            data-type="<?= $tKey ?>">
                        <i class="bi bi-<?= $tMeta['icon'] ?> me-1"></i>
                        <?= $tMeta['label'] ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <input type="hidden" name="transaction_type" id="f-type" value="<?= esc($type) ?>">

        <div class="tx-section col-12 row g-3" id="section-income"
             style="<?= $type !== 'income' ? 'display:none;' : '' ?>">

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Account</label>
                <select class="form-select form-select-sm" name="i_account_id" id="f-income-account">
                    <option value="">— Select account —</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>"
                                <?= (isset($preAccountId) && (int)$preAccountId === (int)$acc['id']) ? 'selected' : '' ?>>
                            <?= esc($acc['account_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Amount</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">KES</span>
                    <input type="number" class="form-control" name="i_amount" id="f-income-amount" min="0.01" step="0.01" placeholder="0.00">
                </div>
            </div>
        </div>

        <div class="tx-section col-12 row g-3" id="section-expense"
             style="<?= $type !== 'expense' ? 'display:none;' : '' ?>">

            <div class="col-12">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Category</label>
                <select class="form-select form-select-sm" name="category_id" id="f-expense-category">
                    <option value="">— Select subcategory —</option>
                    <?php
                    $currentParent = null;
                    foreach ($subcategories as $sub):
                        if ($sub['parent_name'] !== $currentParent):
                            if ($currentParent !== null) echo '</optgroup>';
                            echo '<optgroup label="' . esc($sub['parent_name']) . '">';
                            $currentParent = $sub['parent_name'];
                        endif;
                        ?>
                        <option value="<?= $sub['id'] ?>"
                                data-account="<?= esc($sub['account_name']) ?>">
                            <?= esc($sub['name']) ?>
                        </option>
                    <?php endforeach; ?>
                    <?php if ($currentParent !== null) echo '</optgroup>'; ?>
                </select>
                <div class="form-text" id="expense-account-hint">
                    Select a category to see which account will be debited.
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Amount</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">KES</span>
                    <input type="number" class="form-control" name="e_amount" id="f-expense-amount" min="0.01" step="0.01" placeholder="0.00">
                </div>
            </div>
        </div>

        <div class="tx-section col-12 row g-3" id="section-debt_payment"
             style="<?= $type !== 'debt_payment' ? 'display:none;' : '' ?>">

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Debt</label>
                <?php if (isset($preDebt)): ?>
                    <div class="form-control form-control-sm bg-light text-muted">
                        <?= esc($preDebt['party_name']) ?>
                        — KES <?= number_format((float)$preDebt['current_balance'], 2) ?> remaining
                    </div>
                    <input type="hidden" name="debt_id" value="<?= $preDebt['id'] ?>">
                <?php else: ?>
                    <select class="form-select form-select-sm" name="debt_id" id="f-debt-select">
                        <option value="">— Select debt —</option>
                        <?php foreach ($debts as $debt): ?>
                            <option value="<?= $debt['id'] ?>"
                                    data-balance="<?= $debt['current_balance'] ?>"
                                    <?= (isset($preDebtId) && (int)$preDebtId === (int)$debt['id']) ? 'selected' : '' ?>>
                                <?= esc($debt['party_name']) ?>
                                (KES <?= number_format((float)$debt['current_balance'], 2) ?> remaining)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Pay From Account</label>
                <select class="form-select form-select-sm" name="d_account_id" id="f-debt-account">
                    <option value="">— Select account —</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>">
                            <?= esc($acc['account_name']) ?>
                           <?= number_format((float)$acc['current_balance'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Amount</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">KES</span>
                    <input type="number" class="form-control" name="d_amount" id="f-debt-amount" min="0.01" step="0.01" placeholder="0.00">
                </div>
            </div>
        </div>

        <div class="tx-section col-12 row g-3" id="section-transfer"
             style="<?= $type !== 'transfer' ? 'display:none;' : '' ?>">

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">From Account</label>
                <select class="form-select form-select-sm" name="t_account_id" id="f-transfer-from">
                    <option value="">— Select account —</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>"
                                <?= (isset($preAccountId) && (int)$preAccountId === (int)$acc['id']) ? 'selected' : '' ?>>
                            <?= esc($acc['account_name']) ?>
                            (KES <?= number_format((float)$acc['current_balance'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">To Account</label>
                <select class="form-select form-select-sm" name="transfer_to_account_id" id="f-transfer-to">
                    <option value="">— Select account —</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['id'] ?>">
                            <?= esc($acc['account_name']) ?>
                            (KES <?= number_format((float)$acc['current_balance'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Amount</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">KES</span>
                    <input type="number" class="form-control" name="t_amount" id="f-transfer-amount" min="0.01" step="0.01" placeholder="0.00">
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Date & Time</label>
            <input type="datetime-local"
                   class="form-control form-control-sm"
                   name="transaction_date"
                   value="<?= date('Y-m-d\TH:i') ?>">
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                Description <span class="text-muted fw-normal">(optional)</span>
            </label>
            <input type="text" class="form-control form-control-sm"
                   name="description" placeholder="e.g. Groceries at Naivas" maxlength="255">
        </div>

        <div class="col-12" id="form-error" style="display:none;">
            <div class="alert alert-danger py-2 small mb-0">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="form-error-msg"></span>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
            <button type="button" class="btn btn-sm btn-light border"
                    data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-sm btn-primary" hx-disabled-elt="this">
                <span class="htmx-indicator spinner-border spinner-border-sm me-1" role="status"></span>
                <i class="bi bi-check-lg me-1"></i>
                <span id="f-submit-label">Record Transaction</span>
            </button>
        </div>

    </div>
</form>

<script>
    (function () {
        const form      = document.getElementById('transaction-form');
        const errorDiv  = document.getElementById('form-error');
        const errorMsg  = document.getElementById('form-error-msg');
        const typeField = document.getElementById('f-type');
        const submitLbl = document.getElementById('f-submit-label');

        const labels = {
            income:       'Record Income',
            expense:      'Record Expense',
            debt_payment: 'Record Payment',
            transfer:     'Transfer',
        };

        // ── Type tab switching ────────────────────────────────────────────────────
        document.querySelectorAll('[data-type]').forEach(btn => {
            btn.addEventListener('click', function () {
                const t = this.dataset.type;

                // Update hidden field
                typeField.value = t;

                // Toggle section visibility
                document.querySelectorAll('.tx-section').forEach(s => s.style.display = 'none');
                document.getElementById('section-' + t).style.display = '';

                // Update button styles
                const styleMap = {
                    expense:      'btn-danger',
                    income:       'btn-success',
                    debt_payment: 'btn-warning',
                    transfer:     'btn-secondary',
                };
                document.querySelectorAll('[data-type]').forEach(b => {
                    b.className = 'btn btn-sm btn-outline-secondary';
                    if (b.dataset.type === t) b.classList.add(styleMap[t]);
                    else b.classList.remove(styleMap[t]);
                });

                submitLbl.textContent = labels[t] ?? 'Record Transaction';
                errorDiv.style.display = 'none';
            });
        });

        // ── Expense: show account hint when category changes ─────────────────────
        const categorySel = document.getElementById('f-expense-category');
        const accountHint = document.getElementById('expense-account-hint');
        if (categorySel) {
            categorySel.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const acct = opt.dataset.account;
                accountHint.innerHTML = acct
                    ? '<i class="bi bi-wallet2 me-1"></i>Will debit: <strong>' + acct + '</strong>'
                    : 'Select a category to see which account will be debited.';
            });
            // Trigger on load if a category is pre-selected
            if (categorySel.value) categorySel.dispatchEvent(new Event('change'));
        }

        // ── Error handling ────────────────────────────────────────────────────────
        form.addEventListener('htmx:beforeRequest', () => errorDiv.style.display = 'none');

        form.addEventListener('htmx:responseError', function (e) {
            if (e.detail.xhr.status === 422) {
                try {
                    const body = JSON.parse(e.detail.xhr.responseText);
                    errorMsg.textContent = body.error ?? 'Please correct the errors and try again.';
                    errorDiv.style.display = '';
                } catch (_) {}
            }
        });
    })();
</script>