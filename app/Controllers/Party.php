<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Party as PartyModel;
use App\Models\PartyContact; // ← fix: add the use statement

class Party extends BaseController
{
    public function index()
    {
        return view('parties/index');
    }

    public function list()
    {
        $parties = (new PartyModel)->findAllWithContacts();
        return view('parties/partials/list', ['parties' => $parties]);
    }

    public function form($id = null)
    {
        $data = [];
        $partyModel = new PartyModel();

        if ($id !== null) {
            $party = $partyModel->find($id);
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

        return view('parties/partials/form', $data);
    }

    public function store()
    {
        $partyModel = new PartyModel();

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

        $partyModel->save($data);
        $partyId = $partyModel->getInsertID();

        $this->_saveContacts($partyId);

        return $this->_successResponse('refreshPartyList','Party created successfully.');
    }

    public function update(int $id)
    {
        $partyModel = new PartyModel();
        $party = $partyModel->find($id);

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

        $partyModel->update($id, $data);

        (new PartyContact)->where('party_id', $id)->delete();
        $this->_saveContacts($id);

        return $this->_successResponse('refreshPartyList','Party updated successfully.');
    }

    private function _saveContacts(int $partyId): void
    {
        $contactModel = new PartyContact(); // ← now resolves correctly

        $phones = $this->request->getPost('phone') ?? [];
        $phones = is_array($phones) ? $phones : [$phones];

        $emails = $this->request->getPost('email') ?? [];
        $emails = is_array($emails) ? $emails : [$emails];

        foreach ($phones as $phone) {
            $phone = trim((string) $phone);
            if ($phone !== '') {
                $contactModel->save([
                    'party_id'      => $partyId,
                    'contact_type'  => 'phone',
                    'contact_value' => $phone,
                ]);
            }
        }

        foreach ($emails as $email) {
            $email = trim((string) $email);
            if ($email !== '') {
                $contactModel->save([
                    'party_id'      => $partyId,
                    'contact_type'  => 'email',
                    'contact_value' => $email,
                ]);
            }
        }
    }
}