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

    // will result in some usage information about the API
    $router->get('/', function() {
        echo 'This is an API.';
    });

    /* GET for reading all series */
    $router->get('/series', function() use ($db) {
        $series = get_series($db);
        $series_json = json_encode($series);
        echo $series_json;
    });

    /* GET for reading individual series */
    $router->get('/series/(\d+)', function($id) use ($db) {
        $series = get_serieinfo($db, $id);
        $series_json = json_encode($series);
        echo $series_json;
    });

    /* DELETE for deleting individual series */
    $router->delete('/series/(\d+)', function($id) use ($db) {
        $feedback = remove_serie($db, $id);
        $feedback_json = json_encode($feedback);
        echo $feedback_json;
    });

    /* POST for adding individual series */
    $router->post('/series/add', function() use ($db) {
        $serie_info = [
            'Name' => $_POST['name'],
            'Creator' => $_POST['creator'],
            'Seasons' => $_POST['seasons'],
            'Abstract' => $_POST['abstract']
        ];
        $feedback = add_serie($db, $serie_info);
        $feedback_json = json_encode($feedback);
        echo $feedback_json;
    });

});

/* 404 code */
$router->set404(function() {
    header('404 Page Not Found');
    echo '404';
});


/* Run the router */
$router->run();
