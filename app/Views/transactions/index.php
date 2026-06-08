<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
    Transactions
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

        {{-- Type shortcut buttons --}}
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-sm btn-success"
                    hx-get="<?= url_to('transaction.form') ?>?type=income"
                    hx-target="#transaction-modal-body"
                    hx-swap="innerHTML"
                    data-bs-toggle="modal"
                    data-bs-target="#transactionModal">
                <i class="bi bi-arrow-down-circle me-1"></i> Income
            </button>
            <button class="btn btn-sm btn-danger"
                    hx-get="<?= url_to('transaction.form') ?>?type=expense"
                    hx-target="#transaction-modal-body"
                    hx-swap="innerHTML"
                    data-bs-toggle="modal"
                    data-bs-target="#transactionModal">
                <i class="bi bi-arrow-up-circle me-1"></i> Expense
            </button>
            <button class="btn btn-sm btn-warning"
                    hx-get="<?= url_to('transaction.form') ?>?type=debt_payment"
                    hx-target="#transaction-modal-body"
                    hx-swap="innerHTML"
                    data-bs-toggle="modal"
                    data-bs-target="#transactionModal">
                <i class="bi bi-credit-card me-1"></i> Debt Payment
            </button>
            <button class="btn btn-sm btn-secondary"
                    hx-get="<?= url_to('transaction.form') ?>?type=transfer"
                    hx-target="#transaction-modal-body"
                    hx-swap="innerHTML"
                    data-bs-toggle="modal"
                    data-bs-target="#transactionModal">
                <i class="bi bi-arrow-left-right me-1"></i> Transfer
            </button>
        </div>

        {{-- Date / filter controls --}}
        <div class="d-flex gap-2 align-items-center flex-wrap" id="filter-bar">
            <input type="month"
                   class="form-control form-control-sm"
                   id="filter-month"
                   value="<?= date('Y-m') ?>"
                   style="width:160px;">
            <select class="form-select form-select-sm" id="filter-type" style="width:160px;">
                <option value="">All types</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
                <option value="debt_payment">Debt Payment</option>
                <option value="transfer">Transfer</option>
            </select>
            <button class="btn btn-sm btn-outline-secondary" id="filter-apply">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <button class="btn btn-sm btn-link text-muted p-0" id="filter-reset">Reset</button>
        </div>
    </div>

    {{-- List container — loaded on page load and refreshed after CUD --}}
    <div id="transactions-list-container"
         hx-get="<?= url_to('transactions.list') ?>?month=<?= date('Y-m') ?>"
         hx-trigger="load, refreshTransactionList from:body">
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="transactionModal" tabindex="-1"
         aria-labelledby="transaction-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold" id="transaction-modal-title">Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="transaction-modal-body">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm me-2"></div>
                        Loading…
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl   = document.getElementById('transactionModal');
            const modalBody = document.getElementById('transaction-modal-body');
            const titleEl   = document.getElementById('transaction-modal-title');

            const cleanPlaceholder = modalBody.innerHTML;
            const cleanTitle       = titleEl.textContent;

            modalEl.addEventListener('hidden.bs.modal', function () {
                modalBody.innerHTML = cleanPlaceholder;
                titleEl.textContent = cleanTitle;
            });

            document.addEventListener('transactionFormSuccess', function () {
                bootstrap.Modal.getInstance(modalEl)?.hide();
                htmx.trigger('body', 'refreshTransactionList');
            });

            // ── Filter bar ────────────────────────────────────────────────────────────
            const listContainer = document.getElementById('transactions-list-container');
            const monthInput    = document.getElementById('filter-month');
            const typeSelect    = document.getElementById('filter-type');

            function buildUrl() {
                const params = new URLSearchParams();
                if (monthInput.value) params.set('month', monthInput.value);
                if (typeSelect.value) params.set('type',  typeSelect.value);
                return '<?= url_to('transactions.list') ?>?' + params.toString();
            }

            document.getElementById('filter-apply').addEventListener('click', function () {
                listContainer.setAttribute('hx-get', buildUrl());
                htmx.process(listContainer); // re-register with new URL
                htmx.trigger(listContainer, 'load');
            });

            document.getElementById('filter-reset').addEventListener('click', function () {
                monthInput.value = '<?= date('Y-m') ?>';
                typeSelect.value = '';
                listContainer.setAttribute('hx-get', buildUrl());
                htmx.process(listContainer);
                htmx.trigger(listContainer, 'load');
            });
        });

        // CSRF refresh + success dispatch
        document.getElementById('transaction-modal-body').addEventListener('htmx:afterRequest', function (e) {
            const token = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
            if (token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);
                const hidden = e.detail.elt.querySelector('input[type="hidden"][name*="csrf"]');
                if (hidden) hidden.value = token;
            }
            if (e.detail.xhr.status === 200) {
                document.dispatchEvent(new CustomEvent('transactionFormSuccess'));
            }
        });
    </script>
<?= $this->endSection() ?>