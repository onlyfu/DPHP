<?php
/**
 * DPhp
 * Author: only.fu <fuwy@foxmail.com>
 * Update: 13-12-10
 */

class Config {

    public function load($file){
        $file=APP_PATH.'/Config/'.$file.'.config.php';
        if(is_file($file)){
            return require_cache($file);
        }
    }
}
?>