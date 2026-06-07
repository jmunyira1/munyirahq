<?php
$isEdit = isset($party) && !empty($party);

$action = $isEdit
    ? url_to('party.update', $party['id'])
    : url_to('party.store');
?>
<div hx-swap-oob="innerHTML:#party-modal-title">
    <?= $isEdit ? 'Edit Party' : 'New Party' ?>
</div>

<form id="party-form"
      hx-post="<?= $action ?>"
      hx-encoding="multipart/form-data"
      hx-target="this"
      hx-swap="none"
      hx-validate="true">

    <?= csrf_field() ?>
    <div class="flex-grow-1">
        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                       for="f-title">Title</label>
                <input type="text" class="form-control form-control-sm"
                       name="title" id="f-title"
                       value="<?= $isEdit ? esc($party['title']) : '' ?>"
                       placeholder="Mr, Dr, Eng…" maxlength="100">
            </div>

            <div class="col-md-8">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                       for="f-name">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm"
                       name="name" id="f-name"
                       value="<?= $isEdit ? esc($party['name']) : '' ?>"
                       placeholder="Enter full name" maxlength="100" required>
                <div class="invalid-feedback">Full name is required.</div>
            </div>

            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1"
                       for="f-isPerson">Party Type</label>
                <select class="form-select form-select-sm" name="isPerson" id="f-isPerson">
                    <option value="1" <?= ($isEdit && $party['is_person'] == 1) ? 'selected' : '' ?>>Person</option>
                    <option value="0" <?= ($isEdit && $party['is_person'] == 0) ? 'selected' : '' ?>>Company</option>
                </select>
            </div>

            <div class="col-md-6" id="f-gender-wrap">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Gender</label>
                <select class="form-select form-select-sm" name="gender">
                    <option value="" <?= ($isEdit && $party['gender'] === null) ? 'selected' : '' ?>>— Not specified —</option>
                    <option value="0" <?= ($isEdit && isset($party['gender']) && $party['gender'] == 0) ? 'selected' : '' ?>>Male</option>
                    <option value="1" <?= ($isEdit && isset($party['gender']) && $party['gender'] == 1) ? 'selected' : '' ?>>Female</option>
                </select>
            </div>

            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold text-muted text-uppercase mb-0">
                        Email Addresses
                    </label>
                    <button type="button" id="add-email-btn"
                            class="btn btn-link btn-sm p-0 text-decoration-none">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                </div>
                <div id="email-fields-container">
                    <?php
                    $emails = ($isEdit && !empty($party['emails'])) ? $party['emails'] : [''];
                    foreach ($emails as $i => $email):
                        ?>
                        <div class="input-group input-group-sm mb-2 contact-row">
                            <span class="input-group-text bg-body-secondary border-end-0">
                                <i class="bi bi-envelope text-muted"></i>
                            </span>
                            <input type="email" class="form-control border-start-0"
                                   name="email[]"
                                   value="<?= esc($email) ?>"
                                   placeholder="name@example.com">
                            <button type="button"
                                    class="btn btn-outline-danger remove-row-btn <?= $i === 0 ? 'd-none' : '' ?>">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold text-muted text-uppercase mb-0">
                        Phone Numbers
                    </label>
                    <button type="button" id="add-phone-btn"
                            class="btn btn-link btn-sm p-0 text-decoration-none">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                </div>
                <div id="phone-fields-container">
                    <?php
                    $phones = ($isEdit && !empty($party['phones'])) ? $party['phones'] : [''];
                    foreach ($phones as $i => $phone):
                        ?>
                        <div class="input-group input-group-sm mb-2 contact-row">
                            <span class="input-group-text bg-body-secondary border-end-0">
                                <i class="bi bi-telephone text-muted"></i>
                            </span>
                            <input type="tel" class="form-control border-start-0"
                                   name="phone[]"
                                   value="<?= esc($phone) ?>"
                                   placeholder="+254 700 000000">
                            <button type="button"
                                    class="btn btn-outline-danger remove-row-btn <?= $i === 0 ? 'd-none' : '' ?>">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="col-12" id="form-error" style="display:none;">
                <div class="alert alert-danger py-2 small mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span id="form-error-msg"></span>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
                <button type="button" class="btn btn-sm btn-light border"
                        data-bs-dismiss="modal">Cancel
                </button>
                <button type="submit" id="f-submit" class="btn btn-sm btn-primary"
                        hx-disabled-elt="this">
                        <span class="htmx-indicator spinner-border spinner-border-sm me-1"
                              role="status"></span>
                    <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?> submit-icon"></i>
                    <span class="submit-label"><?= $isEdit ? 'Save Changes' : 'Create Party' ?></span>
                </button>
            </div>

        </div>
    </div>
</form>

<script>
    const isPersonSel = document.getElementById('f-isPerson');
    const genderWrap = document.getElementById('f-gender-wrap');
    const toggleGender = () => {
        genderWrap.style.display = isPersonSel.value === '1' ? '' : 'none';
    };
    isPersonSel.addEventListener('change', toggleGender);
    toggleGender();

    function setupRows(containerId, addBtnId, inputType, placeholder, iconClass) {
        const container = document.getElementById(containerId);
        const addBtn = document.getElementById(addBtnId);

        container.querySelectorAll('.remove-row-btn').forEach(wireRemove);

        addBtn.addEventListener('click', () => {
            const newRow = document.createElement('div');
            newRow.className = "input-group input-group-sm mb-2 contact-row";
            newRow.innerHTML = `
                <span class="input-group-text bg-body-secondary border-end-0">
                    <i class="bi ${iconClass} text-muted"></i>
                </span>
                <input type="${inputType}" class="form-control border-start-0"
                       name="${inputType}[]" placeholder="${placeholder}">
                <button type="button" class="btn btn-outline-danger remove-row-btn">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;

            const rmBtn = newRow.querySelector('.remove-row-btn');
            wireRemove(rmBtn);
            container.appendChild(newRow);
            newRow.querySelector('input').focus();
        });

        function wireRemove(btn) {
            btn.addEventListener('click', () => btn.closest('.contact-row').remove());
        }
    }

    setupRows('email-fields-container', 'add-email-btn', 'email', 'name@example.com', 'bi-envelope');
    setupRows('phone-fields-container', 'add-phone-btn', 'tel', '+254 700 000000', 'bi-telephone');

    const form = document.getElementById('party-form');
    const errorDiv = document.getElementById('form-error');
    const errorMsg = document.getElementById('form-error-msg');

    form.addEventListener('htmx:beforeRequest', function (e) {
        errorDiv.style.display = 'none';
    });

    form.addEventListener('htmx:responseError', function (e) {
        if (e.detail.xhr.status === 422) {
            try {
                const body = JSON.parse(e.detail.xhr.responseText);
                errorMsg.textContent = body.error ?? 'Please correct the errors and try again.';
                errorDiv.style.display = '';
            } catch (_) {}
        }
    });

</script>