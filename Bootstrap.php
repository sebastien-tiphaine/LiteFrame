<?php

// The purpose of this file is to define main framework variables

define('LITEFRAME_MAIN_PATH',   realpath(__DIR__));
define('LITEFRAME_LIBS_PATH',   realpath(LITEFRAME_MAIN_PATH.'/Libs'));

try{

    // Checking common var definitions
    if(!defined('APPLICATION_PATH') || !is_dir(APPLICATION_PATH)){
        throw new Exception('APPLICATION_PATH is not defined or is not a valid directory');
    }

    if(!defined('LIBS_PATH')){
        define('LIBS_PATH', realpath(APPLICATION_PATH.'/Libs'));
        if(!is_dir(LIBS_PATH)){
            throw new Exception(LIBS_PATH.' is not a valid directory');
        }
    }

    if(!defined('CONTROLLERS_PATH')){
        define('CONTROLLERS_PATH', realpath(APPLICATION_PATH.'/Controllers'));
        if(!is_dir(CONTROLLERS_PATH)){
            throw new Exception(CONTROLLERS_PATH.' is not a valid directory');
        }
    }

    if(!defined('TEMPLATING_PATH')){
        define('TEMPLATING_PATH', realpath(APPLICATION_PATH.'/Templating'));
        if(!is_dir(TEMPLATING_PATH)){
            throw new Exception(TEMPLATING_PATH.' is not a valid directory');
        }
    }

    // Loading the main loader class
    require_once(LITEFRAME_LIBS_PATH.'/Loader.php');

    if(!class_exists('LiteFrame_Loader')){
        throw new Exception('Not able to load class LiteFrame_Loader');
    }

    // Loading the dispatcher
    LiteFrame_Loader::Library('LiteFrame_Dispatcher');

    // dispatching the request to the controller and rendring content
    $oDispatcher = new LiteFrame_Dispatcher();
    $oDispatcher->dispatch();
    
}catch(Exception $oException){

    // getting trace
    $arrTrace = $oException->getTrace();

    ?><html>
        <head>
            <title>LiteFrame Error : <?php echo $oException->getMessage(); ?></title>
        </head>
        <body>
            <h2>An Exception Was Thrown :</h1>
            <h3 style="color:red;"><?php echo $oException->getMessage(); ?></h3>
            <?php
                // setting default initial trace
                $strTrace = '';
                
                if($arrTrace[0]['class'] != '') {
                    $strTrace.= $arrTrace[0]['class'].'->';
                }
                if($arrTrace[0]['function'] != '') {
                    $strTrace.= $arrTrace[0]['function'].'()';
                }

                if(!empty($strTrace)){
                    echo '@'.$strTrace;
                }
            ?><br><br>
            <h4>Trace :</h4>
            <?php
            
                foreach($arrTrace as $intTrace => $arrDatas){
                    
                    echo '---------- '.$intTrace.'---------- <br><br>';
                    
                    foreach($arrDatas as $strKey => $mTraceData){
                        if(is_string($mTraceData)){
                            echo '<strong>'.$strKey.'</strong> : '.$mTraceData.'<br>';
                        }
                        if(is_array($mTraceData)){
                            foreach($mTraceData as $strDataKey => $mData){
                                echo '<strong>>>> '.$strKey.' _ '.$strDataKey.'</strong> : '.$mData.'<br>'; 
                            }
                        }
                    }
                    
                    echo '<br>';
                }            
            ?>
        </body>
     </html><?php
}
