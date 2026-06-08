<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Category as CategoryModel;
use App\Models\Account as AccountModel;


class Category extends BaseController
{
    public function index()
    {
        return view('categories/index');
    }

    // ── HTMX partials ─────────────────────────────────────────────────────────

    public function list()
    {
        $categories = (new CategoryModel)->findAllNested();
        return view('categories/partials/list', ['categories' => $categories]);
    }

    /**
     * GET param ?parent_id=N pre-selects a parent so "Add subcategory"
     * buttons on the list can open the form already scoped to that parent.
     */
    public function form(?int $id = null)
    {
        $categoryModel = new CategoryModel();

        $data = [
            'accounts' => (new AccountModel)->findAll(),
            'parents'  => $categoryModel->where('parent_category_id', null)->findAll(),
            'preselectedParentId' => $this->request->getGet('parent_id'),
        ];

        if ($id !== null) {
            $category = $categoryModel->find($id);
            if (!$category) {
                return $this->response->setStatusCode(404)->setBody('Category not found.');
            }
            $data['category'] = $category;
        }

        return view('categories/partials/form', $data);
    }

    // ── CUD ───────────────────────────────────────────────────────────────────

    public function store()
    {
        $model     = new CategoryModel();
        $parentId  = $this->request->getPost('parent_category_id') ?: null;
        $accountId = $this->request->getPost('account_id') ?: null;
        $isSubcat  = $parentId !== null;

        if ($isSubcat && empty($accountId)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Subcategories must be linked to an account.']);
        }

        if (!$isSubcat) $accountId = null;

        $allocation =  $this->request->getPost('allocation_percentage');

        $headroomError = $this->_checkAllocationHeadroom(
            $allocation, $isSubcat, $parentId ? (int) $parentId : null
        );
        if ($headroomError) {
            return $this->response->setStatusCode(422)->setJSON(['error' => $headroomError]);
        }

        $data = [
            'parent_category_id'    => $parentId,
            'account_id'            => $accountId,
            'name'                  => $this->request->getPost('name'),
            'allocation_percentage' => $allocation,
        ];

        if (!$model->validate($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => implode(' ', $model->errors())]);
        }

        $model->insert($data);

        return $this->_successResponse('refreshCategoryList', 'Category created successfully.');
    }

    public function update(int $id)
    {
        $model    = new CategoryModel();
        $category = $model->find($id);
        if (!$category) {
            return $this->response->setStatusCode(404)->setBody('Category not found.');
        }

        $parentId  = $this->request->getPost('parent_category_id') ?: null;
        $accountId = $this->request->getPost('account_id') ?: null;
        $isSubcat  = $parentId !== null;

        if ($isSubcat && empty($accountId)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => 'Subcategories must be linked to an account.']);
        }

        if (!$isSubcat) $accountId = null;

        $allocation = $this->request->getPost('allocation_percentage');

        $headroomError = $this->_checkAllocationHeadroom(
            $allocation, $isSubcat, $parentId ? (int) $parentId : null, $id
        );
        if ($headroomError) {
            return $this->response->setStatusCode(422)->setJSON(['error' => $headroomError]);
        }

        $data = [
            'parent_category_id'    => $parentId,
            'account_id'            => $accountId,
            'name'                  => $this->request->getPost('name'),
            'allocation_percentage' => $allocation,
        ];

        if (!$model->validate($data)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => implode(' ', $model->errors())]);
        }

        $model->update($id, $data);

        return $this->_successResponse('refreshCategoryList', 'Category updated successfully.');
    }

    public function destroy(int $id)
    {
        $model    = new CategoryModel();
        $category = $model->find($id);

        if (!$category) {
            return $this->response->setStatusCode(404)->setBody('Category not found.');
        }

        $childCount = $model->where('parent_category_id', $id)->countAllResults();
        if ($childCount > 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => "Cannot delete: {$childCount} subcategor" . ($childCount === 1 ? 'y exists' : 'ies exist') . " under this category. Remove them first."]);
        }

        $txCount = $this->db->table('transactions')
            ->where('category_id', $id)
            ->countAllResults();
        if ($txCount > 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON(['error' => "Cannot delete: {$txCount} transaction(s) are linked to this category."]);
        }

        $model->delete($id);

        return $this->_successResponse('refreshCategoryList', 'Category deleted.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function _checkAllocationHeadroom(
        float $allocation,
        bool  $isSubcat,
        ?int  $parentId,
        ?int  $excludeId = null
    ): ?string {
        $model    = new CategoryModel();
        $existing = $isSubcat
            ? $model->childAllocationTotal($parentId, $excludeId)
            : $model->parentAllocationTotal($excludeId);

        if (($existing + $allocation) > 1.0001) {
            $remaining = round((1.0 - $existing) * 100, 2);
            return "Total allocation would exceed 100%. You have {$remaining}% remaining.";
        }

        return null;
    }

}
