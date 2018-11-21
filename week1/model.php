<?php
/**
 * Model
 * Derived from model.php by user: reinardvandalen
 * Author: Rolf Daling, s2344343
 * Date: 18-11-18
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Connect to a MYSQL database
 * @param string $host
 * @param string $database
 * @param string $username
 * @param string $password
 * @return PDO
 */
function connect_db($host, $database, $username, $password){
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$database;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (\PDOException $e) {
        echo sprintf("Failed to connect. %s",$e->getMessage());
    }
    return $pdo;
}

/**
 * Counts the number of series in the database.
 * @return int Number of series
 */
function count_series($pdo){
    $stmt = $pdo->prepare("SELECT * FROM series");
    $stmt->execute();
    return $stmt->rowCount();
}

/**
 * Get the series from the table in an array.
 * @param $pdo
 * @return array
 */
function get_series($pdo){
    $stmt = $pdo->prepare('SELECT * FROM series');
    $stmt->execute();
    $series = $stmt->fetchAll();
    $series_exp = Array();
    /* Create array with htmlspecialchars */
    foreach ($series as $key => $value){
        foreach ($value as $user_key => $user_input) {
            $series_exp[$key][$user_key] = htmlspecialchars($user_input);
        }
    }
    return $series_exp;
}

/**
 * Gets one serie by its id.
 * @param $pdo
 * @param $id
 * @return mixed
 */
function get_series_info($pdo, $id){
    $stmt = $pdo->prepare("SELECT * FROM series WHERE id = '$id'");
    $stmt->execute();
    $serie = $stmt->fetch();
    return $serie;
}

/**
 * Gets the series and puts it in
 * @param $series
 * @return string The table.
 */
function get_serie_table($series){
    $table_exp = '
    <table class="table table-hover">
    <thead
    <tr>
    <th scope="col">Series</th>
    <th scope="col"></th>
    </tr>
    </thead>
    <tbody>';
    foreach($series as $key => $value){
        $table_exp .= '
        <tr>
        <th scope="row">'.$value['name'].'</th>
        <td><a href="/DDWT18/week1/serie/?serie_id='.$value['id'].'" role="button" class="btn btn-primary">More info</a></td>
        </tr>
        ';
    }
    $table_exp .= '
    </tbody>
    </table>
    ';
    return $table_exp;
}

/**
 * Adds a series to the database after checking if all the fields were correctly filled.
 * @param $pdo
 * @param $serie_info
 * @return array Feedback about the operation.
 */
function add_series($pdo, $serie_info){
    if (empty($serie_info['Name'])) {
        $feedback = ['type'=>'danger', 'message'=>'Name field empty. Series not added.'];
    } elseif (empty($serie_info['Creator'])) {
        $feedback = ['type'=>'danger', 'message'=>'Creator field empty. Series not added.'];
    } elseif (empty($serie_info['Seasons'])) {
        $feedback = ['type'=>'danger', 'message'=>'Seasons field empty. Series not added.'];
    } elseif (!is_numeric($serie_info['Seasons'])) {
        $feedback = ['type'=>'danger', 'message'=>'Seasons field is not numeric. Series not added.'];
    } elseif (empty($serie_info['Abstract'])) {
        $feedback = ['type'=>'danger', 'message'=>'Abstract field empty. Series not added.'];
    } elseif (check_series_name($pdo, $serie_info['Name'])) {
        $feedback = ['type'=>'danger', 'message'=>'Series name already in database. Series not added.'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO series (name, creator, seasons, abstract) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $serie_info['Name'],
            $serie_info['Creator'],
            $serie_info['Seasons'],
            $serie_info['Abstract']
        ]);
        $inserted = $stmt->rowCount();
        if ($inserted == 1) {
            $feedback = ['type'=>'success', 'message'=>'Series successfully added.'];
        } else {
            $feedback = ['type' => 'danger', 'message' => 'Error, series not added. Please try again.'];
        }
    }
    return $feedback;
}

/**
 * Check if a series with a specified name is in the database and returns the series ID if it does.
 * @param $pdo
 * @param $name
 * @return bool Series ID if already in database, else false.
 */
function check_series_name($pdo, $name) {
    $stmt = $pdo->prepare("SELECT * FROM series WHERE name = '$name'");
    $stmt->execute();
    $serie = $stmt->fetch();
    return ($serie ? $serie['id'] : False);
}

/**
 * Update a serie in the database after checking if all the fields were correctly filled.
 * Also checks if a name already occurs in the database by comparing the ID with the output from check_series_name().
 * @param $pdo
 * @param $serie_info
 * @return array Feedback about the operation.
 */
function update_series($pdo, $serie_info){
    if (empty($serie_info['Name'])) {
        $feedback = ['type'=>'danger', 'message'=>'Name field empty. Series not updated.'];
    } elseif (empty($serie_info['Creator'])) {
        $feedback = ['type'=>'danger', 'message'=>'Creator field empty. Series not updated.'];
    } elseif (empty($serie_info['Seasons'])) {
        $feedback = ['type'=>'danger', 'message'=>'Seasons field empty. Series not updated.'];
    } elseif (!is_numeric($serie_info['Seasons'])) {
        $feedback = ['type'=>'danger', 'message'=>'Seasons field is not numeric. Series not updated.'];
    } elseif (empty($serie_info['Abstract'])) {
        $feedback = ['type'=>'danger', 'message'=>'Abstract field empty. Series not updated.'];
    } elseif (check_series_name($pdo, $serie_info['Name']) != $serie_info['serie_id'] ) {
        $feedback = ['type'=>'danger', 'message'=>'Series name already in database. Series not updated.'];
    } else {
        $stmt = $pdo->prepare("UPDATE series SET name = ?, creator = ?, seasons = ?, abstract = ? WHERE id = ?");
        $stmt->execute([
            $serie_info['Name'],
            $serie_info['Creator'],
            $serie_info['Seasons'],
            $serie_info['Abstract'],
            $serie_info['serie_id']
        ]);
        $inserted = $stmt->rowCount();
        if ($inserted == 1) {
            $feedback = ['type'=>'success', 'message'=>'Series successfully updated.'];
        } else {
            $feedback = ['type' => 'danger', 'message' => 'Error, series not updated. Please try again.'];
        }
    }
    return $feedback;
}

/**
 * Removes a series from the database.
 * @param $pdo
 * @param $serie_info
 * @param $serie_id
 * @return array Feedback about the operation.
 */
function remove_serie($pdo, $serie_info, $serie_id) {
    $stmt = $pdo->prepare("DELETE FROM series WHERE id = ?");
    $stmt->execute([$serie_id]);
    $deleted = $stmt->rowCount();
    if ($deleted == 1) {
        return [ 'type' => 'success', 'message' => sprintf("Series '%s' was removed!", $serie_info['name']];
    }
    else {
        return [ 'type' => 'warning', 'message' => 'Error occurred. Series was not removed.'];
    }
}

/**
 * Check if the route exist
 * @param string $route_uri URI to be matched
 * @param string $request_type request method
 * @return bool
 *
 */
function new_route($route_uri, $request_type){
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
        return True;
    }
}

/**
 * Creates a new navigation array item using url and active status
 * @param string $url The url of the navigation item
 * @param bool $active Set the navigation item to active or inactive
 * @return array
 */
function na($url, $active){
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template filename of the template without extension
 * @return string
 */
function use_template($template){
    $template_doc = sprintf("views/%s.php", $template);
    return $template_doc;
}

/**
 * Creates breadcrumb HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '<nav aria-label="breadcrumb">';
    $breadcrumbs_exp .= '<ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]){
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        }else{
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '</ol>';
    $breadcrumbs_exp .= '</nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation HTML code using given array
 * @param array $navigation Array with as Key the page name and as Value the corresponding url
 * @return string html code that represents the navigation
 */
function get_navigation($navigation){
    $navigation_exp = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
    $navigation_exp .= '<a class="navbar-brand">Series Overview</a>';
    $navigation_exp .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $navigation_exp .= '<span class="navbar-toggler-icon"></span>';
    $navigation_exp .= '</button>';
    $navigation_exp .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $navigation_exp .= '<ul class="navbar-nav mr-auto">';
    foreach ($navigation as $name => $info) {
        if ($info[1]){
            $navigation_exp .= '<li class="nav-item active">';
            $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';
        }else{
            $navigation_exp .= '<li class="nav-item">';
            $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';
        }

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '</ul>';
    $navigation_exp .= '</div>';
    $navigation_exp .= '</nav>';
    return $navigation_exp;
}

/**
 * Pritty Print Array
 * @param $input
 */
function p_print($input){
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Creats HTML alert code with information about the success or failure
 * @param bool $type True if success, False if failure
 * @param string $message Error/Success message
 * @return string
 */
function get_error($feedback){
    $error_exp = '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
    return $error_exp;
}