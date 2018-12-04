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

/* Set the credentials */
$cred = set_cred('ddwt18', 'ddwt18');

/* Authentication */
$router->before('GET|POST|PUT|DELETE', '/api/.*', function() use ($cred){
    if (!check_cred($cred)){
        http_response_code(401);
        $feedback = [
            'type' => 'danger',
            'message' => 'Authentication required. Credentials incorrect.'
        ];
        $feedback_json = json_encode($feedback);
        echo $feedback_json;
        exit();
    }
});

/* Mount */
$router->mount('/api', function() use ($router, $db) {
    http_content_type("application/json");

    /* Some usage information about the API */
    $router->get('/', function() {
        $feedback = [
            'type' => 'info',
            'message' => 'This is an api for the series overview. You can get all series, individual series by id, delete series and update series.'
        ];
        $feedback_json = json_encode($feedback);
        echo $feedback_json;
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
    $router->post('/series/', function() use ($db) {
        $feedback = add_serie($db, $_POST);
        $feedback_json = json_encode($feedback);
        echo $feedback_json;
    });

    /* PUT for updating individual series */
    $router->put('/series/(\d+)', function($id) use ($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'),$_PUT);

        $serie_info = $_PUT + ["serie_id" => $id];
        $feedback = update_serie($db, $serie_info);
        $feedback_json = json_encode($feedback);
        echo $feedback_json;
    });

});

/* 404 code */
$router->set404(function() {
    header('404 Page Not Found');
    $feedback = [
        'type' => 'danger',
        'message' => 'Page not found.'
    ];
    $feedback_json = json_encode($feedback);
    echo $feedback_json;
});


/* Run the router */
$router->run();
