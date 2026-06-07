<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Home::index');

$routes->get('Parties',              'Party::index',      ['as' => 'parties']);
$routes->get('parties/list',         'Party::list',       ['as' => 'parties.list']);
$routes->get('parties/form',         'Party::form',       ['as' => 'party.form']);
$routes->get('parties/form/(:num)',  'Party::form/$1',    ['as' => 'party.edit']);

$routes->post('parties/store',            'Party::store',       ['as' => 'party.store']);
$routes->post('parties/update/(:num)',    'Party::update/$1',   ['as' => 'party.update']);
$routes->post('parties/delete/(:num)',    'Party::delete/$1',   ['as' => 'party.delete']);

service('auth')->routes($routes);