<?php
function get_routes() {
   $str_routes = array();
   $routes = Cake\Routing\Router::routes();
   foreach($routes as $route) {
       $str_routes[] = $route->compile();
   }
   $str_routes = array_unique($str_routes);
   return $str_routes;
}


if(count($argv) <= 1) {
   print("Usage: {$argv[0]} path/to/index.php\n");
   die;
}


ob_start();
include $argv[1];
$output = ob_get_clean();


$routes = get_routes();
foreach($routes as $route) print("$route\n");
