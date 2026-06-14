<?php /* $costs, $projectId, $isActive */ ?>

<?php if (empty($costs)): ?>
    <div class="p-3 text-muted small">No costs recorded yet.</div>
<?php else: ?>
    <table class="table table-sm align-middle mb-0 small">
        <thead class="table-light text-uppercase text-muted" style="font-size:0.65rem;">
        <tr>
            <th class="ps-3">Title</th>
            <th class="text-end">Qty</th>
            <th class="text-end">Unit Price</th>
            <th class="text-end">Total</th>
            <th>Date</th>
            <th>Notes</th>
            <?php if ($isActive): ?><th class="pe-3 text-end">Actions</th><?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($costs as $cost): ?>
            <tr id="cost-row-<?= $cost['id'] ?>">
                <td class="ps-3 fw-semibold"><?= esc($cost['title']) ?></td>
                <td class="text-end"><?= $cost['quantity'] ?></td>
                <td class="text-end text-muted"><?= number_format((float)$cost['unit_price'], 2) ?></td>
                <td class="text-end text-danger fw-semibold"><?= number_format((float)$cost['amount'], 2) ?></td>
                <td class="text-muted"><?= date('d M Y', strtotime($cost['incurred_on'])) ?></td>
                <td class="text-muted"><?= esc($cost['notes'] ?? '—') ?></td>
                <?php if ($isActive): ?>
                    <td class="text-end pe-3">
                        <div class="d-inline-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary"
                                    onclick="toggleEdit('cost-edit-<?= $cost['id'] ?>', 'cost-row-<?= $cost['id'] ?>')"
                                    title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                    hx-post="<?= base_url('project/cost/destroy/' . $cost['id']) ?>"
                                    hx-confirm="Delete this cost?"
                                    hx-target="this" hx-swap="none"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                <?php endif; ?>
            </tr>
            <?php if ($isActive): ?>
                <tr id="cost-edit-<?= $cost['id'] ?>" style="display:none;" class="bg-light">
                    <td colspan="7" class="ps-3 pe-3 py-2">
                        <form hx-post="<?= base_url('project/cost/update/' . $cost['id']) ?>"
                              hx-target="this" hx-swap="none"
                              hx-encoding="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Title</label>
                                    <input type="text" class="form-control form-control-sm" name="title"
                                           value="<?= esc($cost['title']) ?>" required>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small mb-1">Qty</label>
                                    <input type="number" class="form-control form-control-sm" name="quantity"
                                           value="<?= $cost['quantity'] ?>" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Unit Price</label>
                                    <input type="number" class="form-control form-control-sm" name="unit_price"
                                           value="<?= $cost['unit_price'] ?>" step="0.01" min="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Date</label>
                                    <input type="date" class="form-control form-control-sm" name="incurred_on"
                                           value="<?= $cost['incurred_on'] ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Notes</label>
                                    <input type="text" class="form-control form-control-sm" name="notes"
                                           value="<?= esc($cost['notes'] ?? '') ?>">
                                </div>
                                <div class="col-md-2 d-flex gap-1">
                                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                    <button type="button" class="btn btn-sm btn-light border"
                                            onclick="toggleEdit('cost-edit-<?= $cost['id'] ?>', 'cost-row-<?= $cost['id'] ?>')">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light">
        <tr>
            <td colspan="3" class="ps-3 text-muted small fw-semibold text-uppercase">Total</td>
            <td class="text-end fw-bold text-danger">
                <?= number_format(array_sum(array_column($costs, 'amount')), 2) ?>
            </td>
            <td colspan="<?= $isActive ? 3 : 2 ?>"></td>
        </tr>
        </tfoot>
    </table>
<?php endif; ?>