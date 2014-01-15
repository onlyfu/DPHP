<?php
/**
 * DPhp
 * Author: only.fu <fuwy@foxmail.com>
 * Update: 13-12-10
 */
error_reporting(0);
defined("APP_PATH") or define("APP_PATH", dirname(__FILE__).'/');
defined("DP_PATH") or define("DP_PATH", dirname(__FILE__).'/');
defined("DP_CLASS_PATH") or define("DP_CLASS_PATH", DP_PATH.'/Class/');
defined("appEx_PATH") or define("appEx_PATH", dirname(__FILE__).'/appEx/');

defined("APP_CLASS_PATH") or define("APP_CLASS_PATH", 'Class/');
define('APP_CONTROLLER_PATH', 'Controller/');
define('APP_MODEL_PATH', 'Model/');
define('APP_VIEW_PATH', 'View/');
define('APP_CACHE_PATH', 'Cache/');
define('APP_VIEW_CACHE_PATH', APP_CACHE_PATH.'views/');
//系统错误模板
defined("DP_ERROR_TPL") or define('DP_ERROR_TPL', DP_PATH.'Template/error.tpl.php');
//系统返回结果模板
defined("DP_MESSAGE_TPL") or define('DP_MESSAGE_TPL', DP_PATH.'Template/message.tpl.php');

//注册错误捕获
register_shutdown_function('fatalError');

//set_error_handler('appError');

set_error_handler('appError');
//注册自动获取
spl_autoload_register('autoLoad');
//检查配置文件
if(!file_exists(APP_PATH.'Config/default.config.php')){
    init(appEx_PATH,APP_PATH,1);
}



function loadBaseClass(){
    $files=array(
        DP_CLASS_PATH.'Route.class.php',
        DP_CLASS_PATH.'Config.class.php',
        DP_CLASS_PATH.'Base.class.php',
        DP_CLASS_PATH.'Template.class.php',
        DP_CLASS_PATH.'DbMysql.class.php',
        DP_CLASS_PATH.'Model.class.php',
    );

    foreach($files as $v){
        if(is_file($v)){
            require_once($v);
        }
    }
}

//初始化文件夹与文件
function init($source, $f, $child){
    if(!is_dir($f)){
        mkdir($f,0777);
    }
    $handle=dir($source);
    while($entry=$handle->read()) {
        if(($entry!=".")&&($entry!="..")){
            if(is_dir($source."/".$entry)){
                if($child)
                    init($source."/".$entry,$f."/".$entry,$child);
            }else{
                copy($source."/".$entry,$f."/".$entry);
            }
        }
    }
    return true;
}

//错误捕获
function fatalError(){
    if ($e = error_get_last()) {
        switch($e['type']){
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                function_exists('halt')?halt($e):exit('ERROR:'.$e['message']. ' in <b>'.$e['file'].'</b> on line <b>'.$e['line'].'</b>');
                break;
        }
    }
}

function appError($errno, $errstr, $errfile, $errline) {
    $errorStr = "$errstr ".$errfile." 第 $errline 行.";
    function_exists('halt')?halt($errorStr):exit('ERROR:'.$errorStr);
}

//错误输出
function halt($error){
    if (!is_array($error)) {
        $trace          = debug_backtrace();
        $e['message']   = $error;
        $e['file']      = $trace[0]['file'];
        $e['line']      = $trace[0]['line'];
        ob_start();
        debug_print_backtrace();
        $e['trace']     = ob_get_clean();
    } else {
        $e              = $error;
    }
    include DP_ERROR_TPL;
    exit;
}

function autoLoad($className){
    $classFile=$className.'.class.php';
    $classPath=APP_CLASS_PATH.$classFile;
    if(!file_exists($classPath)){
        halt(array(
            'message'=>$classFile.'文件不存在',
            'file'=>$classPath
        ));
    }
    require_once($classPath);
}

function require_cache($fileName){
    static $_importFiles = array();
    if (!isset($_importFiles[$fileName])) {
        if (is_file($fileName)) {
            //require $fileName;
            $_importFiles[$fileName] = require $fileName;
        }
    }
    return $_importFiles[$fileName];
}

loadBaseClass();
$Route=new Route();
$Route->init();