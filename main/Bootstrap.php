<?php
class Bootstrap_Main
{    	 
	public function _initDb($meta) {
        $config = $meta["config"];
        $handler = new PDO('mysql:host='.$config["db_hostname"].';dbname='.$config["db_schema"], $config["db_user"], $config["db_pass"]);
        if(!$handler){
            throw new Exception('Could not connect to database');
        }
        return array('db_handler'=>$handler);
	}
}