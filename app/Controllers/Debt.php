<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Debt as DebtModel;
use App\Models\Party as PartyModel;

class Debt extends BaseController
{
    public function index()
    {
        return view('debts/index');
    }

    public function list()
    {
        $debts = (new DebtModel)->findAllWithParty();
        return view('debts/partials/list', ['debts' => $debts]);
    }

    public function form($id = null)
    {
        $data = [];
        $debtModel = new DebtModel();
        $partyModel = new PartyModel();

        if ($id !== null) {
            $debt = $debtModel->find($id);
            if (!$debt) {
                return $this->response->setStatusCode(404)->setBody('debt not found');
            }
            $party = $partyModel->find($debt['party_id']);
            $data['debt'] = $debt;
            $data['party'] = $party;
        }
        $parties = $partyModel->findAll();

        $data['parties'] = $parties;

        return view('debts/partials/form', $data);
    }

    public function store()
    {
        $debtModel = new DebtModel();
        $data = [
            'amount'     => $this->request->getPost('amount'),
            'party_id'      => $this->request->getPost('party'),
        ];
        $debtModel->save($data);
        return $this->_successResponse('refreshDebtList','debt created successfully.');
    }

    public function update(int $id)
    {
        $debtModel = new DebtModel();
        $debt = $debtModel->find($id);

        if (!$debt) {
            return $this->response->setStatusCode(404)->setBody('debt not found.');
        }

        $data = [
            'amount'     => $this->request->getPost('amount'),
            'party_id'      => $this->request->getPost('party'),
        ];

        $debtModel->update($id, $data);


        return $this->_successResponse('refreshDebtList','debt updated successfully.');
    }
}
