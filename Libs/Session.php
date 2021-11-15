<?php

//bin2hex(openssl_random_pseudo_bytes(32))

class LiteFrame_Session{

    // user identity. 
    // this is not a user id.
    // this value is only used to secure the connection
    // and ensure that the client that create the session is still the same
    protected $_strUserIdentity = false;

    // cache of the strRotate function
    protected $_arrRotateCache = array();
    
    // constructor
    public function __construct(){
        // getting browser identity
        $this->_strUserIdentity = $this->getBrowserSign();
    }

    // destrtruct function.
    public function __destruct(){
        // closing session
        $this->close();
    }
    
    // return true if a session is active
    public function hasSession(){
    
        if(session_status() === PHP_SESSION_ACTIVE){
            return true;
        }
        
        return false;
    }
        
    // start the session
    public function start($intLoop = false){
    
        // do we need to start a session
        if(!$this->hasSession()){  
            // yes
            // defining session name
            session_name('LiteFrame_Session');
            // starting session
            session_start();
        }
    
        // we change immediatly the sessions id
        session_regenerate_id();
        
        // do we need to set user identity
        if(!isset($_SESSION['_UserIdent'])){
            // yes
            $_SESSION['_UserIdent'] = $this->getBrowserSign();
        }
        
        // checking user identity
        if(isset($_SESSION['_UserIdent']) && $_SESSION['_UserIdent'] != $this->getBrowserSign()){
        
            if($intLoop){
                throw new Exception('LiteFrame_Session::start: Not able to start a valid session');
            }
        
            // session has been changed externally
            // removing all session data
            $this->clearDatas();
            // destroying session
            session_destroy();
            // restarting session
            $this->start(true);
        }
        
        // done
        return $this;
    }
    
    // close the session
    public function close(){
        
         // do we have a session
        if(!$this->hasSession()){
            // no
            return $this;
        }
        
        // close the sessions
        session_write_close();
        
        // done
        return $this;
    }
    
    // remove all data from the session, 
    // clear the _strUserIdentity and rotate cache
    public function clearDatas(){
    
        // remove user identity
        $this->_strUserIdentity = false;
        // clear the rotate cache
        $this->_arrRotateCache = false;
        // removing all session data
        $_SESSION = array();
        
        // done
        return $this;
    }
    
    // get the browser signature
    public function getBrowserSign(){
       return md5($_SERVER["REMOTE_ADDR"].$_SERVER["HTTP_USER_AGENT"].$_SERVER["HTTP_ACCEPT_LANGUAGE"].$_SERVER["HTTP_ACCEPT_DECODING"].'TPOU1458GLE51SKWHIQA26PLKVNHR236JDUENFZR489654FKXCHEZAJH12654');
    }
    
    // filter a string that will be use as session variable name or value
    // return true if the value is secure
    protected function _filterSessionString($strValue){
        
        if(!is_string($strValue) || empty($strValue) || !preg_match('/^[a-zA-Z0-9]+$/', $strValue)){
            return false;
        }
    
        return $this;
    }
    
    // a function that rotate chars in a string using the ascii table
    // we use it to crypte data to the session
    protected function _strRotate($string, $n = 10){
   
        // setting cache id
        $strCacheId = md5(serialize(func_get_args()));
   
        // do we have a cache
        if(isset($this->_arrRotateCache[$strCacheId]) && !empty($this->_arrRotateCache[$strCacheId])){
            // yes
            return $this->_arrRotateCache[$strCacheId];
        }
   
        $length = strlen($string);
        $result = '';
    
        for($i = 0; $i < $length; $i++) {
            $ascii = ord($string[$i]);
        
            $rotated = $ascii;
        
            if ($ascii > 64 && $ascii < 91) {
                $rotated += $n;
                $rotated > 90 && $rotated += -90 + 64;
                $rotated < 65 && $rotated += -64 + 90;
            } elseif ($ascii > 96 && $ascii < 123) {
                $rotated += $n;
                $rotated > 122 && $rotated += -122 + 96;
                $rotated < 97 && $rotated += -96 + 122;
            }
        
            $result .= chr($rotated);
        }
    
        // adding result to the cache
        $this->_arrRotateCache[$strCacheId] = $result;
        // done
        return $result;
    }
    
    // short for setting session vars
    public function __set($strName, $strValue){
        $this->set($strName, $strValue);
    }
    
    // sets a value to the session
    public function set($strName, $strValue){
        
        // do we have a session
        if(!$this->hasSession()){
            throw new Exception('LiteFrame_Session::set: Session is not started');
        }
        
        // checking name
        if(!$this->_filterSessionString($strName)){
            throw new Exception('LiteFrame_Session::set: invalid variable name. String expected');
        }
        
        // checking value
        if(!$this->_filterSessionString($strValue)){
            throw new Exception('LiteFrame_Session::set: invalid variable value. String expected');
        }
        
        // storing value
        $_SESSION[$strName] = $this->_strRotate($strValue, 10);
        
        // done
        return $this;
    }
    
     // short for getting session vars
    public function __get($strName){
        return $this->get($strName);
    }
    
    // get a value from the session
    public function get($strName){
    
         // do we have a session
        if(!$this->hasSession()){
            throw new Exception('LiteFrame_Session::get: Session is not started');
        }
    
        // does the value exists
        if(!$this->has($strName)){
            throw new Exception('LiteFrame_Session::get: invalid variable name. String expected');
        }
        
        // unrotate string
        $strValue = $this->_strRotate($_SESSION[$strName], -10);
        
        // is the value secured
        if(!$this->_filterSessionString($strValue)){
            // no
            // cleaning session
            $_SESSION = array();
            // destroying session
            session_destroy();
            // restarting session
            $this->start();
            // return default value, an empty string
            return '';
        }
        
        // returning session value
        return $strValue;
    }
    
     // short for testing session vars
    public function __isset($strName){
        return $this->has($strName);
    }
    
    // returns true if a variable is stored in the session
    public function has($strName){
    
        // do we have a session
        if(!$this->hasSession()){
            throw new Exception('LiteFrame_Session::get: Session is not started');
        }
        
        // checking name
        if(!$this->_filterSessionString($strName)){
            throw new Exception('LiteFrame_Session::has: invalid variable name. String expected');
        }
        
        return isset($_SESSION[$strName]);
    }
}
