<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BudgetItem as BudgetItemModel;
use App\Models\Category   as CategoryModel;

class BudgetItem extends BaseController
{
    /**
     * Budget items list for a specific subcategory.
     * Loaded as an HTMX partial — embedded inside the category dashboard card.
     */
    public function list(int $categoryId)
    {
        $items = (new BudgetItemModel)->findForCategory($categoryId);
        return view('budget_items/partials/list', [
            'items'      => $items,
            'categoryId' => $categoryId,
        ]);
    }

    /**
     * Create / edit form for a budget item.
     * ?category_id=N pre-selects the subcategory when adding from a category card.
     */
    public function form(?int $id = null)
    {
        $categoryModel = new CategoryModel();
        $data = [
            'subcategories'       => $categoryModel->findAllSubcategories(),
            'preselectedCategory' => $this->request->getGet('category_id'),
        ];

        if ($id !== null) {
            $item = (new BudgetItemModel)->find($id);
            if (!$item) {
                return $this->response->setStatusCode(404)->setBody('Budget item not found.');
            }
            $data['item'] = $item;
        }

        return view('budget_items/partials/form', $data);
    }

    public function store()
    {
        $model = new BudgetItemModel();

        $itemType   = $this->request->getPost('item_type');
        $recurrence = $itemType === 'recurring'
            ? $this->request->getPost('recurrence')
            : null;

        $data = [
            'category_id' => $this->request->getPost('category_id'),
            'name'        => $this->request->getPost('name'),
            'amount'      => (float) $this->request->getPost('amount'),
            'item_type'   => $itemType,
            'recurrence'  => $recurrence,
            'due_date'    => $this->request->getPost('due_date') ?: null,
            'notes'       => $this->request->getPost('notes') ?: null,
            'status'      => 'pending',
        ];

        if (!$model->validate($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => implode(' ', $model->errors())]);
        }

        // Ensure it's a subcategory
        $category = (new CategoryModel)->find((int)$data['category_id']);
        if (!$category || empty($category['parent_category_id'])) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Budget items must belong to a subcategory.']);
        }

        $model->insert($data);

        return $this->_successResponse(
            'refreshBudgetItems_' . $data['category_id'],
            'Budget item added.'
        );
    }

    public function update(int $id)
    {
        $model = new BudgetItemModel();
        $item  = $model->find($id);

        if (!$item) {
            return $this->response->setStatusCode(404)->setBody('Budget item not found.');
        }

        if ($item['status'] === 'fulfilled') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Fulfilled items cannot be edited.']);
        }

        $itemType   = $this->request->getPost('item_type');
        $recurrence = $itemType === 'recurring'
            ? $this->request->getPost('recurrence')
            : null;

        $data = [
            'name'       => $this->request->getPost('name'),
            'amount'     => (float) $this->request->getPost('amount'),
            'item_type'  => $itemType,
            'recurrence' => $recurrence,
            'due_date'   => $this->request->getPost('due_date') ?: null,
            'notes'      => $this->request->getPost('notes') ?: null,
        ];

        if (!$model->validate($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => implode(' ', $model->errors())]);
        }

        $model->update($id, $data);

        return $this->_successResponse(
            'refreshBudgetItems_' . $item['category_id'],
            'Budget item updated.'
        );
    }

    public function destroy(int $id)
    {
        $model = new BudgetItemModel();
        $item  = $model->find($id);

        if (!$item) {
            return $this->response->setStatusCode(404)->setBody('Budget item not found.');
        }

        if ($item['status'] === 'fulfilled') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Fulfilled items cannot be deleted — the transaction record must be kept.']);
        }

        $categoryId = $item['category_id'];
        $model->delete($id);

        return $this->_successResponse(
            'refreshBudgetItems_' . $categoryId,
            'Budget item deleted.'
        );
    }

    /**
     * Fulfil a budget item — creates the expense transaction and marks it done.
     */
    public function fulfil(int $id)
    {
        $model = new BudgetItemModel();
        $item  = $model->find($id);

        if (!$item) {
            return $this->response->setStatusCode(404)->setBody('Budget item not found.');
        }

        $date        = $this->request->getPost('date') ?: date('Y-m-d H:i:s');
        $description = $this->request->getPost('description') ?: '';

        try {
            $model->fulfil($id, $date, $description);
        } catch (\RuntimeException $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => $e->getMessage()]);
        }

        return $this->_successResponse(
            'refreshBudgetItems_' . $item['category_id'] . ' refreshTransactionList',
            'Budget item fulfilled and transaction recorded.'
        );
    }
}