<?php
// get_routes command START
function get_routes() {
    $str_routes = array();
    $routes = Cake\Routing\Router::routes();
    foreach($routes as $route) {
        $str_routes[] = $route->compile();
    }
    $str_routes = array_unique($str_routes);
    return $str_routes;
}
// get_routes command END
// get_controllers command START
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
// get_controllers command END
// get_actions command START
function get_actions() {
    $result = array();
    $controllers = get_controllers();
    foreach($controllers as $controller) {
        $class = load_controller($controller);
        if ($class!==false && class_exists($class)) {
            $result[$controller] = get_controller_action($class);
        }
    }
    return $result;
}
function load_controller($controller) {
    $pluginPath = "";
    $namespace = "Controller";
    $className = Cake\Core\App::className($pluginPath . $controller, $namespace, $namespace);

    $reflection = new ReflectionClass($className);
    if ($reflection->isAbstract()) {
        return false;
    }
    if (class_exists($className)) {
        return $className;
    }
    return false;
}
function get_controller_action($controller_class) {
    $controllerObject = new $controller_class();
    $actions = array();
    $methods = get_class_methods($controllerObject);
    foreach($methods as $method) {
        if(is_controller_action($controllerObject, $method)) {
            $actions[] = $method;
        }
    }
    return $actions;
}
function is_controller_action($controllerObject, $action) {
    $baseClass = new ReflectionClass(new Cake\Controller\Controller);
    if ($baseClass->hasMethod($action)) {
        return false;
    }
    try {
        $reflectionMethod = new ReflectionMethod($controllerObject, $action);
    } catch (ReflectionException $e) {
        return false;
    }
    $objectReflection = new ReflectionClass($controllerObject);
    if($reflectionMethod->class !== $objectReflection->getName()) {
        return false;
    }
    return $reflectionMethod->isPublic() && $reflectionMethod->getName() === $action;
    
}
// get_actions command END
// get_plugins command START
function get_server_var() {
    global $app_vars;
    foreach($app_vars as $k=>$v) {
        if($v instanceof Cake\Http\Server) return $v;
    }
    return null;
}
function get_object_property($object, $property) {
    $object_reflection = new ReflectionClass($object);
    $property_reflection = $object_reflection->getProperty($property);
    $property_reflection->setAccessible(true);
    return $property_reflection->getValue($object);
}
function get_plugins() {
    $server = get_server_var();
    if(is_null($server)) return null;
    return get_object_property($server->getApp()->getPlugins(), 'names');
}
// get_plugins command END

$available_commands = array('get_routes', 'get_controllers', 'get_actions', 'get_plugins');
if(count($argv) <= 2 || !in_array($argv[2], $available_commands)) {
    print("Usage: {$argv[0]} path/to/index.php command\n");
    print(" Available commands: ".implode(", ",$available_commands)."\n");
    die;
}

ob_start();
include $argv[1];
$output = ob_get_clean();
$app_vars = get_defined_vars();
unset($app_vars['output'], $app_vars['argv']);

$result = $argv[2]();
print(json_encode($result));
