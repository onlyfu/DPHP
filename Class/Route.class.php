<?php
/**
 * DPhp
 * Author: only.fu <fuwy@foxmail.com>
 * Update: 13-12-10
 */
class Route{
    private $_config,$_controller,$_method;

    public function init(){
        $_POST = $this->daddslashes($_POST);
        $_GET = $this->daddslashes($_GET);
        $_REQUEST = $this->daddslashes($_REQUEST);
        $_COOKIE = $this->daddslashes($_COOKIE);
        $_FILES = $this->daddslashes($_FILES);

        $config=new Config();
        $this->_config=$config->load('default');
        define('CONTROLLER',$this->getController());
        define('METHOD',$this->getMethod());

        $controllerFile=APP_CONTROLLER_PATH.CONTROLLER.'.controller.php';
        if(is_file($controllerFile)){
            $className=CONTROLLER;
            require($controllerFile);
            if(class_exists($className)){
                $class=new $className();
                if(method_exists($class,METHOD)){
                    $method=METHOD;
                    $class->$method();
                }else{
                    halt(array(
                        'message'=>METHOD.'方法不存在',
                        'file'=>$controllerFile,
                    ));
                }
            }else{
                halt(array(
                    'message'=>$className.'类不存在',
                    'file'=>$controllerFile,
                ));
            }
        }else{
            halt(array(
                'message'=>CONTROLLER.'控制器不存在',
                'file'=>$controllerFile,
            ));
        }
    }

    private function getController(){
        $controller = isset($_GET['controller']) && !empty($_GET['controller']) ? $_GET['controller'] : (isset($_POST['controller']) && !empty($_POST['controller']) ? $_POST['controller'] : '');
        if(empty($controller)){
            $controller = $this->_config['CONTROLLER'];
        }
        return $controller;
    }

    private function getMethod(){
        $method = isset($_GET['method']) && !empty($_GET['method']) ? $_GET['method'] : (isset($_POST['method']) && !empty($_POST['method']) ? $_POST['method'] : '');
        if(empty($method)){
            $method = $this->_config['METHOD'];
        }
        return $method;
    }

    private function daddslashes($string, $force = 0) {
        //$string=dhtmlspecialchars($string);
        !defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
        if(!MAGIC_QUOTES_GPC || $force) {
            if(is_array($string)) {
                foreach($string as $key => $val) {
                    $string[$key] = $this->daddslashes($val, $force);
                }
            } else {
                $string = addslashes(trim($string));
            }
        }
        return $string;
    }
}
?>