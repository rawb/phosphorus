<?php
require_once ('/austinhyde/iniparser/src/IniParser.php');
class Phosphorus_Core_Main
{
    //stores configs and shared objects
    protected $_meta = array();
    //modules in application
    protected $_modules = array();
    //routing parameters from request
    protected $_routingParameters = array();
    protected $_defaultModulePath = "";
    protected $_controllerPath = "";
    protected $_controllerNames = array();
    protected $_currentModule = null;

    //initialize application and handle request
    public function __construct()
    {
        try{
            $this->setRoutingParameters();
            $this->setModules();
            $this->setPaths();
            $this->setControllerNames();
            $this->setConfigBasedOnEnvironment();
            $this->bootstrap();
            $this->routeRequest();
        } catch (Exception $e) {
            $this->handleException($e);

        }
    }
    function routeRequest() {
        if(count($this->_routingParameters) == 1){
            $this->handleOneRoutingParameter();
        }elseif(count($this->_routingParameters) == 2){
            $this->handleTwoRoutingParameters();
        }elseif(count($this->_routingParameters) == 3){
            $this->handleThreeRoutingParameters();
        }else{
            //not accepted, just hit default/index
            $this->bootstrap("default");
            $this->initializeController("index","index");
        }
    }

    function handleOneRoutingParameter() {
        //hit index controller of module
        if($this->checkModuleExists($this->_routingParameters[0])){
            $this->bootstrap($this->_routingParameters[0]);
            $this->initializeController("index","index");
        }else{
            throw new Exception('Unknown Route - Attempted to match module name');
        }
    }

    function handleTwoRoutingParameters() {
        //default module assumed with controller/action route
        if($this->checkControllerExists($this->_routingParameters[0])){
            $this->bootstrap("default");
            $this->initializeController(strtolower($this->_routingParameters[0]),$this->_routingParameters[1]);
        }else{
            //or index action of controller
            if($this->checkModuleExists($this->_routingParameters[0])){
                if($this->checkControllerExists($this->_routingParameters[1])){
                    $this->bootstrap($this->_routingParameters[0]);
                    $this->initializeController(strtolower($this->_routingParameters[1]),"index");
                }else{
                    throw new Exception('Unknown Route - Attempted to match module / index action');
                }
            }else{
                throw new Exception('Unknown Route - Attempted to match default module with action');
            }
        }
    }

    function handleThreeRoutingParameters() {
        //module controller action
        if($this->checkControllerExists($this->_routingParameters[1])){
            $this->bootstrap($this->_routingParameters[0]);
            $this->initializeController(strtolower($this->_routingParameters[1]),$this->_routingParameters[2]);
        }else{
            throw new Exception('Unknown Route - Attempted to match module / controller / action');
        }
    }


    function checkControllerExists($controllerName) {
        if(in_array($controllerName,$this->_controllerNames)){
            return true;
        }else{
            return false;
        }
    }

    function checkModuleExists($moduleName) {
        if(in_array($moduleName,$this->_modules)){
            return true;
        }else{
            return false;
        }
    }

    function setUrlParameters() {
        //set parameters
        $this->_meta["url_parameters"]["GET"] = $_GET;
        $this->_meta["url_parameters"]["POST"] = $_POST;
    }

    function initializeController($controllerName,$actionName) {
        $this->includeDirectory($this->_controllerPath);
        $controller =  ucFirst($controllerName)."Controller";
        $controller = new $controller($actionName,$this->_meta);
        $this->cleanup($controller);
    }

    function handleException($e) {
        if($this->_currentModule != null){
            $this->bootstrap($this->_currentModule);
        }else{
            $this->bootstrap("default");
        }
        $this->_meta["url_parameters"]["GET"]["error"] = $e;
        $this->initializeController("error","error");
    }

    function setModules() {
        $modules = $this->getFileSystem(MAIN_PATH . "/modules/","folder");
        if(!in_array("default",$modules)){
            throw new Exception('Must have "default" module');
        }

        $this->_modules = $modules;
    }

    function setPaths() {
        $this->_defaultModulePath = MAIN_PATH . "/modules/default/controllers";
        if(in_array($this->_routingParameters[0],$this->_modules)){
            $this->_controllerPath = MAIN_PATH . "/modules/".$this->_routingParameters[0]."/controllers";
        }else{
            $this->_controllerPath = MAIN_PATH . "/modules/default/controllers";
        }
    }

    function setControllerNames() {
        $names = $this->getFileSystem($this->_controllerPath,"file");
        $this->_controllerNames = $this->formatControllerList($names);
        if(!count($this->_controllerNames) > 0){
            throw new Exception('Must have controllers in module'.$this->_currentModule);
        }
    }
	
	function setConfigBasedOnEnvironment() {
		$environment = (string) ENVIRONMENT;
        $parser = new IniParser(MAIN_PATH . "/config/main.ini");
        $config = $parser->parse();
        $this->_meta["config"] = $config["main"];
        foreach($config as $name => $node){
            $imploded = explode(":",$name);
            $stripped = str_replace(' ', '', $imploded[0]);
            if($stripped == $environment){
                $this->_meta["config"] = $node;
                break;
            }
        }
	}

	function bootstrap($module = null) {
		if($module){
            if(!in_array($module,$this->_modules)){
                throw new Exception('Module does not exist');
            }
			//pick up module
            $path = MAIN_PATH . "/modules/".$module."/Bootstrap.php";
            $postfix = ucfirst($module);
            $this->_meta["current_module"] = $module;
            $this->_currentModule = $module;
		}else{
			$path = MAIN_PATH . "/Bootstrap.php";
			$postfix = "Main";
		}
        require_once $path;
		$className = "Bootstrap_".$postfix;
		if (!class_exists($className, false)) {
			throw new Exception('Bootstrap class not found. ( Bootstrap_'.$postfix.' )');
		}
        $bootstrap = new $className();
		$classMethods = get_class_methods($bootstrap);
		foreach ($classMethods as $methodName) {
			$result = call_user_func_array(array($className, $methodName),array($this->_meta));
			if(!is_null($result)){
                $this->_meta += $result;
			}
		}
	}

    public function formatControllerList($controllerList){
        $controllerNames = array();
        if(count($controllerList) > 0){
            foreach($controllerList as $name){
                $formatted = str_replace('.php', '', $name);
                $formatted = str_replace('Controller', '', $formatted);
                $controllerNames[] = strtolower($formatted);
            }
        }
        return $controllerNames;
    }

    public function setRoutingParameters(){
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $routingParams = preg_split('@/@', $path, NULL, PREG_SPLIT_NO_EMPTY);
        $this->_routingParameters =  $routingParams;
    }

    public function getFileSystem($path,$type){
        $items = array();
        $dirHandle = opendir($path);
        while($item = readdir($dirHandle)){
            if($type == "file"){
                if(!is_dir($path . $item) && $item != '.' && $item != '..'){
                    $items[] = $item;
                }
            }elseif($type == "folder"){
                if(is_dir($path . $item) && $item != '.' && $item != '..'){
                    $items[] = $item;
                }
            }
        }
        return $items;
    }
	
	function cleanup($controller) {
        echo $controller->getOutput();
        $this->_meta["db_handler"] = null;
        exit();
	}

    function includeDirectory($path) {
        foreach (glob($path."/*.php") as $filename)
        {
            include_once  $filename;
        }
    }
}
