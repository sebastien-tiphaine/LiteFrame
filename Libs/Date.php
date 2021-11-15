<?php

// class used to manipulate dates
class LiteFrame_Date{ 

    // constants
    const ONE_DAY = 24*60*60;
    
    // vars
    protected $_intTimeStamp = false;
    
    // constructor
    public function __construct($intTimeStamp = false){
    
        // do we have a date
        if(!is_numeric($intTimeStamp) || $intTimeStamp < 0){
            // no
            $intTimeStamp = time();
        }
    
        // setting date to current object
        $this->_intTimeStamp = $intTimeStamp;
    }
    
    // return time stamp of current object
    public function getTimeStamp(){
        return $this->_intTimeStamp;
    }
    
    // returns the timestamp of the first day of the current month at 00h00
    public function getMonthFirstDayStart(){
        return mktime(0, 0, 0, date('m', $this->_intTimeStamp), 1, date('Y', $this->_intTimeStamp));
    }
    
    // returns the timestamp of the last day of the current month at 23h59
    public function getMonthLastDayEnd(){
        return mktime(23, 59, 59, date('m', $this->_intTimeStamp)+1, 1, date('Y', $this->_intTimeStamp)) - self::ONE_DAY;
    }
    
    // returns the timestamp of the last day of the previous month at 23h59
    public function getPrevMonthLastDayEnd(){
        return $this->getMonthFirstDayStart() - 1;
    }
    
    // returns the timestamp of the first day of the previous month at 00h00
    public function getPrevMonthFirstDayStart(){
        return mktime(0, 0, 0, date('m', $this->getPrevMonthLastDayEnd()), 1, date('Y', $this->getPrevMonthLastDayEnd()));
    }
    
    // returns the timestamp of the last day of the previous month
    public function getNextMonthLastDayEnd(){
         return mktime(23, 59, 59, date('m', $this->getNextMonthFirstDayStart())+1, 1, date('Y', $this->getNextMonthFirstDayStart())) - self::ONE_DAY; 
    }
    
    // returns the timestamp of the first day of the next month at 00h00
    public function getNextMonthFirstDayStart(){
        return $this->getMonthLastDayEnd() + 1;
    }
    
    // returns true if the given date is possible
    public static function isValidDMY($intDay, $intMonth, $intYear){
    
        // do we have numerical values
        if(!is_numeric($intDay) || !is_numeric($intMonth) || !is_numeric($intYear)){
            // no
            return false;
        }
        
        // converting all to integers
        $intDay   = intval($intDay);
        $intMonth = intval($intMonth);
        $intYear  = intval($intYear);
    
        // simple check
        if($intDay < 1 || $intDay > 31 || $intMonth < 1 || $intMonth > 12 || $intYear < 0 || $intYear > 5000){
            return false;
        }
    
        // creating a timestamp from the given values
        $intTimeStamp = mktime(0,0,0, $intMonth, $intDay, $intYear);
        
        // checking day
        if(date('j', $intTimeStamp) != $intDay || 
           date('n', $intTimeStamp) != $intMonth || 
           date('Y', $intTimeStamp) != $intYear){
            // date is not valid
            return false;
        }
        
        return true;
    }

}
