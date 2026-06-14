<?php /* $items, $projectId, $isActive */ ?>

<?php if (empty($items)): ?>
    <div class="p-3 text-muted small">No delivery items yet.</div>
<?php else: ?>
    <table class="table table-sm align-middle mb-0 small">
        <thead class="table-light text-uppercase text-muted" style="font-size:0.65rem;">
        <tr>
            <th class="ps-3">Item</th>
            <th class="text-end">Qty</th>
            <th class="text-end">Unit Price</th>
            <th class="text-end pe-3">Total</th>
            <?php if ($isActive): ?><th class="pe-3 text-end">Actions</th><?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr id="ditem-row-<?= $item['id'] ?>">
                <td class="ps-3 fw-semibold"><?= esc($item['name']) ?></td>
                <td class="text-end"><?= $item['quantity'] ?></td>
                <td class="text-end text-muted"><?= number_format((float)$item['unit_price'], 2) ?></td>
                <td class="text-end fw-semibold pe-3"><?= number_format((float)$item['total_price'], 2) ?></td>
                <?php if ($isActive): ?>
                    <td class="text-end pe-3">
                        <div class="d-inline-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary"
                                    onclick="toggleEdit('ditem-edit-<?= $item['id'] ?>', 'ditem-row-<?= $item['id'] ?>')"
                                    title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                    hx-post="<?= base_url('project/delivery-item/destroy/' . $item['id']) ?>"
                                    hx-confirm="Delete this item?"
                                    hx-target="this" hx-swap="none"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                <?php endif; ?>
            </tr>
            <?php if ($isActive): ?>
                <tr id="ditem-edit-<?= $item['id'] ?>" style="display:none;" class="bg-light">
                    <td colspan="5" class="ps-3 pe-3 py-2">
                        <form hx-post="<?= base_url('project/delivery-item/update/' . $item['id']) ?>"
                              hx-target="this" hx-swap="none"
                              hx-encoding="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label small mb-1">Item Name</label>
                                    <input type="text" class="form-control form-control-sm" name="name"
                                           value="<?= esc($item['name']) ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Qty</label>
                                    <input type="number" class="form-control form-control-sm" name="quantity"
                                           value="<?= $item['quantity'] ?>" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Unit Price</label>
                                    <input type="number" class="form-control form-control-sm" name="unit_price"
                                           value="<?= $item['unit_price'] ?>" step="0.01" min="0.01" required>
                                </div>
                                <div class="col-md-2 d-flex gap-1">
                                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                    <button type="button" class="btn btn-sm btn-light border"
                                            onclick="toggleEdit('ditem-edit-<?= $item['id'] ?>', 'ditem-row-<?= $item['id'] ?>')">
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
            <td class="text-end fw-bold pe-3">
                <?= number_format(array_sum(array_column($items, 'total_price')), 2) ?>
            </td>
            <?php if ($isActive): ?><td></td><?php endif; ?>
        </tr>
        </tfoot>
    </table>
<?php endif; ?>