<?php /* $payments, $projectId, $isActive */ ?>

<?php if (empty($payments)): ?>
    <div class="p-3 text-muted small">No payments recorded yet.</div>
<?php else: ?>
    <table class="table table-sm align-middle mb-0 small">
        <thead class="table-light text-uppercase text-muted" style="font-size:0.65rem;">
        <tr>
            <th class="ps-3">Date</th>
            <th>Method</th>
            <th>Reference</th>
            <th class="text-end">Amount</th>
            <?php if ($isActive): ?><th class="pe-3 text-end">Actions</th><?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $pay): ?>
            <tr id="pay-row-<?= $pay['id'] ?>">
                <td class="ps-3 text-muted"><?= date('d M Y', strtotime($pay['payment_date'])) ?></td>
                <td><?= esc($pay['method'] ?? '—') ?></td>
                <td class="text-muted small"><?= esc($pay['reference'] ?? '—') ?></td>
                <td class="text-end fw-semibold text-success"><?= number_format((float)$pay['amount'], 2) ?></td>
                <?php if ($isActive): ?>
                    <td class="text-end pe-3">
                        <div class="d-inline-flex gap-1">
                            <button class="btn btn-sm btn-outline-secondary"
                                    onclick="toggleEdit('pay-edit-<?= $pay['id'] ?>', 'pay-row-<?= $pay['id'] ?>')"
                                    title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                    hx-post="<?= base_url('project/payment/destroy/' . $pay['id']) ?>"
                                    hx-confirm="Delete this payment?"
                                    hx-target="this" hx-swap="none"
                                    title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                <?php endif; ?>
            </tr>
            <?php if ($isActive): ?>
                <tr id="pay-edit-<?= $pay['id'] ?>" style="display:none;" class="bg-light">
                    <td colspan="5" class="ps-3 pe-3 py-2">
                        <form hx-post="<?= base_url('project/payment/update/' . $pay['id']) ?>"
                              hx-target="this" hx-swap="none"
                              hx-encoding="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Amount</label>
                                    <input type="number" class="form-control form-control-sm" name="amount"
                                           value="<?= $pay['amount'] ?>" step="0.01" min="0.01" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Date</label>
                                    <input type="datetime-local" class="form-control form-control-sm" name="payment_date"
                                           value="<?= date('Y-m-d\TH:i', strtotime($pay['payment_date'])) ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Method</label>
                                    <input type="text" class="form-control form-control-sm" name="method"
                                           value="<?= esc($pay['method'] ?? '') ?>" placeholder="M-Pesa, Bank…">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small mb-1">Reference</label>
                                    <input type="text" class="form-control form-control-sm" name="reference"
                                           value="<?= esc($pay['reference'] ?? '') ?>">
                                </div>
                                <div class="col-md-2 d-flex gap-1">
                                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                    <button type="button" class="btn btn-sm btn-light border"
                                            onclick="toggleEdit('pay-edit-<?= $pay['id'] ?>', 'pay-row-<?= $pay['id'] ?>')">
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
            <td colspan="3" class="ps-3 text-muted small fw-semibold text-uppercase">Total Paid</td>
            <td class="text-end fw-bold text-success">
                <?= number_format(array_sum(array_column($payments, 'amount')), 2) ?>
            </td>
            <?php if ($isActive): ?><td></td><?php endif; ?>
        </tr>
        </tfoot>
    </table>
<?php endif; ?>