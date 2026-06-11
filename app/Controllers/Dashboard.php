<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Category    as CategoryModel;
use App\Models\Account     as AccountModel;
use App\Models\Transaction as TransactionModel;
use App\Models\Debt        as DebtModel;
use App\Models\BudgetItem  as BudgetItemModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $month = $this->request->getGet('month') ?: date('Y-m');
        return view('dashboard/index', ['month' => $month]);
    }

    /**
     * Main dashboard partial — loaded on page load and on month change.
     */
    public function summary()
    {
        $month = $this->request->getGet('month') ?: date('Y-m');

        $categoryModel    = new CategoryModel();
        $accountModel     = new AccountModel();
        $transactionModel = new TransactionModel();
        $debtModel        = new DebtModel();
        $budgetItemModel  = new BudgetItemModel();

        // Category balances for the period
        $balanceData = $categoryModel->findAllWithBalances($month);

        // Account balances (live, not period-scoped)
        $accounts = $accountModel->findAll();
        $totalAccountBalance = array_sum(array_column($accounts, 'current_balance'));

        // Period transaction summary
        $summary = $transactionModel->monthlySummary($month);

        // Outstanding debts
        $debts = $debtModel->findAllWithParty(false);
        $totalDebtOwed = array_sum(array_column(
            array_filter($debts, fn($d) => $d['debt_type'] === 'owed_by_me'),
            'current_balance'
        ));

        // Pending budget items
        $pendingItems = $budgetItemModel->findAllPending();

        return view('dashboard/partials/summary', [
            'month'               => $month,
            'balanceData'         => $balanceData,
            'accounts'            => $accounts,
            'totalAccountBalance' => $totalAccountBalance,
            'summary'             => $summary,
            'debts'               => $debts,
            'totalDebtOwed'       => $totalDebtOwed,
            'pendingItems'        => $pendingItems,
        ]);
    }
}