<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/',                  'Dashboard::index',   ['as' => 'dashboard.index']);
$routes->get('dashboard',          'Dashboard::index',   ['as' => 'dashboard']);
$routes->get('dashboard/summary',  'Dashboard::summary', ['as' => 'dashboard.summary']);



$routes->group('budget-item', ['namespace' => 'App\Controllers'], function ($routes) {
    // 1. Literal GET routes (Specific matches first)
    $routes->get('form', 'BudgetItem::form', ['as' => 'budget_items.form']);

    // 2. Wildcard GET routes (Placeholders at the bottom)
    $routes->get('form/(:num)',    'BudgetItem::form/$1', ['as' => 'budget_items.edit']);
    $routes->get('(:num)',         'BudgetItem::list/$1', ['as' => 'budget_items.list']);

    // 3. POST actions
    $routes->post('store',          'BudgetItem::store',      ['as' => 'budget_items.store']);
    $routes->post('update/(:num)',  'BudgetItem::update/$1',  ['as' => 'budget_items.update']);
    $routes->post('destroy/(:num)', 'BudgetItem::destroy/$1', ['as' => 'budget_items.destroy']);
    $routes->post('fulfil/(:num)',  'BudgetItem::fulfil/$1',  ['as' => 'budget_items.fulfil']);
});






$routes->group('parties', ['namespace' => 'App\Controllers'], function ($routes) {
    // GET Requests
    $routes->get('/',              'Party::index',      ['as' => 'parties']);
    $routes->get('list',           'Party::list',       ['as' => 'parties.list']);
    $routes->get('form',           'Party::form',       ['as' => 'party.form']);
    $routes->get('form/(:num)',    'Party::form/$1',    ['as' => 'party.edit']);

    // POST Requests
    $routes->post('store',          'Party::store',      ['as' => 'party.store']);
    $routes->post('update/(:num)',  'Party::update/$1',  ['as' => 'party.update']);
    $routes->post('delete/(:num)',  'Party::delete/$1',  ['as' => 'party.delete']);
});
$routes->group('debts', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get(  '/',               'Debt::index',       ['as' => 'debts']);
    $routes->get(  'list',          'Debt::list',        ['as' => 'debts.list']);
    $routes->get(  'form',           'Debt::form',        ['as' => 'debt.form']);
    $routes->get(  'form/(:num)',    'Debt::form/$1',     ['as' => 'debt.edit']);
    $routes->post( 'store',          'Debt::store',       ['as' => 'debt.store']);
    $routes->post( 'update/(:num)',  'Debt::update/$1',   ['as' => 'debt.update']);
    $routes->post( 'destroy/(:num)', 'Debt::destroy/$1',  ['as' => 'debt.destroy']);
});
$routes->group('accounts', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'Account::index', ['as' => 'accounts']);
    $routes->get('list', 'Account::list', ['as' => 'accounts.list']);
    $routes->get('form', 'Account::form', ['as' => 'account.form']);
    $routes->get('form/(:num)', 'Account::form/$1', ['as' => 'account.edit']);
    $routes->post('store', 'Account::store', ['as' => 'account.store']);
    $routes->post('update/(:num)', 'Account::update/$1', ['as' => 'account.update']);
    $routes->post('destroy/(:num)', 'Account::destroy/$1', ['as' => 'account.destroy']);
});


// ── Categories ────────────────────────────────────────────────────────────────
$routes->group('categories', ['namespace' => 'App\Controllers'], function ($routes) {

    $routes->get('/', 'Category::index', ['as' => 'categories']);
    $routes->get('list', 'Category::list', ['as' => 'categories.list']);
    $routes->get('form', 'Category::form', ['as' => 'category.form']);      // create (optional ?parent_id=N)
    $routes->get('form/(:num)', 'Category::form/$1', ['as' => 'category.edit']);      // edit
    $routes->post('store', 'Category::store', ['as' => 'category.store']);
    $routes->post('update/(:num)', 'Category::update/$1', ['as' => 'category.update']);
    $routes->post('destroy/(:num)', 'Category::destroy/$1', ['as' => 'category.destroy']);});

$routes->group('transactions', ['namespace' => 'App\Controllers'], function ($routes) {


$routes->get(  '/',               'Transaction::index',    ['as' => 'transactions']);
$routes->get(  'list',          'Transaction::list',     ['as' => 'transactions.list']);
$routes->get(  'form',           'Transaction::form',     ['as' => 'transaction.form']);
$routes->post( 'store',          'Transaction::store',    ['as' => 'transaction.store']);
$routes->post( 'destroy/(:num)', 'Transaction::destroy/$1', ['as' => 'transaction.destroy']);});


// ── Projects ──────────────────────────────────────────────────────────────────

$routes->get('projects', 'Project::index', ['as' => 'projects']);
$routes->get('projects/list', 'Project::list', ['as' => 'projects.list']);
$routes->get('project/(:num)', 'Project::show/$1', ['as' => 'project.show']);
$routes->get('project/form', 'Project::form', ['as' => 'project.form']);
$routes->get('project/form/(:num)', 'Project::form/$1', ['as' => 'project.form.edit']);
$routes->post('project/store', 'Project::store', ['as' => 'project.store']);
$routes->post('project/update/(:num)', 'Project::update/$1', ['as' => 'project.update']);
$routes->post('project/destroy/(:num)', 'Project::destroy/$1', ['as' => 'project.destroy']);

// Completion
$routes->get('project/complete/form/(:num)', 'Project::completeForm/$1', ['as' => 'project.complete.form']);
$routes->post('project/complete/(:num)', 'Project::complete/$1', ['as' => 'project.complete']);

// Sub-resources
$routes->get('project/(:num)/costs', 'Project::costsPartial/$1', ['as' => 'project.costs_partial']);
$routes->post('project/(:num)/cost/store', 'Project::storeCost/$1', ['as' => 'project.store_cost']);
$routes->post('project/cost/destroy/(:num)', 'Project::destroyCost/$1', ['as' => 'project.destroy_cost']);

$routes->get('project/(:num)/delivery-items', 'Project::deliveryItemsPartial/$1', ['as' => 'project.delivery_items_partial']);
$routes->post('project/(:num)/delivery-item/store', 'Project::storeDeliveryItem/$1', ['as' => 'project.store_delivery_item']);
$routes->post('project/delivery-item/destroy/(:num)', 'Project::destroyDeliveryItem/$1', ['as' => 'project.destroy_delivery_item']);

$routes->get('project/(:num)/payments', 'Project::paymentsPartial/$1', ['as' => 'project.payments_partial']);
$routes->post('project/(:num)/payment/store', 'Project::storePayment/$1', ['as' => 'project.store_payment']);
$routes->post('project/payment/destroy/(:num)', 'Project::destroyPayment/$1', ['as' => 'project.destroy_payment']);

// Documents (PDF download)
$routes->get('project/(:num)/invoice', 'Project::invoice/$1', ['as' => 'project.invoice']);
$routes->get('project/(:num)/delivery-note', 'Project::deliveryNote/$1', ['as' => 'project.delivery_note']);



service('auth')->routes($routes);