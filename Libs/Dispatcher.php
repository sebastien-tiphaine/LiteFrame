<?php

// Loading required lib
LiteFrame_Loader::Library('LiteFrame_HttpRequest');
LiteFrame_Loader::Library('LiteFrame_ControllerAbstract');

// translate a script uri to a controller and action call
class LiteFrame_Dispatcher{
    
    // instance of current request
    protected $_oHttpRequest = null;

    // constructor
    public function __construct($oHttpRequest = null){
    
        // do we have a request object
        if(!$oHttpRequest instanceof LiteFrame_HttpRequest){
            // no creating a new one
            $oHttpRequest = new LiteFrame_HttpRequest();
        }
    
        // setting request
        $this->_oHttpRequest = $oHttpRequest;

        // done
        return $this;
    }
    
    // send the request to the controller
    public function dispatch(){
    
        // getting controller
        $strControllerClassName = LiteFrame_Loader::Controller($this->_oHttpRequest->getControllerName());
        
        // checking controller
        if(!is_string($strControllerClassName) || empty($strControllerClassName)){
            // Controller not found 
            // sendind 404
            $this->_oHttpRequest->send404();
            // end of all scripts
        }
        
        // building controller
        $oController = new $strControllerClassName($this->_oHttpRequest);
        
        // checking controller class
        if(!$oController instanceof LiteFrame_ControllerAbstract){
            throw new Exception('LiteFrame_Dispatcher::dispatch: Controller '.$strControllerClassName.' does not extend LiteFrame_ControllerAbstract');
        }
       
        // getting action
        $strAction = $this->_oHttpRequest->getActionName();

        // checking action
        if(!is_string($strAction) || empty($strAction)){
            throw new Exception('LiteFrame_Dispatcher::dispatch: HttpReququest does not have any valid action');
        }
        
        // do we have a method to call ?
        if(!$oController->hasAction($strAction)){
            // sendind 404
            $this->_oHttpRequest->send404();
            // end of all scripts
        }
        
        // rendering
        $oController->render();
        
        // done
        return $this;
    }
}
