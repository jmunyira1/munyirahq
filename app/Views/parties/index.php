<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
    Parties
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h5 class="mb-0">Parties</h5>
            <p class="text-muted mb-0 small">Manage people and companies</p>
        </div>
        <button class="btn btn-sm btn-primary"
                hx-get="<?= url_to('party.form') ?>"
                hx-target="#party-modal-body"
                hx-swap="innerHTML"
                data-bs-toggle="modal"
                data-bs-target="#partyModal">
            <i class="bi bi-plus-lg me-1"></i> New Party
        </button>
    </div>

    <div id="parties-list-container"
         hx-get="<?= url_to('parties.list') ?>"
         hx-trigger="load, refreshPartiesList from:body">
    </div>

    <div class="modal fade" id="partyModal" tabindex="-1"
         aria-labelledby="party-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold" id="party-modal-title">Party</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="party-modal-body">
                    <div class="text-center py-5 text-muted HTML-placeholder">
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
            const modalEl = document.getElementById('partyModal');
            const modalBody = document.getElementById('party-modal-body');
            const bootstrapModal = new bootstrap.Modal(modalEl);

            // Capture the pristine loading state to restore it whenever the modal is hidden
            const cleanPlaceholder = modalBody.innerHTML;

            modalEl.addEventListener('hidden.bs.modal', function () {
                // Reset the modal content to the spinner when closed
                // This prevents old form data or titles from "flashing" next time it's opened
                modalBody.innerHTML = cleanPlaceholder;
                document.getElementById('party-modal-title').textContent = 'Party';
            });

            // ── Close Modal upon Successful Form Submission ──────────────────────
            // If your server controller redirects or sends an empty success response (Status 200),
            // we close the modal and tell the main container to reload its list.
            document.addEventListener('partyFormSuccess', function () {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
                htmx.trigger('body', 'refreshPartiesList');
            });
        });
        // Replace the partyFormSuccess dispatch logic — listen at the modal body level
        const modalBody = document.getElementById('party-modal-body');
        modalBody.addEventListener('htmx:afterRequest', function (e) {
            // CSRF refresh
            const token = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
            if (token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);
                const hiddenCsrfInput = e.detail.elt.querySelector('input[type="hidden"][name*="csrf"]');
                if (hiddenCsrfInput) hiddenCsrfInput.value = token;
            }

            if (e.detail.xhr.status === 200) {
                document.dispatchEvent(new CustomEvent('partyFormSuccess'));
            }
        });
    </script>
<?= $this->endSection() ?>