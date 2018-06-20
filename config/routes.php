<?php
use Cake\Routing\Router;

Router::plugin(
    'Qobo/Comments',
    ['path' => '/comments'],
    function ($routes) {
        $routes->fallbacks('DashedRoute');
    }
);
