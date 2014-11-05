<?php
//define environment and main application path
$environment = "production";
define('ENVIRONMENT',$environment);
define('MAIN_PATH', realpath(dirname(__FILE__) . '/../main'));
//add /library and /library to includes to clean up requires
set_include_path(dirname(dirname(__FILE__)) . '/library' . PATH_SEPARATOR . dirname(dirname(__FILE__)) . '/vendor' . PATH_SEPARATOR . get_include_path());
//Phosphorous core class includes
foreach (glob( dirname(dirname(__FILE__)) . '/library/phosphorus/core/*.php') as $filename)
{
    include_once  $filename;
}
//lets crunch baby
new Phosphorus_Core_Main();