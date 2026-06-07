<?php

function _partyInitials(array $p): string
{
    $parts = explode(' ', trim($p['name']));
    $i = strtoupper($parts[0][0] ?? '?');
    if (count($parts) >= 2) $i .= strtoupper($parts[count($parts) - 1][0]);
    return $i;
}



function genderLabel(array $p): string
{
    if (!$p['is_person']) return '—';
    if ($p['gender'] === null || $p['gender'] === '') return '—';
    return $p['gender'] == 1 ? 'Female' : 'Male';
}

function genderPrefix(array $p): string
{
    if (!$p['is_person'] || $p['gender'] === null || $p['gender'] === '') return '';
    return $p['gender'] == 1 ? 'Ms. ' : 'Mr. ';
}

?>

<!-- ════════════════════════════════════════════ CARDS VIEW ══════════════ -->
<div class="parties-cards row g-3">

    <?php if (empty($parties)): ?>
        <div class="col-12 text-center text-muted py-5">
            <i class="bi bi-people fs-1 d-block mb-3 opacity-25"></i>
            <p class="mb-0">No parties yet. Click <strong>New Party</strong> to get started.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($parties as $p): ?>
        <div class="col-md-6 col-xxl-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-start gap-3">


                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-w-0">
                                <h6 class="mb-0 text-truncate fw-semibold">
                                    <?= esc(genderPrefix($p) . $p['name']) ?>
                                </h6>
                                <p class="mb-0 text-muted small text-truncate">
                                    <?= esc($p['title'] ?? '') ?>
                                </p>
                            </div>
                            <span class="badge <?= $p['is_person'] ? 'text-bg-info' : 'text-bg-secondary' ?> fw-normal flex-shrink-0">
                                <?= $p['is_person'] ? 'Person' : 'Company' ?>
                            </span>
                        </div>

                        <?php if (!empty($p['contacts'])): ?>
                            <div class="mt-2 d-flex flex-column gap-1">
                                <?php foreach ($p['contacts'] as $c): ?>
                                    <div class="d-flex align-items-center gap-2 small text-muted">
                                        <i class="bi bi-<?= $c['contact_type'] === 'phone' ? 'telephone' : 'envelope' ?> flex-shrink-0"></i>
                                        <span class="text-truncate"><?= esc($c['contact_value']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer bg-transparent border-top d-flex justify-content-end gap-2 py-2">
                    <button class="btn btn-xs btn-outline-secondary"
                            hx-get="<?= url_to('party.edit' , $p['id']) ?>"
                            hx-target="#party-modal-body"
                            hx-swap="innerHTML"
                            data-bs-toggle="modal"
                            data-bs-target="#partyModal">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <button class="btn btn-xs btn-outline-danger"
                            hx-post="<?= base_url('party/destroy/' . $p['id']) ?>"
                            hx-confirm="Delete <?= esc($p['name']) ?>? This cannot be undone."
                            hx-target="#parties-list"
                            hx-swap="innerHTML">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>