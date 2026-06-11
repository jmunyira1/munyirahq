<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
    Debts
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-primary"
                    hx-get="<?= url_to('debt.form') ?>"
                    hx-target="#debt-modal-body"
                    hx-swap="innerHTML"
                    data-bs-toggle="modal"
                    data-bs-target="#debtModal">
                <i class="bi bi-plus-lg me-1"></i> New Debt
            </button>
        </div>

        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="show-settled">
            <label class="form-check-label small text-muted" for="show-settled">
                Show paid debts
            </label>
        </div>
    </div>

    <div id="debts-list-container"
         hx-get="<?= url_to('debts.list') ?>"
         hx-trigger="load, refreshDebtList from:body">
    </div>

    <div class="modal fade" id="debtModal" tabindex="-1"
         aria-labelledby="debt-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold" id="debt-modal-title">Debt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="debt-modal-body">
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
            const modalEl   = document.getElementById('debtModal');
            const modalBody = document.getElementById('debt-modal-body');
            const titleEl   = document.getElementById('debt-modal-title');
            const container = document.getElementById('debts-list-container');

            const cleanPlaceholder = modalBody.innerHTML;
            const cleanTitle       = titleEl.textContent;

            modalEl.addEventListener('hidden.bs.modal', function () {
                modalBody.innerHTML = cleanPlaceholder;
                titleEl.textContent = cleanTitle;
            });

            document.addEventListener('debtFormSuccess', function () {
                bootstrap.Modal.getInstance(modalEl)?.hide();
                htmx.trigger('body', 'refreshDebtList');
            });

            // ── Settled toggle ────────────────────────────────────────────────────────
            document.getElementById('show-settled').addEventListener('change', function () {
                const url = '<?= url_to('debts.list') ?>' + (this.checked ? '?settled=1' : '');
                container.setAttribute('hx-get', url);
                htmx.process(container);
                htmx.trigger(container, 'load');
            });
        });

        document.getElementById('debt-modal-body').addEventListener('htmx:afterRequest', function (e) {
            const token = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
            if (token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);
                const hidden = e.detail.elt.querySelector('input[type="hidden"][name*="csrf"]');
                if (hidden) hidden.value = token;
            }
            if (e.detail.xhr.status === 200) {
                document.dispatchEvent(new CustomEvent('debtFormSuccess'));
            }
        });
    </script>
<?= $this->endSection() ?>