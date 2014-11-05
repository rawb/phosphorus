<?php
class Phosphorus_Core_Controller
{
    protected $_meta = null;
    protected $_viewData = null;
    protected $_output = null;
    protected $_action = null;
    public function __construct($action,$meta)
    {
        $this->_action = $action;
        $this->_meta = $meta;
        $classMethods = get_class_methods($this);
        if(in_array($action."Action",$classMethods)){
           call_user_func_array(array($this, $action."Action"),array($this->_meta));
        }else{
            throw new Exception('Action : ( '.$action.' ) does not exist.');
        }
        $this->render();
    }

    public function render()
    {
        $class = get_class($this);
        $formatted = str_replace('Controller', '', $class);
        $controllerName = strtolower($formatted);
        $content = $this->loadView(MAIN_PATH . "/modules/".$this->_meta["current_module"]."/views/".$controllerName."/".$this->_action.".php",$this->_viewData);
        $template = $this->loadView(MAIN_PATH . "/modules/".$this->_meta["current_module"]."/views/template.php",array("content"=>$content));
        $this->_output = $template;
    }

    public function loadView ($viewPath, $data = null)
    {
        extract($data);
        ob_start();
        require($viewPath);
        $stringResult = ob_get_contents();
        ob_end_clean();
        return $stringResult;
    }

    public function getMeta()
    {
        return $this->_meta;
    }

    public function getOutput()
    {
        return $this->_output;
    }

    public function setViewData($viewData)
    {
       $this->_viewData = $viewData;
    }
}
