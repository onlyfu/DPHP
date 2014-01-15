<?php
/**
 * DPhp
 * Author: only.fu <fuwy@foxmail.com>
 * Update: 13-12-10
 */

class Template{
    public $_lang;

    public $_TplDir='index';

    public function __construct($lang){
        if($lang){
            //$language=new language();
            //$this->_lang=$language->get($lang['lang']);
        }
    }

    public function load($file){
        if(!is_dir(APP_VIEW_PATH)){
            @mkdir(APP_VIEW_PATH,0777);
        }
        if(!is_dir(APP_VIEW_PATH.$this->_TplDir)){
            @mkdir(APP_VIEW_PATH.$this->_TplDir,0777);
        }
        $tplFile =APP_VIEW_PATH.$this->_TplDir.'/'.$file.'.htm';
        $tplCacheFile = APP_VIEW_CACHE_PATH.$this->_TplDir.'/'.$file.'.tpl.php';
        $timeCompare=0;
        if(is_file($tplCacheFile)){
            if(time()-@filemtime($tplCacheFile)>1200){
                @unlink($tplCacheFile);
            }else{
                $timeCompare=filemtime($tplCacheFile);
            }
        }

        @$this->checkTplRefresh($tplFile, $tplFile, $timeCompare);

        return $tplCacheFile;
    }

    public function checkTplRefresh($mainTpl, $subTpl, $timeCompare) {
        if(empty($timeCompare) || @filemtime($subTpl) > $timeCompare) {
            $this->parse_template($mainTpl,$timeCompare);
            return TRUE;
        }
        return FALSE;
    }

    public function parse_template($tplFile,$timestamp='') {
        $nest = 6;
        $file = basename($tplFile, '.htm');

        if(!is_dir(APP_VIEW_CACHE_PATH)){
            @mkdir(APP_VIEW_CACHE_PATH,0777);
        }

        if(!is_dir(APP_VIEW_CACHE_PATH.$this->_TplDir)){
            @mkdir(APP_VIEW_CACHE_PATH.$this->_TplDir,0777);
        }

        $tplCacheFile = APP_VIEW_CACHE_PATH.$this->_TplDir.'/'.$file.'.tpl.php';

        if(!@$fp = fopen($tplFile, 'r')) {
            echo "View does not exist!Plese check ".$this->_TplDir.'/'.$file.".htm";
            $fp=array();
        }

        $template = @fread($fp, filesize($tplFile));
        fclose($fp);

        //$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
        $const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

        //$headerexists = preg_match("/{(sub)?template\s+header\}/", $template) || $baseFile == 'header_ajax';
        //$subtemplates = array();
        for($i = 1; $i <= 3; $i++) {
            if($this->strexists($template, '{subtemplate')) {
                $template = preg_replace("/[\n\r\t]*\{subtemplate\s+([a-z0-9_:]+)\}[\n\r\t]*/ies", "self::stripvtemplate('\\1', 1,'".APP_VIEW_PATH."')", $template);
            }
        }

        //$template = preg_replace("/[\n\r\t]*\{csstemplate\}[\n\r\t]*/ies", $this->loadcsstemplate('\\1'), $template);
        $template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
        $template = preg_replace("/\{lang\s+(.+?)\}/ies", "self::languagevar('\\1')", $template);
        //$template = preg_replace("/\{faq\s+(.+?)\}/ies", "faqvar('\\1')", $template);
        $template = preg_replace("/\{\s*data_call\s*\(\s*([0-9]+)\s*\)\s*\}/s", "<?php data_call('\\1'); ?>", $template);
        $template = str_replace("{LF}", "<?=\"\\n\"?>", $template);

        $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);


        /*$headeradd = $headerexists ? "hookscriptoutput('$baseFile');" : '';
        if(!empty($subtemplates)) {
            $headeradd .= "\n0\n";
            foreach ($subtemplates as $fname) {
                $headeradd .= "|| checktplrefresh('$tplfile', '$fname', $timecompare, '$templateid', '$tpldir')\n";
            }
            $headeradd .= ';';
        }*/


        $template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_:]+)\}[\n\r\t]*/ies", "self::stripvtemplate('\\1', 0)", $template);
        $template = preg_replace("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/ies", "self::stripvtemplate('\\1', 0)", $template);
        $template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "self::stripvtags('<? \\1 ?>','')", $template);
        $template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "self::stripvtags('<? echo \\1; ?>','')", $template);
        $template = preg_replace("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/ies", "self::stripvtags('\\1<? } elseif(\\2) { ?>\\3','')", $template);
        $template = preg_replace("/([\n\r\t]*)\{else\}([\n\r\t]*)/is", "\\1<? } else { ?>\\2", $template);

        for($i = 0; $i < $nest; $i++) {
            $template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/ies", "self::stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\\3<? } } ?>')", $template);
            $template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/ies", "self::stripvtags('<? if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\\4<? } } ?>')", $template);
            $template = preg_replace("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r]*)(.+?)([\n\r]*)\{\/if\}([\n\r\t]*)/ies", "self::stripvtags('\\1<? if(\\2) { ?>\\3','\\4\\5<? } ?>\\6')", $template);
        }

        $template = preg_replace("/\{$const_regexp\}/s", "<?echo \\1?>", $template);
        $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);


        if(!@$fp = fopen($tplCacheFile, 'w')) {
            echo "Tpl can't write! Plese check ".APP_VIEW_CACHE_PATH.$this->_TplDir;
            $fp=array();
        }

        //$template = preg_replace("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/e", "self::transamp('\\0')", $template);
        //$template = preg_replace("/\<script[^\>]*?src=\"(.+?)\"(.*?)\>\s*\<\/script\>/ise", "self::stripscriptamp('\\1', '\\2')", $template);

        //$template = preg_replace("/[\n\r\t]*\{block\s+([a-zA-Z0-9_]+)\}(.+?)\{\/block\}/ies", "self::stripblock('\\1', '\\2')", $template);

        flock($fp, 2);
        fwrite($fp, $template);
        fclose($fp);
    }

    public function stripvtemplate($tpl, $sub,$tplDir='') {
        $vars = explode(':', $tpl);
        if(count($vars) > 1){
            $tpl = $vars[1];
            $tplDir = $vars[0];
        }
        $templateid = 0;
        if($sub) {
            return self::loadSubTemplate($tpl,$tplDir);
        } else {
            return stripvtags("<? include template('$tpl', '$templateid', '$tpldir'); ?>", '');
        }
    }

    public function loadSubTemplate($file,$tplDir) {

        $tplFile =APP_VIEW_PATH.$tplDir.'/'.$file.'.htm';

        $content = @implode('', file($tplFile));
        return $content;
    }

    public function languagevar($var) {
        return $this->_lang[$var];
    }

    /*public function faqvar($var) {
        global $_DCACHE;
        include_once ROOT.'/'.$cachedir.'/cache/cache_faqs.php';

        if(isset($_DCACHE['faqs'][$var])) {
            return '<a href="faq.php?action=faq&id='.$_DCACHE['faqs'][$var]['fpid'].'&messageid='.$_DCACHE['faqs'][$var]['id'].'" target="_blank">'.$_DCACHE['faqs'][$var]['keyword'].'</a>';
        } else {
            return "!$var!";
        }
    }*/

    public function stripvtags($expr, $statement) {
        $expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
        $statement = str_replace("\\\"", "\"", $statement);
        return $expr.$statement;
    }

    /*public function stripscriptamp($s, $extra) {
        $extra = str_replace('\\"', '"', $extra);
        $s = str_replace('&amp;', '&', $s);
        return "<script src=\"$s\" type=\"text/javascript\"$extra></script>";
    }*/

    /*public function stripblock($var, $s) {
        $s = str_replace('\\"', '"', $s);
        $s = preg_replace("/<\?=\\\$(.+?)\?>/", "{\$\\1}", $s);
        preg_match_all("/<\?=(.+?)\?>/e", $s, $constary);
        $constadd = '';
        $constary[1] = array_unique($constary[1]);
        foreach($constary[1] as $const) {
            $constadd .= '$__'.$const.' = '.$const.';';
        }
        $s = preg_replace("/<\?=(.+?)\?>/", "{\$__\\1}", $s);
        $s = str_replace('?>', "\n\$$var .= <<<EOF\n", $s);
        $s = str_replace('<?', "\nEOF;\n", $s);
        return "<?\n$constadd\$$var = <<<EOF\n".$s."\nEOF;\n?>";
    }*/

    public function strexists($haystack, $needle) {
        return !(strpos($haystack, $needle) === FALSE);
    }

    /*public function deldir($dir){
        $dir='cache/views/'.$dir;
        echo $dir;
        $dh = opendir($dir);
        while ($file = readdir($dh)){
            if ($file != "." && $file != ".."){
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)){
                    @unlink($fullpath);
                } else{
                    deldir($fullpath);
                }
            }
        }
        closedir($dh);
    }*/
}

?>