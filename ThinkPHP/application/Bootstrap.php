<?php 
class Bootstrap extends Yaf_Bootstrap_Abstract {

    private $_config;

    public function _initBootstrap(){
        $this->_config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set("config",  $this->_config);
    }
/*
    public function _initIncludePath(){
        set_include_path(get_include_path() . PATH_SEPARATOR . $this->_config->application->library);
    }
*/
    // ThinkPHP init
    public function _initThink(){
        // init storage type 初始化文件存储方式
        Storage::connect($this->_config->think->STORAGE_TYPE);
    }

    public function _initErrors(){
        if($this->_config->application->showErrors){
            error_reporting (-1);
            ini_set('display_errors','On');
        }
    }

}
