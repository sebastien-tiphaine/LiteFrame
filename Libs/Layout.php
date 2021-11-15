<?php

// Loading main class
LiteFrame_Loader::Library('LiteFrame_RenderingAbstract');
LiteFrame_Loader::Library('LiteFrame_Page');

class LiteFrame_Layout extends LiteFrame_RenderingAbstract{

    // template type
    protected $_strTemplateType = 'Layout';
    
    // Page
    protected $_oPage = null;
    
     // constructor that auto define the file name
    public function __construct($strName = 'Layout'){
        return parent::__construct($strName);
    }
    
    // sets the file name for the rendering
    public function setFileName($strFileName = false){
        return $this->_setFileName($strFileName);
    }
    
    // sets the page object
    public function setPage($oPage){
    
        if(!$oPage instanceof LiteFrame_Page){
            throw new Exception('LiteFrame_Layout::setPage : invalid page object set.');
        }
        
        $this->_oPage = $oPage;
    
        return $this;
    }
    
    // returns true if a page object is set
    public function hasPage(){
        
        if($this->_oPage instanceof LiteFrame_Page){
            return true;
        }
        
        return false;
    }
    
    // render and output the view
    public function thePage(){
        
        // do we have a view
        if(!$this->hasPage()){
            // no
            throw new Exception('LiteFrame_Layout::thePage : no page object set');
        }
    
        // output the view
        echo $this->_oPage->__toString();
        
        // done
        return $this;
    }
    
    // hook called by the parent class
    protected function _render(){
    
        if(!$this->hasPage()){
            throw new Exception('LiteFrame_Layout::_render : no view object set');
        }
        
        // done
        return $this;
    }
}
