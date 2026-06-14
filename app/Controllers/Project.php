<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Project as ProjectModel;
use App\Models\Party   as PartyModel;
use App\Models\Account as AccountModel;

class Project extends BaseController
{    protected $db;

    public function __construct()
    {
        // Initialize the database connection
        $this->db = \Config\Database::connect();
    }
    // ── Pages ─────────────────────────────────────────────────────────────────

    public function index()
    {
        return view('projects/index');
    }

    public function show(int $id)
    {
        $project = (new ProjectModel)->findWithDetails($id);
        if (!$project) {
            return redirect()->to(url_to('projects.index'))->with('error', 'Project not found.');
        }
        return view('projects/show', ['project' => $project]);
    }

    // ── HTMX partials ─────────────────────────────────────────────────────────

    public function list()
    {
        $status   = $this->request->getGet('status') ?: '';
        $projects = (new ProjectModel)->findAllWithFigures($status);
        return view('projects/partials/list', [
            'projects' => $projects,
            'status'   => $status,
        ]);
    }

    public function form(?int $id = null)
    {
        $data = ['parties' => (new PartyModel)->findAll()];

        if ($id !== null) {
            $project = (new ProjectModel)->find($id);
            if (!$project) {
                return $this->response->setStatusCode(404)->setBody('Project not found.');
            }
            $data['project'] = $project;
        }

        return view('projects/partials/form', $data);
    }

    // ── CUD ───────────────────────────────────────────────────────────────────

    public function store()
    {
        $model = new ProjectModel();
        $data  = [
            'party_id'          => $this->request->getPost('party_id'),
            'title'             => $this->request->getPost('title'),
            'description'       => $this->request->getPost('description') ?: null,
            'contracted_amount' => (float) $this->request->getPost('contracted_amount'),
            'due_date'          => $this->request->getPost('due_date') ?: null,
            'status'            => 'active',
        ];

        if (!$model->validate($data)) {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => implode(' ', $model->errors())]);
        }

        $id = $model->insert($data, true);

        return $this->_successResponse('refreshProjectList', 'Project created.');
    }

    public function update(int $id)
    {
        $model   = new ProjectModel();
        $project = $model->find($id);

        if (!$project) {
            return $this->response->setStatusCode(404)->setBody('Project not found.');
        }

        if ($project['status'] === 'completed') {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => 'Completed projects cannot be edited.']);
        }

        $data = [
            'party_id'          => $this->request->getPost('party_id'),
            'title'             => $this->request->getPost('title'),
            'description'       => $this->request->getPost('description') ?: null,
            'contracted_amount' => (float) $this->request->getPost('contracted_amount'),
            'due_date'          => $this->request->getPost('due_date') ?: null,
        ];

        if (!$model->validate($data)) {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => implode(' ', $model->errors())]);
        }

        $model->update($id, $data);

        return $this->_successResponse('refreshProjectList', 'Project updated.');
    }

    public function destroy(int $id)
    {
        $model   = new ProjectModel();
        $project = $model->find($id);

        if (!$project) {
            return $this->response->setStatusCode(404)->setBody('Project not found.');
        }

        if ($project['status'] === 'completed') {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => 'Completed projects cannot be deleted.']);
        }

        $model->delete($id);

        return $this->_successResponse('refreshProjectList', 'Project deleted.');
    }

    // ── Completion ────────────────────────────────────────────────────────────

    /**
     * Shows the complete form — account picker + confirmation.
     */
    public function completeForm(int $id)
    {
        $project = (new ProjectModel)->findWithDetails($id);
        if (!$project) {
            return $this->response->setStatusCode(404)->setBody('Project not found.');
        }

        $accounts = (new AccountModel)->findAll();

        return view('projects/partials/complete_form', [
            'project'  => $project,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Processes completion — triggers auto income transaction.
     */
    public function complete(int $id)
    {
        $accountId = (int) $this->request->getPost('account_id');

        if (!$accountId) {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => 'Select an account to receive the profit.']);
        }

        try {
            (new ProjectModel)->complete($id, $accountId);
        } catch (\RuntimeException $e) {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => $e->getMessage()]);
        }

        return $this->_successResponse(
            'refreshProjectList refreshTransactionList',
            'Project completed. Profit recorded as income.'
        );
    }

    // ── Sub-resource: costs ───────────────────────────────────────────────────

    public function storeCost(int $projectId)
    {
        $data = [
            'project_id'  => $projectId,
            'title'       => $this->request->getPost('title'),
            'quantity'    => (int) $this->request->getPost('quantity') ?: 1,
            'unit_price'  => (float) $this->request->getPost('unit_price'),
            'incurred_on' => $this->request->getPost('incurred_on'),
            'notes'       => $this->request->getPost('notes') ?: null,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        if (empty($data['title']) || $data['unit_price'] <= 0 || empty($data['incurred_on'])) {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => 'Title, unit price and date are required.']);
        }

        $this->db->table('projectcosts')->insert($data);

        return $this->_successResponse('refreshProjectStats_' . $projectId . ' refreshProjectCosts_' . $projectId, 'Cost added.');
    }

    public function destroyCost(int $costId)
    {
        $cost = $this->db->table('projectcosts')->where('id', $costId)->get()->getRowArray();

        if (!$cost) {
            return $this->response->setStatusCode(404)->setBody('Cost not found.');
        }

        $this->db->table('projectcosts')->where('id', $costId)->delete();

        return $this->_successResponse('refreshProjectStats_' . $cost['project_id'] . ' refreshProjectCosts_' . $cost['project_id'], 'Cost deleted.');
    }

    // ── Sub-resource: delivery items ──────────────────────────────────────────

    public function storeDeliveryItem(int $projectId)
    {
        $data = [
            'project_id' => $projectId,
            'name'       => $this->request->getPost('name'),
            'quantity'   => (int) $this->request->getPost('quantity'),
            'unit_price' => (float) $this->request->getPost('unit_price'),
        ];

        if (empty($data['name']) || $data['quantity'] <= 0 || $data['unit_price'] <= 0) {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => 'Name, quantity and unit price are required.']);
        }

        $this->db->table('projectdeliveryitems')->insert($data);

        return $this->_successResponse('refreshProjectStats_' . $projectId . ' refreshDeliveryItems_' . $projectId, 'Item added.');
    }

    public function destroyDeliveryItem(int $itemId)
    {
        $item = $this->db->table('projectdeliveryitems')->where('id', $itemId)->get()->getRowArray();

        if (!$item) {
            return $this->response->setStatusCode(404)->setBody('Item not found.');
        }

        $this->db->table('projectdeliveryitems')->where('id', $itemId)->delete();

        return $this->_successResponse('refreshProjectStats_' . $item['project_id'] . ' refreshDeliveryItems_' . $item['project_id'], 'Item deleted.');
    }

    // ── Sub-resource: payments ────────────────────────────────────────────────

    public function storePayment(int $projectId)
    {
        $project = (new ProjectModel)->find($projectId);

        if (!$project) {
            return $this->response->setStatusCode(404)->setBody('Project not found.');
        }

        if ($project['status'] === 'completed') {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => 'Cannot add payments to a completed project.']);
        }

        $data = [
            'project_id'   => $projectId,
            'amount'       => (float) $this->request->getPost('amount'),
            'payment_date' => $this->request->getPost('payment_date') ?: date('Y-m-d H:i:s'),
            'method'       => $this->request->getPost('method') ?: null,
            'reference'    => $this->request->getPost('reference') ?: null,
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        if ($data['amount'] <= 0) {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => 'Payment amount must be greater than zero.']);
        }

        $this->db->table('projectpayments')->insert($data);

        return $this->_successResponse('refreshProjectStats_' . $projectId . ' refreshProjectPayments_' . $projectId, 'Payment recorded.');
    }

    public function destroyPayment(int $paymentId)
    {
        $payment = $this->db->table('projectpayments')->where('id', $paymentId)->get()->getRowArray();

        if (!$payment) {
            return $this->response->setStatusCode(404)->setBody('Payment not found.');
        }

        $this->db->table('projectpayments')->where('id', $paymentId)->delete();

        return $this->_successResponse(
            'refreshProjectPayments_' . $payment['project_id'],
            'Payment deleted.'
        );
    }

    // ── Documents ─────────────────────────────────────────────────────────────

    public function invoice(int $id)
    {
        return $this->_generateDocument($id, 'invoice');
    }

    public function deliveryNote(int $id)
    {
        return $this->_generateDocument($id, 'delivery_note');
    }

    private function _generateDocument(int $id, string $type): mixed
    {
        $project = (new ProjectModel)->findWithDetails($id);

        if (!$project) {
            return $this->response->setStatusCode(404)->setBody('Project not found.');
        }

        if (empty($project['delivery_items'])) {
            return $this->response->setStatusCode(422)
                ->setBody('No delivery items found. Add items before generating documents.');
        }

        // Render the document HTML view
        $html = view('projects/documents/' . $type, ['project' => $project]);

        // Generate PDF with mPDF
        try {
            $mpdf = new \Mpdf\Mpdf([
                'margin_top'    => 15,
                'margin_bottom' => 15,
                'margin_left'   => 15,
                'margin_right'  => 15,
                'format'        => 'A4',
            ]);

            $mpdf->WriteHTML($html);

            $prefix   = $type === 'invoice' ? 'INV' : 'DN';
            $filename = $prefix . '-' . date('Y') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT) . '.pdf';

            return $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);

        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)
                ->setBody('PDF generation failed: ' . $e->getMessage());
        }
    }
    // ── Sub-resource partials (HTMX loaders) ─────────────────────────────────

    public function costsPartial(int $projectId)
    {
        $project = (new ProjectModel)->find($projectId);
        if (!$project) {
            return $this->response->setStatusCode(404)->setBody('Project not found.');
        }

        $costs = $this->db->table('projectcosts')
            ->where('project_id', $projectId)
            ->orderBy('incurred_on', 'DESC')
            ->get()
            ->getResultArray();

        return view('projects/partials/costs', [
            'costs'     => $costs,
            'projectId' => $projectId,
            'isActive'  => $project['status'] === 'active',
        ]);
    }

    public function deliveryItemsPartial(int $projectId)
    {
        $project = (new ProjectModel)->find($projectId);
        if (!$project) {
            return $this->response->setStatusCode(404)->setBody('Project not found.');
        }

        $items = $this->db->table('projectdeliveryitems')
            ->where('project_id', $projectId)
            ->get()
            ->getResultArray();

        return view('projects/partials/delivery_items', [
            'items'     => $items,
            'projectId' => $projectId,
            'isActive'  => $project['status'] === 'active',
        ]);
    }

    public function paymentsPartial(int $projectId)
    {
        $project = (new ProjectModel)->find($projectId);
        if (!$project) {
            return $this->response->setStatusCode(404)->setBody('Project not found.');
        }

        $payments = $this->db->table('projectpayments')
            ->where('project_id', $projectId)
            ->orderBy('payment_date', 'DESC')
            ->get()
            ->getResultArray();

        return view('projects/partials/payments', [
            'payments'  => $payments,
            'projectId' => $projectId,
            'isActive'  => $project['status'] === 'active',
        ]);
    }
    // ── Stats partial ─────────────────────────────────────────────────────────

    public function statsPartial(int $projectId)
    {
        $project = (new ProjectModel)->findWithDetails($projectId);
        if (!$project) {
            return $this->response->setStatusCode(404)->setBody('Project not found.');
        }
        return view('projects/partials/stats', ['project' => $project]);
    }

    // ── Update sub-resources ──────────────────────────────────────────────────

    public function updateCost(int $costId)
    {
        $cost = $this->db->table('projectcosts')->where('id', $costId)->get()->getRowArray();
        if (!$cost) {
            return $this->response->setStatusCode(404)->setBody('Cost not found.');
        }

        $unitPrice = (float) $this->request->getPost('unit_price');
        $quantity  = (int) $this->request->getPost('quantity') ?: 1;

        if (empty($this->request->getPost('title')) || $unitPrice <= 0) {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => 'Title and unit price are required.']);
        }

        $this->db->table('projectcosts')->where('id', $costId)->update([
            'title'       => $this->request->getPost('title'),
            'quantity'    => $quantity,
            'unit_price'  => $unitPrice,
            'incurred_on' => $this->request->getPost('incurred_on'),
            'notes'       => $this->request->getPost('notes') ?: null,
        ]);

        return $this->_successResponse(
            'refreshProjectStats_' . $cost['project_id'] . ' refreshProjectCosts_' . $cost['project_id'],
            'Cost updated.'
        );
    }

    public function updateDeliveryItem(int $itemId)
    {
        $item = $this->db->table('projectdeliveryitems')->where('id', $itemId)->get()->getRowArray();
        if (!$item) {
            return $this->response->setStatusCode(404)->setBody('Item not found.');
        }

        $unitPrice = (float) $this->request->getPost('unit_price');
        $quantity  = (int) $this->request->getPost('quantity') ?: 1;

        if (empty($this->request->getPost('name')) || $unitPrice <= 0 || $quantity <= 0) {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => 'Name, quantity and unit price are required.']);
        }

        $this->db->table('projectdeliveryitems')->where('id', $itemId)->update([
            'name'       => $this->request->getPost('name'),
            'quantity'   => $quantity,
            'unit_price' => $unitPrice,
        ]);

        return $this->_successResponse(
            'refreshProjectStats_' . $item['project_id'] . ' refreshDeliveryItems_' . $item['project_id'],
            'Item updated.'
        );
    }

    public function updatePayment(int $paymentId)
    {
        $payment = $this->db->table('projectpayments')->where('id', $paymentId)->get()->getRowArray();
        if (!$payment) {
            return $this->response->setStatusCode(404)->setBody('Payment not found.');
        }

        $amount = (float) $this->request->getPost('amount');
        if ($amount <= 0) {
            return $this->response->setStatusCode(422)
                ->setJSON(['error' => 'Payment amount must be greater than zero.']);
        }

        $this->db->table('projectpayments')->where('id', $paymentId)->update([
            'amount'       => $amount,
            'payment_date' => $this->request->getPost('payment_date') ?: date('Y-m-d H:i:s'),
            'method'       => $this->request->getPost('method') ?: null,
            'reference'    => $this->request->getPost('reference') ?: null,
        ]);

        return $this->_successResponse(
            'refreshProjectStats_' . $payment['project_id'] . ' refreshProjectPayments_' . $payment['project_id'],
            'Payment updated.'
        );
    }

}