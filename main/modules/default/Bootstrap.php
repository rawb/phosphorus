<?php
class Bootstrap_Default
{
    public function _initModels() {
        $paths = array(MAIN_PATH . "/modules/default/models/dbo");
        foreach($paths as $path){
            foreach (glob($path."/*.php") as $filename)
            {
                include_once  $filename;
            }
        }
        return null;
    }
}