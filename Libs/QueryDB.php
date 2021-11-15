<?php

class LiteFrame_QueryDB{

    // instance of current object
    protected static $_oInstance = null;

    // database connector
    protected $_oPDO = null;

    // connection string
    protected $_strDbConnectorParams = false;
    
    // constructor
    private function __construct(){
    
        // TODO: check if PDO is available
    
        // do we have the required defined vars
        if(!defined('DB_NAME')     || empty(DB_NAME)     ||
           !defined('DB_USER')     || empty(DB_USER)     || 
           !defined('DB_PASSWORD') || empty(DB_PASSWORD) ||
           !defined('DB_HOST')     || empty(DB_HOST)){
                // no
                throw new Exception('LiteFrame_QueryDB: Missing or invalid database parameters.');
        }
    
        // setting connector parameters
        $this->_strDbConnectorParams = 'mysql:host='.DB_HOST.';dbname='.DB_NAME;
        
        // do we have to insert the charset param
        if(defined('DB_CHARSET') && !empty(DB_CHARSET)){
            // yes
            $this->_strDbConnectorParams.=';charset='.DB_CHARSET;
        }
        
        return $this;
    }
    
    // return true if the string is valid
    protected function _validateString($strString){
     
        if(!is_string($strString) || empty($strString) || !preg_match('/^[a-zA-ZÀ-ÿ0-9\.\,\-\+\s]+$/', $strString)){
            return false;
        }
     
        return true;
    }
    
    // Returns an instance of LiteFrame_QueryDB
    public static function getInstance(){
    
         // do we already have an instance of the current object
        if(!self::$_oInstance instanceof LiteFrame_QueryDB){
            // no
            self::$_oInstance = new LiteFrame_QueryDB();
        }
        
        // yes
        return self::$_oInstance;
    }
    
    // return pdo connector
    public function getConnector(){
    
        // do we already have a pdo instance
        if(!$this->_oPDO instanceof PDO){
            // no
            // building object
            $this->_oPDO = new PDO($this->_strDbConnectorParams, DB_USER, DB_PASSWORD);
            // setting additionnal params
            $this->_oPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    
        // yes
        return $this->_oPDO;
    }
    
    // generate an uniq id for datat insertion
    public static function generateId(){
        return md5(uniqid('fzhrhla0943i15fosmcnmfheipé15786dleuasd', true).microtime().str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'));
    }

    // shortcut to query method
    public static function sendQuery($strQuery, $arrParams = array()){
        // calling query function
        return self::getInstance()->query($strQuery, $arrParams);
    }
    
    // shortcut to query and fetchAll method.
    // returns an array
    public static function fetchAll($strQuery, $arrParams = array()){
        // getting statment
        $oStmt = self::sendQuery($strQuery, $arrParams);
        // do we have a result
        if(!$oStmt instanceof PDOStatement){
            // no
            return false;
        }
        // getting result
        return $oStmt->fetchAll();
    }
    
    // Send a query to the database and return the result as a PDOStatement
    // exemple :
    // 'SELECT * FROM users WHERE uslogin = :uslogin' , 
    //        array(
    //            ':uslogin'    => $strLogin,
    //        )
    // to force type of the param :
    //        array(
    //            ':uslogin'    => array($strLogin, PDO::PARAM_STR),
    //        )
    public function query($strQuery, $arrParams = array()){
    
        // do we have a valid query string
        if(!is_string($strQuery) || empty($strQuery)){
            // no
            throw new Exception('LiteFrame_QueryDB::query Empty Query String.');
        }
        
        // TODO:Secure the query string with escape or something else.
        
        // getting databse connection
        $oCon = $this->getConnector();
        // getting query
        $oQuery = $oCon->prepare($strQuery);
        
        // binding params
        if(is_array($arrParams) && !empty($arrParams)){
            foreach($arrParams as $strParamName => $mParamValue){
                // setting default type to string
                $intType = PDO::PARAM_STR;
            
                // do we have a param type ?
                if(is_array($mParamValue)){
                    // yes
                    // is param type usable ?
                    if(!isset($mParamValue[1]) || !in_array($mParamValue[1], array(
                        PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_BOOL, PDO::PARAM_NULL,
                    ))){
                        // no
                        throw new Exception('LiteFrame_QueryDB::query Invalid PDO value type.');
                    }
                    
                    // extracting type
                    $intType = $mParamValue[1];
                    // extracting value
                    $mParamValue = $mParamValue[0];
                }
            
                // checking content
                // do we have a string
                if(is_string($mParamValue)){
                    // yes
                    if(!$this->_validateString($mParamValue)){
                        throw new Exception('LiteFrame_QueryDB::query Unsecure string found : '.$mParamValue);
                    }
                } // do we have a usable type ?
                else if(!is_int($mParamValue) && !is_null($mParamValue) && !is_bool($mParamValue)){
                    // no
                    throw new Exception('LiteFrame_QueryDB::query invalid data type.');
                }

                // adding param value to query
                if(!$oQuery->bindValue($strParamName, $mParamValue, $intType)){
                    throw new Exception('LiteFrame_QueryDB::query. Not able to bind param');
                }
            }
        }
        
        // executing query
        $oQuery->execute();
        
        // return query
        return $oQuery;
    }
}
