<?php

// loading required lib
LiteFrame_Loader::Library('LiteFrame_Form');

abstract class LiteFrame_RenderingAbstract{

    // name of current rendering phtml file
    protected $_strFileName = false;
    
    // list of variable
    protected $_arrVars = array();
    
    // must be defined by the extender class
    protected $_strTemplateType = false;
    
    // sub folder
    protected $_strFileFolderName = false;
    
    // list of forms
    protected $_arrForms = array();
    
    // constructor
    // $strName set the file name
    public function __construct($strName = false, $strFolderName = false){
        
        // setting file name
        $this->_setFileName($strName);
        
        // do we have a folder name
        if(is_string($strFolderName) && !empty($strFolderName)){
             $this->_validateString($strFolderName);
             $this->_strFileFolderName = $strFolderName;
        }
        
        // done
        return $this;
    }
    
    // check string security.
    protected function _validateString($strString){
    
         if(!is_string($strString) || empty($strString) || !preg_match('/^[a-zA-Z0-9]+$/', ($strString)))   {
            throw new Exception('LiteFrame_RenderingAbstract::_filterString: invalid param. String expected.');
        }
        
        return true;
    }
    
    // sets the file name
    protected function _setFileName($strFileName = false){
    
        // checking name
        $this->_validateString($strFileName);
            
        // setting name
        $this->_strFileName = $strFileName;
        
        // done
        return $this;
    }
    
    // set a variable to the current object
    public function __set($strName, $mValue){
        
        // checking name
        $this->_validateString($strName);
        
        $this->_arrVars[$strName] = $mValue;
        
        return $this;
    }
    
    // returns a variable
    public function __get($strName){
    
        // checking name
        $this->_validateString($strName);
    
        // do we have a variable named $strName
        if(!array_key_exists($strName, $this->_arrVars)){
            // no
            throw new Exception('LiteFrame_View::__get: No variable: '.$strName);
        }
        
        return $this->_arrVars[$strName];
    }
    
    // returns true if a variable exists
    public function __isset($strName){
        
         if(!is_string($strName) || empty($strName) || !preg_match('/^[a-zA-Z0-9]+$/', $strName)){
            return false;
        }
        
        return array_key_exists($strName, $this->_arrVars);
    }
    
    // display the current url
    public function theUrl(){
        echo $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_URL'];
        return $this;
    }
    
    // includes a sub template
    public function includePartial($strName, $strSubPath = 'Partials'){
    
        // is output buffurig started ?
        if(!ob_get_level()){
            throw new Exception('Function called out of template rendering env.');
        }
    
        // getting template path
        $strTemplatePath = $this->_getTemplatePath($strName, $strSubPath);
        // including template
        require($strTemplatePath);
        // done
        return $this;
    }
    
    // output template content
    public function __toString(){
        echo $this->render();
    }
    
    // return the form object attached with $strName or create a new form 
    public function getForm($strName){
    
        // do we already have a form
        if(!$this->hasForm($strName)){
            // no
            $this->_arrForms[$strName] = new LiteFrame_Form($strName);
        }
        
        return $this->_arrForms[$strName]; 
    }
    
    // return true if the form $strName exists
    public function hasForm($strName){
    
        // checking form name
        $this->_validateString($strName);
    
        // do we have a form
        if(isset($this->_arrForms[$strName]) && $this->_arrForms[$strName] instanceof LiteFrame_Form){
            // yes
            return true;
        }   
        
        // no
        return false;
    }

    // returns full path for the template $strName
    protected function _getTemplatePath($strName, $strSubPath = false){
    
        // checking main templating path
        if(!defined('TEMPLATING_PATH') || !is_dir(TEMPLATING_PATH)){
            throw new Exception(TEMPLATING_PATH.' is not a valid directory');
        }
        
        // checking template type
        $this->_validateString($this->_strTemplateType);
        
        // setting template path:
        $strTemplatePath = TEMPLATING_PATH.'/'.$this->_strTemplateType.'s/';
        
        //do we have a folder name
        if(isset($this->_strFileFolderName) && !empty($this->_strFileFolderName)){
            // inserting folder name
            $strTemplatePath.= $this->_strFileFolderName.'/';
        }
        
        // do we have a sub path ?
        if(is_string($strSubPath) && !empty($strSubPath)){
            // yes
            // checking path
            $this->_validateString($strSubPath);
            // adding sub path
            $strTemplatePath.= $strSubPath.'/';
        }
        
        // checking template name
        $this->_validateString($strName);
        
        // setting template path:
        $strTemplatePath.= $strName.'.phtml';
        
        if(!file_exists($strTemplatePath)){
            throw new Exception('Template not found : '.$strTemplatePath); 
        }
        
        // done
        return $strTemplatePath;
    }
    
    // render the template and return it content as a string
    public function render(){
        
        // getting template path
        $strTemplatePath = $this->_getTemplatePath($this->_strFileName);
        
        // calling extender hook
        $this->_render();
        
        // starting output buffuring
        ob_start();
        // executing template content
        require($strTemplatePath);
        // getting content
        $strContent = ob_get_contents();
        // cleaning buffer
        ob_end_clean();
        // returuns the content
        return $strContent;
    }
    
    // function call on runtime. Must be defined by the extender class.
    // use it to apply change before template rendering
    abstract protected function _render();
}
