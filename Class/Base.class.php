<?php
/**
 * DPhp
 * Author: only.fu <fuwy@foxmail.com>
 * Update: 13-12-10
 */
class Base{

    private $_view,$_controllerName,$_methodName;

    public function __construct(){
        $this->_view=new Template('');

    }

    //调用数据
    public function Model($name=''){
        $name=$name?$name:CONTROLLER;
        $modelName=$name.'Model';
        $modelPath=APP_MODEL_PATH.$modelName.'.php';
        if(is_file($modelPath)){
            require_once($modelPath);
            if(class_exists($modelName)){
                return new $modelName();
            }else{
                halt(array(
                    'message'=>$modelName.'不存在',
                    'file'=>$modelPath,
                ));
            }
        }else{
            halt(array(
                'message'=>$name.'文件不存在',
                'file'=>$modelPath,
            ));
        }
    }


    //调用类
    public function C($name){
        if(empty($name)){
            halt(array(
                'message'=>'类文件不能为空',
            ));
        }
        //check user class path
        $classPath=APP_CLASS_PATH.$name.'.class'.'.php';
        $classPath=is_file($classPath)?$classPath:DP_CLASS_PATH.$name.'.class'.'.php';
        if(is_file($classPath)){
            require_once($classPath);
            if(class_exists($name)){
                return new $name();
            }else{
                halt(array(
                    'message'=>$name.'不存在',
                    'file'=>$classPath,
                ));
            }
        }
    }

    //调用模板
    public function view($name=''){
        $this->_view->_TplDir=CONTROLLER;
        $methodName=METHOD;
        $name=$name?$name:$methodName;
        return $this->_view->load($name);
    }

    //获取$_POST和$_GET请求
    public function R(){
        return array_merge($_POST,$_GET);
    }

    /*
    * 结果输出
    * $msg: 输出文本
    * $status: 输出状态，可传值：succ, error，默认为succ
    * $redirect: 跳转地址
    * $refresh: 自动中转时间。默认为3秒
    */
    public function showMsg($msg,$status='succ',$redirect='',$refresh=3){
        if(empty($redirect)){
            $redirect=$this->getRefererUrl();
        }
        include DP_MESSAGE_TPL;
        exit;
    }

    //获取当前访问地址
    public function getCurrentUrl(){
        return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    //获取来路地址
    public function getRefererUrl(){
        return isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
    }

    //输出调试数组
    public function dump($arr){
        echo "<pre>";
        print_r($arr);
    }
}
?>