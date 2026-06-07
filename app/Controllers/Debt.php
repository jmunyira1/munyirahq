<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Debt as DebtModel;

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
        $DebtModel = new DebtModel();

        if ($id !== null) {
            $party = $DebtModel->find($id);
            if (!$party) {
                return $this->response->setStatusCode(404)->setBody('Party not found');
            }

            // Read contacts from the contacts table, not JSON columns
            $contactModel = new PartyContact();
            $contacts = $contactModel->where('party_id', $id)->findAll();

            $party['emails'] = array_column(
                array_filter($contacts, fn($c) => $c['contact_type'] === 'email'),
                'contact_value'
            );
            $party['phones'] = array_column(
                array_filter($contacts, fn($c) => $c['contact_type'] === 'phone'),
                'contact_value'
            );

            $data['party'] = $party;
        }

        return view('debts/partials/form', $data);
    }

    public function store()
    {
        $DebtModel = new DebtModel();

        $gender = $this->request->getPost('gender');
        $data = [
            'title'     => $this->request->getPost('title') ?: null,
            'name'      => $this->request->getPost('name'),
            'is_person' => (int) $this->request->getPost('isPerson'),
            'gender'    => ($gender !== '' && $gender !== null) ? (int) $gender : null,
        ];

        if (empty(trim((string) $data['name']))) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Full name is required.']);
        }

        $DebtModel->save($data);
        $partyId = $DebtModel->getInsertID();

        $this->_saveContacts($partyId);

        return $this->_successResponse('Party created successfully.');
    }

    public function update(int $id)
    {
        $DebtModel = new DebtModel();
        $party = $DebtModel->find($id);

        if (!$party) {
            return $this->response->setStatusCode(404)->setBody('Party not found.');
        }

        $gender = $this->request->getPost('gender');
        $data = [
            'title'     => $this->request->getPost('title') ?: null,
            'name'      => $this->request->getPost('name'),
            'is_person' => (int) $this->request->getPost('isPerson'),
            'gender'    => ($gender !== '' && $gender !== null) ? (int) $gender : null,
        ];

        if (empty(trim((string) $data['name']))) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Full name is required.']);
        }

        $DebtModel->update($id, $data);

        (new PartyContact)->where('party_id', $id)->delete();
        $this->_saveContacts($id);

        return $this->_successResponse('refreshPartyList','Party updated successfully.');
    }
}
