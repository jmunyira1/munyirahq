<?= $this->extend('layout/layout') ?>

<?= $this->section('title') ?>
    Categories
<?= $this->endSection() ?>

<?= $this->section('content') ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <button class="btn btn-sm btn-primary"
                hx-get="<?= url_to('category.form') ?>"
                hx-target="#category-modal-body"
                hx-swap="innerHTML"
                data-bs-toggle="modal"
                data-bs-target="#categoryModal">
            <i class="bi bi-plus-lg me-1"></i> New Category
        </button>
    </div>

    <div id="categories-list-container"
         hx-get="<?= url_to('categories.list') ?>"
         hx-trigger="load, refreshCategoryList from:body">
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1"
         aria-labelledby="category-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-semibold" id="category-modal-title">Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="category-modal-body">
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
            const modalEl   = document.getElementById('categoryModal');
            const modalBody = document.getElementById('category-modal-body');
            const titleEl   = document.getElementById('category-modal-title');

            const cleanPlaceholder = modalBody.innerHTML;
            const cleanTitle       = titleEl.textContent;

            modalEl.addEventListener('hidden.bs.modal', function () {
                modalBody.innerHTML = cleanPlaceholder;
                titleEl.textContent = cleanTitle;
            });

            document.addEventListener('categoryFormSuccess', function () {
                bootstrap.Modal.getInstance(modalEl)?.hide();
                htmx.trigger('body', 'refreshCategoryList');
            });
        });

        document.getElementById('category-modal-body').addEventListener('htmx:afterRequest', function (e) {
            const token = e.detail.xhr.getResponseHeader('X-CSRF-TOKEN');
            if (token) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', token);
                const hiddenInput = e.detail.elt.querySelector('input[type="hidden"][name*="csrf"]');
                if (hiddenInput) hiddenInput.value = token;
            }

            if (e.detail.xhr.status === 200) {
                document.dispatchEvent(new CustomEvent('categoryFormSuccess'));
            }
        });
    </script>
<?= $this->endSection() ?>