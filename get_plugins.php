<?php
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


if(count($argv) <= 1) {
   print("Usage: {$argv[0]} path/to/index.php\n");
   die;
}


ob_start();
include $argv[1];
$output = ob_get_clean();
$app_vars = get_defined_vars();
unset($app_vars['output'], $app_vars['argv']);


print(json_encode(get_plugins()));