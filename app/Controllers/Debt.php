<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Debt  as DebtModel;
use App\Models\Party as PartyModel;

class Debt extends BaseController
{
    // ── Pages ─────────────────────────────────────────────────────────────────

    public function index()
    {
        return view('debts/index');
    }

    // ── HTMX partials ─────────────────────────────────────────────────────────

    public function list()
    {
        $includeSettled = (bool) $this->request->getGet('settled');
        $debts = (new DebtModel)->findAllWithParty($includeSettled);

        return view('debts/partials/list', [
            'debts'          => $debts,
            'includeSettled' => $includeSettled,
        ]);
    }

    public function form(?int $id = null)
    {
        $data = [
            'parties' => (new PartyModel)->findAll(),
            'types'   => DebtModel::types(),
        ];

        if ($id !== null) {
            $debt = (new DebtModel)->find($id);
            if (!$debt) {
                return $this->response->setStatusCode(404)->setBody('Debt not found.');
            }
            $data['debt'] = $debt;
        }

        return view('debts/partials/form', $data);
    }

    // ── CUD ───────────────────────────────────────────────────────────────────

    public function store()
    {
        $model  = new DebtModel();
        $amount = (float) $this->request->getPost('total_principal');

        $data = [
            'party_id'        => $this->request->getPost('party_id'),
            'debt_type'       => $this->request->getPost('debt_type'),
            'total_principal' => $amount,
            'current_balance' => $amount, // balance starts equal to the full principal
            'status'          => 0,
            'due_date'        => $this->request->getPost('due_date') ?: null,
        ];

        if (!$model->validate($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => implode(' ', $model->errors())]);
        }

        $model->insert($data);

        return $this->_successResponse('refreshDebtList', 'Debt recorded.');
    }

    public function update(int $id)
    {
        $model = new DebtModel();
        $debt  = $model->find($id);

        if (!$debt) {
            return $this->response->setStatusCode(404)->setBody('Debt not found.');
        }

        $newPrincipal = (float) $this->request->getPost('total_principal');

        // Recalculate balance: new principal minus what has already been paid
        $alreadyPaid = (float) $debt['total_principal'] - (float) $debt['current_balance'];
        $newBalance  = max(0, $newPrincipal - $alreadyPaid);

        $data = [
            'party_id'        => $this->request->getPost('party_id'),
            'debt_type'       => $this->request->getPost('debt_type'),
            'total_principal' => $newPrincipal,
            'current_balance' => $newBalance,
            'status'          => $newBalance <= 0 ? 1 : 0,
            'due_date'        => $this->request->getPost('due_date') ?: null,
        ];

        if (!$model->validate($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => implode(' ', $model->errors())]);
        }

        $model->update($id, $data);

        return $this->_successResponse('refreshDebtList', 'Debt updated.');
    }

    public function destroy(int $id)
    {
        $model = new DebtModel();
        $debt  = $model->find($id);

        if (!$debt) {
            return $this->response->setStatusCode(404)->setBody('Debt not found.');
        }

        // Block deletion if payments have been made against this debt
        $txCount = $this->db->table('transactions')
            ->where('debt_id', $id)
            ->countAllResults();

        if ($txCount > 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => "Cannot delete: {$txCount} payment transaction(s) are linked to this debt."]);
        }

        $model->delete($id);

        return $this->_successResponse('refreshDebtList', 'Debt deleted.');
    }
}