<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Account as AccountModel;

class Account extends BaseController
{
    public function index()
    {
        return view('accounts/index');
    }
    public function list()
    {
        $accounts = (new AccountModel)->findAll();
        return view('accounts/partials/list', ['accounts' => $accounts]);
    }
    public function form(?int $id = null)
    {
        $data = ['types' => AccountModel::types()];

        if ($id !== null) {
            $account = (new AccountModel)->find($id);
            if (!$account) {
                return $this->response->setStatusCode(404)->setBody('Account not found.');
            }
            $data['account'] = $account;
        }

        return view('accounts/partials/form', $data);
    }

    public function store()
    {
        $accountModel = new AccountModel();

        $data = [
            'account_name'    => $this->request->getPost('account_name'),
            'account_type'    => $this->request->getPost('account_type'),
            'current_balance' => (float) $this->request->getPost('current_balance'),
            'color'        =>$this->request->getPost('color')
        ];

        if (!$accountModel->validate($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => implode(' ', $accountModel->errors())]);
        }

        $accountModel->insert($data);

        return $this->_successResponse('refreshAccountList', 'Account created successfully.');
    }
    public function update(int $id)
    {
        $accountModel   = new AccountModel();
        $account = $accountModel->find($id);

        if (!$account) {
            return $this->response->setStatusCode(404)->setBody('Account not found.');
        }

        $data = [
            'account_name' => $this->request->getPost('account_name'),
            'account_type' => $this->request->getPost('account_type'),
            'currency'     => strtoupper(trim($this->request->getPost('currency') ?: 'KES')),
            // current_balance is NOT editable via this form after creation —
            // it is maintained exclusively by transaction operations.
        ];

        if (!$accountModel->validate($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => implode(' ', $accountModel->errors())]);
        }

        $accountModel->update($id, $data);

        return $this->_successResponse('refreshAccountList', 'Account updated successfully.');
    }





}
