<?php

// Loading required lib
LiteFrame_Loader::Library('LiteFrame_HttpRequest');
LiteFrame_Loader::Library('LiteFrame_View');
LiteFrame_Loader::Library('LiteFrame_Layout');
LiteFrame_Loader::Library('LiteFrame_Page');

abstract class LiteFrame_ControllerAbstract{

    // instance of LiteFrame_HttpRequest
    protected $_oHttpRequest = null;

    // constructor
    public function __construct($oHttpRequest){
        
        // checking object
        if(!$oHttpRequest instanceof LiteFrame_HttpRequest){
            throw new Exception('LiteFrame_ControllerAbstract::__construct: Given Request object is not an instance of HttpReame_HttpRequest');
        }
        
        $this->_oHttpRequest = $oHttpRequest;
        
        // done
        return $this;
    }
    
    // return attached Request object
    public function getHttpRequest(){
        return $this->_oHttpRequest;
    }
    
    protected function _getActionMethodName($strActionName){
    
        return ucfirst($this->_filterActionName($strActionName).'Action');
    }
    
    // return true if the string is valid
    protected function _validateString($strString){
     
        if(!is_string($strString) || empty($strString) || !preg_match('/^[a-zA-Z0-9]+$/', $strString)){
            return false;
        }
     
        return true;
    }
    
    // returns a filtered version of an action name
    protected function _filterActionName($strActionName){
    
        if(!$this->_validateString($strActionName)){
            throw new Exception('LiteFrame_ControllerAbstract::_filterActionName: invalid action name. String expected');
        }
        
        return $strActionName;
    }
    
    // returns true if controller has an action 
    public function hasAction($strActionName = false){
        
        return method_exists($this, $this->_getActionMethodName($strActionName));
    }

    // call an action and output the content
    public function render(){
    
        // getting action
        $strActionName = $this->_oHttpRequest->getActionName();
    
        if(!$this->hasAction($strActionName)){
            throw new Exception('LiteFrame_ControllerAbstract::callActionMethod: controller does not have a action named : '.$strActionName);
        }
    
        // getting method name 
        $strActionMethodName = $this->_getActionMethodName($strActionName);
    
        // creation view object
        $oView = new LiteFrame_View($this->_filterActionName($strActionName), $this->_oHttpRequest->getControllerName());
        // creating Layout object
        // Note : this could be changed inside the crontroller action
        $oLayout = new LiteFrame_Layout();
        // creating Page object using controller name as file name
        // Note : this could be changed inside the crontroller action
        $oPage = new LiteFrame_Page($this->_oHttpRequest->getControllerName());
        
        // calling controller method
        $this->$strActionMethodName($oView, $oPage, $oLayout);
        
        // setting view to the page object
        $oPage->setView($oView);
        
        // setting the page to the layout object
        $oLayout->setPage($oPage);
        
        // output the content
        $oLayout->__toString();
        
        return $this;
    }

}
