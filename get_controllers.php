<?php
function get_controllers() {
   $controllers = array();
   $c_const = 'Controller';
   $ext = '.php';


   $dirs = Cake\Core\App::classPath($c_const);
   foreach($dirs as $dir) {
       $paths = get_dir_content($dir);
       foreach($paths as $path) {
           if(strpos($path, $c_const.$ext) !== false) {
               $controllers[] = substr($path, strlen($dir), -strlen($c_const)-strlen($ext));
           }
       }
   }
   $controllers = array_unique($controllers);
   return $controllers;
}
function get_dir_content($dir, &$results = array()) {
   $files = scandir($dir);


   foreach ($files as $key => $value) {
       $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
       if (!is_dir($path)) {
           $results[] = $path;
       } else if ($value != "." && $value != "..") {
           get_dir_content($path, $results);
           $results[] = $path;
       }
   }


   return $results;
}


if(count($argv) <= 1) {
   print("Usage: {$argv[0]} path/to/index.php\n");
   die;
}


ob_start();
include $argv[1];
$output = ob_get_clean();


$controllers = get_controllers();
foreach($controllers as $controller) print("$controller\n");
