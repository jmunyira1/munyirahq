<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Transaction as TransactionModel;
use App\Models\Account     as AccountModel;
use App\Models\Category    as CategoryModel;
use App\Models\Debt        as DebtModel;

class Transaction extends BaseController
{
    // ── Pages ─────────────────────────────────────────────────────────────────
    protected $db;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        // Do not forget to call parent
        parent::initController($request, $response, $logger);

        // Initialize the database connection here
        $this->db = \Config\Database::connect();
    }    public function index()
    {
        return view('transactions/index');
    }

    // ── HTMX partials ─────────────────────────────────────────────────────────

    /**
     * Filtered, paginated transaction list.
     * Accepts GET: type, date_from, date_to, month, page
     */
    public function list()
    {
        $filters = [
            'type'      => $this->request->getGet('type'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
            'month'     => $this->request->getGet('month') ?: date('Y-m'),
        ];
        $page    = (int) ($this->request->getGet('page') ?: 1);
        $perPage = 30;

        $model  = new TransactionModel();
        $result = $model->findFiltered($filters, $perPage, $page);

        // Summary for the active month (always the selected month, not the range)
        $summary = $model->monthlySummary($filters['month'] ?: date('Y-m'));

        return view('transactions/partials/list', [
            'transactions' => $result['transactions'],
            'total'        => $result['total'],
            'page'         => $page,
            'perPage'      => $perPage,
            'filters'      => $filters,
            'summary'      => $summary,
        ]);
    }

    /**
     * Adaptive form partial.
     *
     * GET params for pre-filling from other pages:
     *   ?type=debt_payment&debt_id=N     ← from Debts list "Pay" button
     *   ?type=transfer&account_id=N      ← from Accounts list "Transfer" button
     *   ?type=expense                    ← from any shortcut
     */
    public function form()
    {
        $type      = $this->request->getGet('type') ?: 'expense';
        $debtId    = $this->request->getGet('debt_id');
        $accountId = $this->request->getGet('account_id');

        $categoryModel = new CategoryModel();
        $accountModel  = new AccountModel();
        $debtModel     = new DebtModel();

        $data = [
            'type'          => $type,
            'preDebtId'     => $debtId,
            'preAccountId'  => $accountId,
            'subcategories' => $categoryModel->findAllSubcategories(),
            'accounts'      => $accountModel->findAll(),
            'debts'         => $debtModel->findAllWithParty(false), // active debts with party_name
        ];

        // Pre-load specific records for focused forms
        if ($debtId) {
            $data['preDebt'] = $debtModel->findOneWithParty((int) $debtId);
        }
        if ($accountId) {
            $data['preAccount'] = $accountModel->find($accountId);
        }

        return view('transactions/partials/form', $data);
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    /**
     * Single endpoint for all four transaction types.
     * Routes internally to the correct handler after validation.
     */
    public function store()
    {
        $type = $this->request->getPost('transaction_type');

        if (!in_array($type, ['income', 'expense', 'transfer', 'debt_payment'])) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Invalid transaction type.']);
        }

        return match($type) {
            'income'       => $this->_storeIncome(),
            'expense'      => $this->_storeExpense(),
            'transfer'     => $this->_storeTransfer(),
            'debt_payment' => $this->_storeDebtPayment(),
        };
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    /**
     * Deletes a transaction and reverses its balance effect.
     * Transfers are blocked — they cannot be deleted.
     */
    public function destroy(int $id)
    {
        $model       = new TransactionModel();
        $transaction = $model->find($id);

        if (!$transaction) {
            return $this->response->setStatusCode(404)->setBody('Transaction not found.');
        }

        if ($transaction['transaction_type'] === 'transfer') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Transfers cannot be deleted once recorded.']);
        }

        $accountModel = new AccountModel();

        $this->db->transStart();

        try {
            // Reverse the balance effect
            match($transaction['transaction_type']) {

                // Income added money to account — take it back
                'income' => $accountModel->debit(
                    (int) $transaction['account_id'],
                    (float) $transaction['amount']
                ),

                // Expense deducted money — restore it
                'expense' => $accountModel->credit(
                    (int) $transaction['account_id'],
                    (float) $transaction['amount']
                ),

                // Debt payment deducted from account and reduced debt balance — reverse both
                'debt_payment' => $this->_reverseDebtPayment($transaction),
            };

            $model->delete($id);

            $this->db->transComplete();

        } catch (\Throwable $e) {
            $this->db->transRollback();
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Could not delete transaction: ' . $e->getMessage()]);
        }

        return $this->_successResponse('refreshTransactionList', 'Transaction deleted.');
    }

    // ── Private: type handlers ────────────────────────────────────────────────

    private function _storeIncome(): mixed
    {
        $accountId =  $this->request->getPost('i_account_id');
        $amount    = (float) $this->request->getPost('i_amount');
        $date      = $this->request->getPost('transaction_date') ?: date('Y-m-d H:i:s');

        if (!$accountId || $amount <= 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Account and a positive amount are required. '.$accountId]);
        }

        $accountModel = new AccountModel();
        if (!$accountModel->find($accountId)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Account not found.']);
        }

        $this->db->transStart();

        (new TransactionModel)->insert([
            'account_id'       => $accountId,
            'amount'           => $amount,
            'transaction_type' => 'income',
            'description'      => $this->request->getPost('description'),
            'transaction_date' => $date,
        ]);

        // Income increases the account balance
        $accountModel->credit($accountId, $amount);

        $this->db->transComplete();

        return $this->_successResponse('refreshTransactionList', 'Income recorded.');
    }

    private function _storeExpense(): mixed
    {
        $categoryId = (int) $this->request->getPost('category_id');
        $amount     = (float) $this->request->getPost('e_amount');
        $date       = $this->request->getPost('transaction_date') ?: date('Y-m-d H:i:s');

        if (!$categoryId || $amount <= 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Category and a positive amount are required.']);
        }

        // Pull account from the subcategory — user does not choose it
        $category = (new CategoryModel)->findSubcategoryWithAccount($categoryId);
        if (!$category) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Subcategory not found or has no linked account.']);
        }

        $accountId    = (int) $category['account_id'];
        $accountModel = new AccountModel();

        $this->db->transStart();

        (new TransactionModel)->insert([
            'account_id'       => $accountId,
            'category_id'      => $categoryId,
            'amount'           => $amount,
            'transaction_type' => 'expense',
            'description'      => $this->request->getPost('description'),
            'transaction_date' => $date,
        ]);

        // Expense reduces the account balance
        $accountModel->debit($accountId, $amount);

        $this->db->transComplete();

        return $this->_successResponse('refreshTransactionList', 'Expense recorded.');
    }

    private function _storeTransfer(): mixed
    {
        $fromId = (int) $this->request->getPost('t_account_id');
        $toId   = (int) $this->request->getPost('transfer_to_account_id');
        $amount = (float) $this->request->getPost('t_amount');
        $date   = $this->request->getPost('transaction_date') ?: date('Y-m-d H:i:s');

        if (!$fromId || !$toId || $amount <= 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Source account, destination account, and a positive amount are required.']);
        }

        if ($fromId === $toId) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Source and destination accounts must be different.']);
        }

        $accountModel = new AccountModel();

        $this->db->transStart();

        (new TransactionModel)->insert([
            'account_id'              => $fromId,
            'transfer_to_account_id'  => $toId,
            'amount'                  => $amount,
            'transaction_type'        => 'transfer',
            'description'             => $this->request->getPost('description'),
            'transaction_date'        => $date,
        ]);

        // Deduct from source, credit destination
        $accountModel->debit($fromId, $amount);
        $accountModel->credit($toId,   $amount);

        $this->db->transComplete();

        return $this->_successResponse('refreshTransactionList', 'Transfer recorded.');
    }

    private function _storeDebtPayment(): mixed
    {
        $accountId = (int) $this->request->getPost('d_account_id');
        $debtId    = (int) $this->request->getPost('debt_id');
        $amount    = (float) $this->request->getPost('d_amount');
        $date      = $this->request->getPost('transaction_date') ?: date('Y-m-d H:i:s');

        if (!$accountId || !$debtId || $amount <= 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Account, debt, and a positive amount are required.']);
        }

        $debtModel = new DebtModel();
        $debt      = $debtModel->find($debtId);

        if (!$debt) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Debt not found.']);
        }

        if ((float) $debt['current_balance'] <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'This debt is already fully paid.']);
        }

        // Cap payment to remaining balance — never overpay
        $effective = min($amount, (float) $debt['current_balance']);

        $accountModel = new AccountModel();
        $newBalance   = max(0, (float) $debt['current_balance'] - $effective);

        $this->db->transStart();

        (new TransactionModel)->insert([
            'account_id'       => $accountId,
            'debt_id'          => $debtId,
            'amount'           => $effective,
            'transaction_type' => 'debt_payment',
            'description'      => $this->request->getPost('description'),
            'transaction_date' => $date,
        ]);

        // Deduct payment from account
        $accountModel->debit($accountId, $effective);

        // Reduce debt balance, flip status to paid if cleared
        $debtModel->update($debtId, [
            'current_balance' => $newBalance,
            'status'          => $newBalance <= 0 ? 1 : 0,
        ]);

        $this->db->transComplete();

        return $this->_successResponse(
            'refreshTransactionList refreshDebtList',
            'Debt payment recorded.'
        );
    }

    private function _reverseDebtPayment(array $transaction): void
    {
        $accountModel = new AccountModel();
        $debtModel    = new DebtModel();
        $debt         = $debtModel->find($transaction['debt_id']);

        // Restore account balance
        $accountModel->credit(
            (int) $transaction['account_id'],
            (float) $transaction['amount']
        );

        // Restore debt balance and reactivate if it was marked paid
        if ($debt) {
            $restored = (float) $debt['current_balance'] + (float) $transaction['amount'];
            $debtModel->update($transaction['debt_id'], [
                'current_balance' => $restored,
                'status'          => 0, // reactivate
            ]);
        }
    }
}