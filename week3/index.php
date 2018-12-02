<?php
/**
 * Controller
 * User: reinardvandalen
 * Date: 05-11-18
 * Time: 15:25
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt18_week3', 'ddwt18', 'ddwt18');

/* Create Router instance */
$router = new \Bramus\Router\Router();

// Add routes here

/* Mount */
$router->mount('/api', function() use ($router, $db) {
    http_content_type("application/json");

    // will result in '/series/'
    $router->get('/', function() use ($db) {
        echo 'series overview';
    });

    // will result in '/series/id'
    $router->get('/(\d+)', function($id) {
        echo 'movie id ' . htmlentities($id);
    });
});

/* 404 code */
$router->set404(function() {
    header('404 Page Not Found');
    echo '404';
});


/* Run the router */
$router->run();
