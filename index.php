<?php

require 'loader.php';

include_once 'Libs/Request.php';
include_once 'Libs/Router.php';

$router = new Router(new Request);

$router->get('/', 'PriceIntervalsController:index');
$router->get('/priceInterval/all', 'PriceIntervalsController:all');
$router->post('/priceInterval/insert', 'PriceIntervalsController:insert');
$router->delete('/priceInterval/delete/$id', 'PriceIntervalsController:delete');
$router->delete('/priceInterval/reset', 'PriceIntervalsController:reset');
$router->put('/priceInterval/update', 'PriceIntervalsController:update');
