<?php

class LiteFrame_Loader{

    // list of paths
    protected $_arrLibsPaths = array();
    
    // instance of Loader
    protected static $_oInstance = null;
    
    // constructor
    private function __construct(){}
    
    // Returns an instance of LiteFrame_Loader
    public static function getInstance(){
    
         // do we already have an instance of the current object
        if(!self::$_oInstance instanceof LiteFrame_Loader){
            // no
            self::$_oInstance = new LiteFrame_Loader();
        }
        
        // yes
        return self::$_oInstance;
    }
    
    // return a clean and secrure version of the class name
    protected function _sanitizeClassName($strName){
    
        // do we have a valid name for a class
        if(!is_string($strName) || empty($strName) ||
           !preg_match('/^[a-zA-Z0-9_]+$/', $strName, $arrName)){
            throw new Exception('LiteFrame_Loader::_sanitizeClassName invalid name given. Classname is empty or unsecure');
        }

        // extracting class name
        $strClassName = array_shift($arrName);
        
        // checking class name
        if(!is_string($strName) || empty($strName)){
            throw new Exception('LiteFrame_Loader::_sanitizeClassName invalid name given.');
        }
        
        // returning value
        return $strClassName;
    }
    
    // return class prefix
    protected function _getClassPrefix($strName){
    
        // extracting prefix
        if(!preg_match('/^[a-zA-Z0-9]+_/', $strName, $arrPrefix)){
            // no prefix found
            return '';
        }
        
        return array_shift($arrPrefix);
    }
    
    // return the filename for a given class
    protected function _getClassFileName($strName){
        
        // do we have a prefix
        if(preg_match('/^[a-zA-Z0-9]+_([a-zA-Z0-9]+)$/', $strName, $arrDatas)){
            // yes
            if(isset($arrDatas[1]) && !empty($arrDatas[1]) && is_string($arrDatas[1])){
                // returning file name
                return $arrDatas[1].'.php';
            }
        }
        
        // extracing simple class name
        if(preg_match('/^[a-zA-Z0-9]+$/', $strName, $arrDatas)){
            return array_shift($arrDatas).'.php';
        }
        
        // nothing found
        throw new Exception('LiteFrame_Loader: not able to extract class file name');
        
    }
    
    // shortcut to the Lib fonction
    public static function Library($strName){
        return self::getInstance()->Lib($strName);
    }
    
    // Load a library
    public function Lib($strName){
    
        // getting a clean version of the class
        $strClassName   = $this->_sanitizeClassName($strName);
        
        // is the class already loaded 
        if(class_exists($strClassName)){
            // yes
            return $this;
        }
        
        // getting class datas
        $strClassPrefix = $this->_getClassPrefix($strClassName);
        $strFileName    = $this->_getClassFileName($strClassName);
    
        // do we have a LiteFrame Lib 
        if($strClassPrefix === 'LiteFrame_'){
            // yes
            // do we have a path defined 
            if(!defined('LITEFRAME_LIBS_PATH') || empty(LITEFRAME_LIBS_PATH) || !is_dir(LITEFRAME_LIBS_PATH)){
                throw new Exception('LiteFrame_Loader::Lib: LITEFRAME_LIBS_PATH is not defined or is not a valid directory');
            }
            
            // loading file
            require_once(LITEFRAME_LIBS_PATH.'/'.$strFileName);
        }
        else{
            // no
            // do we have a path defined 
            if(!defined('LIBS_PATH') || empty(LIBS_PATH) || !is_dir(LIBS_PATH)){
                throw new Exception('LiteFrame_Loader::Lib: LIBS_PATH is not defined or is not a valid directory');
            }
            // loading file
            require_once(LIBS_PATH.'/'.$strFileName);
        }
        
        // is the class loaded 
        if(!class_exists($strClassName)){
            // no
            throw new Exception('LiteFrame_Loader::Lib: Class '.$strClassName.' not found in file '.$strFileName);
        }
        
        // done
        return $this;
    }
    
     // shortcut to the Ctrl fonction
    public static function Controller($strName){
        return self::getInstance()->Ctrl($strName);
    }
    
    // Load a controller and return the class name
    // return if the controller does not exists.
    public function Ctrl($strName){
    
        // getting a clean version of the class
        $strClassName       = $this->_sanitizeClassName($strName);
        $strControllerClass = $strClassName;
        
        // is the word controller contained
        if(preg_match('/^([a-zA-Z0-9]+)Controller$/', $strClassName, $arrData)){
            // yes
            $strClassName       = $arrData[1];
            $strControllerClass = $arrData[0];
        }
        else{
            // no
            $strControllerClass = $strControllerClass.'Controller';
        }
        
        // is the class already loaded 
        if(class_exists($strControllerClass)){
            // yes
            return $this;
        }
        
        // getting class datas
        $strFileName    = $this->_getClassFileName($strClassName);
    
        // no
        // do we have a path defined 
        if(!defined('CONTROLLERS_PATH') || empty(CONTROLLERS_PATH) || !is_dir(CONTROLLERS_PATH)){
            throw new Exception('LiteFrame_Loader::Ctrl: CONTROLLERS_PATH is not defined or is not a valid directory');
        }
        
        // do the controller exists
        if(!file_exists(CONTROLLERS_PATH.'/'.$strFileName)){
            // no
            return false;
        }
        
        // loading file
        require_once(CONTROLLERS_PATH.'/'.$strFileName);
        
        // is the class loaded 
        if(!class_exists($strControllerClass)){
            // no
            throw new Exception('LiteFrame_Loader::Ctrl: Class '.$strControllerClass.' not found in file '.CONTROLLERS_PATH.'/'.$strFileName);
        }
        
        // done
        return $strControllerClass;
    }
    
    // Load a view
    public function View($strName){
    
    }
}
