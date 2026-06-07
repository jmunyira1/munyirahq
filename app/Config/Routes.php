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
service('auth')->routes($routes);