<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');
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
    // GET Requests
    $routes->get('/',              'Debt::index',      ['as' => 'debts']);
    $routes->get('list',           'Debt::list',       ['as' => 'debts.list']);
    $routes->get('form',           'Debt::form',       ['as' => 'debt.form']);
    $routes->get('form/(:num)',    'Debt::form/$1',    ['as' => 'debt.edit']);

    // POST Requests
    $routes->post('store',          'Debt::store',      ['as' => 'debt.store']);
    $routes->post('update/(:num)',  'Debt::update/$1',  ['as' => 'debt.update']);
    $routes->post('delete/(:num)',  'Debt::delete/$1',  ['as' => 'debt.delete']);
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

    $routes->get('/', 'Transaction::index', ['as' => 'transactions']);
    $routes->get('list', 'Transaction::list', ['as' => 'transactions.list']);
    $routes->get('form', 'Transaction::form', ['as' => 'transaction.form']);      // create (optional ?parent_id=N)
    $routes->get('form/(:num)', 'Transaction::form/$1', ['as' => 'transaction.edit']);      // edit
    $routes->post('store', 'Transaction::store', ['as' => 'transaction.store']);
    $routes->post('update/(:num)', 'Transaction::update/$1', ['as' => 'transaction.update']);
    $routes->post('destroy/(:num)', 'Transaction::destroy/$1', ['as' => 'transaction.destroy']);});

service('auth')->routes($routes);