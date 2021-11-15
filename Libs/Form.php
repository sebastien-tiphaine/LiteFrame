<?php

// class used to manipulate dates
class LiteFrame_Form{ 

    // list of form elements
    protected $_arrElements = array();
    
    // form name
    protected $_strName = false;
    
    // constructor
    public function __construct($strName){
    
        // checking string
        $this->_validateString($strName);
        
        // setting name
        $this->_strName = $strName;
        
        // done
        return $this;
    }
    
    // throws an exception if the string is not valid
    protected function _validateString($strString){
    
        // checking string
        if(!is_string($strString) || empty($strString) || !preg_match('/^[a-zA-Z0-9\._\s]+$/', $strString)){
            throw new Exception('LiteFrame_Form: invalid string !');
        }
        
        // done
        return $this;
    }
    
    // returns the form cryted name
    protected function _getElementValidationName($strName){
    
        // checking string
        $this->_validateString($strName);
        
        return md5('LITE_FRAME_'.$strName.'erofuhfjncnlljk539kirnfi8492ptivm&irnç%orjmwpkgitsjvmoei87026sd9cd$!djw+2*#$dua'.$this->_strName);
    }
    
    // returns true if form has an element named $strName
    public function hasElement($strName){
    
        // checking string
        $this->_validateString($strName);
        
        return array_key_exists($strName, $this->_arrElements);
    }
    
    // adds a form element
    public function addElement($strName, $strType = 'text', $strValue = false, $arrParams = array()){
    
        // do we already have this element ?
        if($this->hasElement($strName)){
            throw new Exception('LiteFrame_Form::addElement : an element with the same name is already set : '.$strName);
        }
        
        // checking type
        $this->_validateString($strType);
    
        // setting tag name
        $strTag = 'input';
        
        // setting tag
        switch(strtolower(trim($strType))){
            case 'button':
                $strTag = 'button';
                break;
            case 'submit':
                $strTag = 'input';
                break;
            case 'textarea':
                $strTag = 'textarea';
                break;
            case 'dayselect':
            case 'monthselect':
            case 'yearselect':
            case 'select':
                $strTag = 'select';
                break;
            case 'password':
            case 'checkbox':
            case 'text':                
            default:
                $strTag = 'input';
        }
    
        // creating element
        $this->_arrElements[$strName] = array(
            'tag'   => $strTag,
            'type'  => strtolower(trim($strType)),
            'name'  => $this->_getElementValidationName($strName),
            'value' => '',
            'params' => array(
                'class' => 'LiteFrame_Form_Element LiteFrame_Form_Element_'.strtolower(trim($strType)).' LiteFrame_Form_Element_'.$strName,
            )
        );
        
        // do we have to initialize options
        if($strTag == 'select'){
            // yes
            $this->_arrElements[$strName]['options'] = array();
            
            // do we have to fill the options
            if(strtolower(trim($strType)) == 'dayselect'){
                // inserting datas
                for($intDay = 1; $intDay < 32; $intDay++){
                    $this->setOption($strName, $intDay);
                }
                // setting value
                if($strValue === false ){
                    $strValue = intval(date('d'));
                }
            }
            
            if(strtolower(trim($strType)) == 'monthselect'){
                // inserting datas
                for($intMonth = 1; $intMonth < 13; $intMonth++){
                    $this->setOption($strName, $intMonth);
                }
                // setting value
                if($strValue === false ){
                    $strValue = intval(date('m'));
                }
            }
            
            if(strtolower(trim($strType)) == 'yearselect'){
                // inserting datas
                for($intYear = (intval(date('Y'))-3); $intYear < (intval(date('Y'))+5); $intYear++){
                    $this->setOption($strName, $intYear);
                }
                // setting value
                if($strValue === false ){
                    $strValue = intval(date('Y'));
                }
            }
        }
        
        // setting element value
        $this->setElementValue($strName, $strValue);
        // adding parama
        $this->setElementParam($strName, $arrParams);
        
        // done
        return $this;
    }
    
    // sets the value of an element
    public function setElementValue($strName, $strValue){
    
        // do we have this element ?
        if(!$this->hasElement($strName)){
            throw new Exception('LiteFrame_Form::setElementValue : no element named : '.$strName);
        }
        
        // do we have to empty the value
        if((!is_string($strValue) && !is_numeric($strValue) && $strValue !== 0 && $strValue !== '0') || empty($strValue)){
            // yes
            $strValue = '';
        }
        
        // setting value
        $this->_arrElements[$strName]['value'] = $strValue;
        
        // done
        return $this;
    }
    
    // returns the value of an element
    public function getElementValue($strName){
    
        // do we have this element ?
        if(!$this->hasElement($strName)){
            throw new Exception('LiteFrame_Form::getElementValue : no element named : '.$strName);
        }
        
        return $this->_arrElements[$strName]['value'];
    }
    
    // use a closure to validate an element value
    // returns true if value has been validated.
    public function isValid($strName, Closure $cloFunction){
        
        // does this element exits
        if(!$this->hasElement($strName)){
            throw new Exception('LiteFrame_Form::isValid : no element named : '.$strName);
        }
        
        // calling closure
        if($cloFunction($this->getElementValue($strName))){
            // value is valid
            return true;
        }
        
        // value is not valid
        return false;
    }
    
    public function setOption($strName, $mValue, $strDisplayValue = false){
    
        // do we have this element ?
        if(!$this->hasElement($strName)){
            throw new Exception('LiteFrame_Form::setOption : no element named : '.$strName);
        }
        
        // is element a select
        if($this->_arrElements[$strName]['tag'] !== 'select'){
            throw new Exception('LiteFrame_Form::setOption : element is not a select : '.$strName);
        }
        
        // do we have to set multiple options at once
        if(is_array($mValue)){
            // yes
            foreach($mValue as $strKey => $strDisplayValue){
                $this->setOption($strName, $strKey, $strDisplayValue);
            }
            // done
            return $this;
        }
        
        // do we have a different display value
        if(!$strDisplayValue){
            // no
            $strDisplayValue = $mValue;
        }
        
        // setting option
        $this->_arrElements[$strName]['options'][$mValue] = $strDisplayValue;
        // done
        return $this;
    }
    
    // add or set a param to an element
    public function setElementParam($strName, $mParamName = false, $strParamValue = false){
    
        // do we have this element ?
        if(!$this->hasElement($strName)){
            throw new Exception('LiteFrame_Form::addElementParam : no element named : '.$strName);
        }
        
        // do we have multiple params to set at once
        if(is_array($mParamName)){
            // yes
            foreach($mParamName as $strParamName => $strParamValue){
                // adding param
                $this->setElementParam($strName. $strParamName, $strParamValue);
            }
            // done
            return $this;
        }
        
        // checking param name
        $this->_validateString($mParamName);
        
        // do we have to remove the param
        if($strParamValue === false){
            // yes
            if(array_key_exists($mParamName,  $this->_arrElements[$strName]['params'])){
                unset($this->_arrElements[$strName]['params'][$mParamName]);
            }
            
            // done
            return $this;
        }
        
        // checking value
        $this->_validateString($strParamValue);
        
        // do we have a class or style element
        if((strolower($mParamName) == 'class' || strolower($mParamName) == 'style') &&
           isset($this->_arrElements[$strName]['params'][$mParamName])){
            // yes. So we have to add the value
            $this->_arrElements[$strName]['params'][$mParamName].=' '.$strParamValue;
            // done
            return $this;
        }
        
        // setting value for standard items
        $this->_arrElements[$strName]['params'][$mParamName] = $strParamValue;
        
        // done
        return $this;
    }
    
    // returns the html opening tag
    public function getOpenHtmlForm(){
        // setting action utl
        $strUrl = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_URL'];
        // returning tag
        return '<form class="LiteFrame_Form LiteFrame_Form_'.$this->_strName.'" name="'.$this->_strName.'" action="'.$strUrl.'" method="post">';
    }
    
    // return the html closing tag
    public function getCloseHtmlForm(){
        return '</form>';
    }
    
    // returns an html version of an element
    public function getHtmlElement($strName){
    
        // do we have this element ?
        if(!$this->hasElement($strName)){
            throw new Exception('LiteFrame_Form::getHtmlElement : no element named : '.$strName);
        }
        
        // checking tag
        // do we have an input
        if($this->_arrElements[$strName]['tag'] == 'input'){
        
            $strHtml = '<'.$this->_arrElements[$strName]['tag'];
            $strHtml.= ' type="'.$this->_arrElements[$strName]['type'].'"';
            $strHtml.= ' value="'.$this->_arrElements[$strName]['value'].'"';
            $strHtml.= ' name="'.$this->_arrElements[$strName]['name'].'"';
            $strHtml.= $this->_getHtmlParams($strName);
            $strHtml.='>';
            // done
            return $strHtml; 
        }
        
        // do we have an input
        if($this->_arrElements[$strName]['tag'] == 'submit' || $this->_arrElements[$strName]['tag'] == 'button'){
        
            $strHtml = '<'.$this->_arrElements[$strName]['tag'];
            $strHtml.= ' value="'.$this->_arrElements[$strName]['value'].'"';
            $strHtml.= $this->_getHtmlParams($strName);
            $strHtml.='>';
            // done
            return $strHtml;
        }
        
        // do we have a select
        if($this->_arrElements[$strName]['tag'] == 'select'){
        
            $strHtml = '<select';
            $strHtml.= $this->_getHtmlParams($strName);
            $strHtml.= ' name="'.$this->_arrElements[$strName]['name'].'"';
            $strHtml.='>';
            
            // getting options
            foreach($this->_arrElements[$strName]['options'] as $strOpValue => $strOpDisplayValue){
                $strHtml.= '<option';
                $strHtml.= ' value="'.$strOpValue.'"';
                
                if($strOpValue == $this->_arrElements[$strName]['value']){
                    $strHtml.= ' selected';
                }
                
                $strHtml.= '>'.$strOpDisplayValue.'</option>';
            }
            // end of tag
            $strHtml.= '</select>';
            // done
            return $strHtml;
        }
    }
    
    // returns element params as an html string
    protected function _getHtmlParams($strName){
    
        // do we have this element ?
        if(!$this->hasElement($strName)){
            throw new Exception('LiteFrame_Form::_getHtmlParams : no element named : '.$strName);
        }
        
        $strHtml = '';
        
        foreach($this->_arrElements[$strName]['params'] as $strParamName => $strParamValue){
            $strHtml.= ' '.$strParamName.'="'.$strParamValue.'"';
        }
        
        return $strHtml;
    }
    
    // fill form with post datas
    public function fillForm(){
    
        // do we have some posted datas
        if(!$this->isPost()){
            // no
            return $this;
        }
        
        // do we have post data for this form
        foreach($this->_arrElements as $strName => $arrElement){
            if(array_key_exists($arrElement['name'], $_POST)){
                // yes
                $this->setElementValue($strName, $_POST[$arrElement['name']]);
            }
        }
        
        // done
        return $this;
    }
    
    // returns true if some datas have been posted for this form 
    public function isPost(){
    
        // do we have post data for this form
        foreach($this->_arrElements as $arrElement){
            if(array_key_exists($arrElement['name'], $_POST)){
                // yes
                return true;
            }
        }
    
        // no
        return false;
    }
    
    // output the form open tag
    public function theOpenTag(){
        echo $this->getOpenHtmlForm();
    }
    
    // output the form close tag
    public function theCloseTag(){
        echo $this->getCloseHtmlForm();
    }
    
    // output the element tag
    public function theElement($strName){
        echo $this->getHtmlElement($strName);
    }
}
