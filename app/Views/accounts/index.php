<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
    Accounts
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <button class="btn btn-sm btn-primary"
                hx-get="<?= url_to('account.form') ?>"
                hx-target="#account-modal-body"
                hx-swap="innerHTML"
                data-bs-toggle="modal"
                data-bs-target="#accountModal">
            <i class="bi bi-plus-lg me-1"></i> New Account
        </button>
    </div>

    <div id="accounts-list-container"
         hx-get="<?= url_to('accounts.list') ?>"
         hx-trigger="load, refreshAccountList from:body">
    </div>

    <div class="modal fade" id="accountModal" tabindex="-1"
         aria-labelledby="account-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold" id="account-modal-title">Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="account-modal-body">
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
            const modalEl   = document.getElementById('accountModal');
            const modalBody = document.getElementById('account-modal-body');
            const titleEl   = document.getElementById('account-modal-title');

            // Capture pristine placeholder so we can restore it on close
            const cleanPlaceholder = modalBody.innerHTML;
            const cleanTitle       = titleEl.textContent;

            modalEl.addEventListener('hidden.bs.modal', function () {
                modalBody.innerHTML    = cleanPlaceholder;
                titleEl.textContent    = cleanTitle;
            });

            document.addEventListener('accountFormSuccess', function () {
                bootstrap.Modal.getInstance(modalEl)?.hide();
                htmx.trigger('body', 'refreshAccountList');
            });
        });

        // Listen for HTMX responses inside the modal body
        document.getElementById('account-modal-body').addEventListener('htmx:afterRequest', function (e) {
            // Refresh CSRF token
            const token = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
            if (token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);
                const hiddenInput = e.detail.elt.querySelector('input[type="hidden"][name*="csrf"]');
                if (hiddenInput) hiddenInput.value = token;
            }

            if (e.detail.xhr.status === 200) {
                document.dispatchEvent(new CustomEvent('accountFormSuccess'));
            }
        });
    </script>
<?= $this->endSection() ?>