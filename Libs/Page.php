<?php

// Loading main class
LiteFrame_Loader::Library('LiteFrame_RenderingAbstract');
LiteFrame_Loader::Library('LiteFrame_View');

class LiteFrame_Page extends LiteFrame_RenderingAbstract{

     // template type
    protected $_strTemplateType = 'Page';
    
    // view
    protected $_oView = null;
    
    // constructor that auto define the file name
    public function __construct($strName = 'Page'){
        return parent::__construct($strName);
    }
    
    // sets the file name for the rendering
    public function setFileName($strFileName = false){
        return $this->_setFileName($strFileName);
    }
    
    // sets the view object
    public function setView($oView){
    
        if(!$oView instanceof LiteFrame_View){
            throw new Exception('LiteFrame_Page::setView : invalid view object set.');
        }
        
        $this->_oView = $oView;
    
        return $this;
    }
    
    // returns true if a view object is set
    public function hasView(){
        
        if($this->_oView instanceof LiteFrame_View){
            return true;
        }
        
        return false;
    }
    
    // render and output the view
    public function theView(){
        
        // do we have a view
        if(!$this->hasView()){
            // no
            throw new Exception('LiteFrame_Page::theView : no view object set');
        }
    
        // output the view
        echo $this->_oView->__toString();
        
        // done
        return $this;
    }
    
    // hook called by the parent class
    protected function _render(){
    
        if(!$this->hasView()){
            throw new Exception('LiteFrame_Page::_render : no view object set');
        }
        
        // done
        return $this;
    }
}
