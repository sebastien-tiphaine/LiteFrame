<?php

// Loading required lib
LiteFrame_Loader::Library('LiteFrame_Session');

class LiteFrame_HttpRequest{

    // name of the controller
    protected $_strControllerName = false;
    
    // name of the action
    protected $_strActionName = false;

    // list of uri vars
    protected $_arrUriVars  = false;
    
    // session object
    protected $_oSession = null;
    
    // constructor
    public function __construct($strRequest = false){
    
        // extracts and set controller and action from request string
        $this->_extractCtrlAction($strRequest);
        $this->_extractUriParams();
        return $this;
    }

    // extract all vars pass in the uri after controller and action name
    protected function _extractUriParams(){
    
        // has data already been extracted
        if(is_array($this->_arrUriVars)){
            return $this;
        }
    
        // getting controller name
        $strCtrlName = $this->getControllerName();
    
        // do we have controller and action data extrated ?
        if(!is_string($strCtrlName) || empty($strCtrlName)){
            // controller data are not usable
            throw new Exception('Controller have not been set or extracted, before calling this method');
        }
    
        // removing controller and action data
        $arrDatas = explode('/', $_SERVER['SCRIPT_URL']);
    
        // removing first item as it's the first slash
        array_shift($arrDatas);
    
        // do we have to remove controller name
        if(strtolower($arrDatas[0]) == strtolower($this->getControllerName())){
            // yes
            array_shift($arrDatas);
        }
        
        // do we have to remove action name
        if(strtolower($arrDatas[0]) == strtolower($this->getActionName())){
            // yes
            array_shift($arrDatas);
        }
    
        // do we still have slash
        if(empty($arrDatas[0])){
            // yes
            array_shift($arrDatas);
        }
        
        // initializing uri vars
        $this->_arrUriVars = array();
    
        // do we still have vars
        if(empty($arrDatas)){
            // done
            return $this;
        } 
    
        // do we have a good number for params
        if(count($arrDatas)%2){
            // no
            return $this;
        }
    
        // flag for key extraction
        $mKey = false;
        
        foreach($arrDatas as $strValue){
            // are we extracting a key
            if($mKey === false){
                // yes
                $mKey = $strValue;
                // done
                continue;
            }
            
            // are values securized
            if(preg_match('/^([a-zA-Z0-9]+)$/', $mKey) && preg_match('/^([a-zA-Z0-9]+)$/', $strValue)){
                // yes
                // adding value to main array
                $this->_arrUriVars[$mKey] = $strValue;
            }
            
            // removing key
            $mKey = false;
        }
        // done
        return $this;
    }
    
    // return value of an uri param
    public function getUriParam($strName){
    
        // ensure that uri params have been extracted
        $this->_extractUriParams();
    
        // do we have a var ?
        if(!$this->hasUriParam($strName)){
            throw new Exception('No var name '.$strName.' in Uri !');
        }
        
        // done
        return $this->_arrUriVars[$strName];
    
    }
    
    // returns true if Uri var $strName exists
    public function hasUriParam($strName){
    
        // ensure that uri params have been extracted
        $this->_extractUriParams();
    
        // do we have a value for the var 
        if(!is_string($strName) || empty($strName) || 
           !array_key_exists($strName, $this->_arrUriVars)){
            // no
            return false;
        }
        
        // yes
        return true;
    }
    
    // return session object
    public function getSession(){
    
        if(!$this->_oSession instanceof LiteFrame_Session){
            $this->_oSession = new LiteFrame_Session();
        }
        
        return $this->_oSession;
    }
    
    // extract Controller and Action from a request
    protected function _extractCtrlAction($strRequest = false){
    
        // do we have a request string
        if(!is_string($strRequest) || empty($strRequest)){
            // no
            // getting data from server var
            $strRequest = $_SERVER['SCRIPT_URL'];
        }
    
        // setting default value
        $arrCtrlAction = array(
            'controller' => 'Index',
            'action'     => 'Index'
        );
        
        // do we have a controller and an action ?
        if(preg_match('/^\/([a-zA-Z0-9]+)\/([a-zA-Z0-9]+)/', $strRequest, $arrUriFiltered)){
            // yes
            // extracting values
            $arrCtrlAction['controller'] = $arrUriFiltered[1];
            $arrCtrlAction['action']     = $arrUriFiltered[2];
        }
        // do we have a controller only ?
        else if(preg_match('/^\/([a-zA-Z0-9]+)/', $strRequest, $arrUriFiltered)){
            // yes
            $arrCtrlAction['controller'] = $arrUriFiltered[1];
        }
        
        $this->setControllerName($arrCtrlAction['controller']);
        $this->setActionName($arrCtrlAction['action']);
             
        // done
        return $this;
    }
    
    // filter string to be used as controller or action name
    protected function _filterControllerActionString($strName){
    
        // is value usable ?
        if(!is_string($strName) || empty($strName) || !preg_match('/^[a-zA-Z0-9]+$/', $strName, $arrData)){
            // no
            throw new Exception('HttpRequest::_filterControllerActionString: invalid name or string');
        }
        
        // extracting value
        $strValue = array_shift($arrData);
    
        // done
        return ucfirst($strValue);
    }
    
    // set controller name
    public function setControllerName($strName){
    
        // setting controller name
        $this->_strControllerName = $this->_filterControllerActionString($strName);
        
        // done
        return $this;
    }
    
    // return current controller name
    public function getControllerName(){
        return $this->_strControllerName;
    }
   
    // set action name
    public function setActionName($strName){
    
        // setting action name
        $this->_strActionName = $this->_filterControllerActionString($strName);
        
        // done
        return $this;
    }
    
    // return current action name
    public function getActionName(){
        return $this->_strActionName;
    }
    
    // end all script with a 404 error
    public function send404(){
        
        // sending header
        header('HTTP/1.0 404 Not Found');
        die("Erreur 404 : la page demandée n'existe pas");
        // just in case :)
        exit;
    }
    
    // redirect to another controller/action
    public function redirect($strController = false, $strAction = false){
    
        // defining protocol
        $strProtoc = (isset($_SERVER['HTTPS']) && strtolower(trim($_SERVER['HTTPS'])) == 'on')? 'https':'http';
        // setting host with the protocol
        $strUrl = $strProtoc.'://'.$_SERVER['HTTP_HOST'].'/';
    
        // do we have a controlleur name
        if(is_string($strController) && !empty($strController)){
            // yes
            $strController = $this->_filterControllerActionString($strController);
            $strUrl.= $strController.'/';
            
            // do we have an action
            if(is_string($strAction) && !empty($strAction)){
                // yes
                $strAction = $this->_filterControllerActionString($strAction);
                $strUrl.= $strAction;
            }
        }
    
        // redirecting
        header('Location: '.strtolower($strUrl));
        exit;
    }
}
