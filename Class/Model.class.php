<?php
/**
 * DPhp
 * Author: only.fu <fuwy@foxmail.com>
 * Update: 13-12-11
 */

class Model {
    private $db,$db_config;

    public function __construct(){
        $config=new Config();
        $_config=$config->load('default');
        $this->db_config=$_config;
        $this->db=new dbmysql();
        $this->db->connect($_config['DB_HOST'],$_config['DB_USER'],
            $_config['DB_PASS'],$_config['DB_NAME'],$_config['DB_PCONNECT'],true,
            $_config['DB_CHARSET']);
    }

    function __destruct(){
        $this->db->close();
    }

    public function find($table,$m,$params=array()){
        $table=$this->db_config['DB_TABLEPRE'].$table;
        $fields=isset($params['fields'])?implode(',',$params['fields']):'*';
        $join=$this->format_left($params);
        $conditions=$this->format_condition($params);
        $orders=$this->format_order($params);
        $group = $this->format_group($params);
        if($m!='first'){
            $limit=$this->format_limit($params);
        }
        $sql='select '.$fields.' from '.$table.' '.$join.' '.$conditions.' '.$group.' '.$orders.' '.$limit;
        //echo $sql."<br/>";
        if($m=='first'){
            $sql.=' limit 1';
            $results=$this->db->fetch_first($sql);
        }elseif($m=='list'){
            $data=$this->db->query($sql);
            while($query=$this->db->fetch_array($data)){
                $results[]=$query;
            }
        }else{
            $data=$this->db->query($sql);
            while($query=$this->db->fetch_array($data)){
                $results[$query[$m]]=$query;
            }
        }
        return $results;
    }


    public function paginate($table,$params=array()){
        $params['page'] = max(1, intval($params['page']));
        $pagenums=$params['limit'][1];
        $start_limit = ($params['page'] - 1) * $pagenums;
        $_params=array(
            'fields'=>array('count(*) as nums'),
            'conditions'=>$params['conditions'],
        );

        if(isset($params['LEFT'])){
            $_params['LEFT']=$params['LEFT'];
        }
        if(isset($params['group'])){
            $_params['group']=$params['group'];

            //$sql="select count(*) as nums from (select count(*) as count from ".$table." )";

            $datanums=$this->getNumsByGroup($table,$_params);
        }else{
            $datanums=$this->getrows($table,$_params);
        }

        $params['limit']=array($start_limit,$pagenums);
        return array($this->find($table,'list',$params),$datanums,$multi_admin);

    }

    public function getNumsByGroup($table,$params){
        $table=$this->db_config['DB_TABLEPRE'].$table;
        $fields=isset($params['fields'])?implode(',',$params['fields']):'*';
        $join=$this->format_left($params);
        $conditions=$this->format_condition($params);
        $group = $this->format_group($params);
        $sql='select count(*) as nums from (select '.$fields.' from '.$table.' '.$join.' '.$conditions.' '.$group.') t';
        //echo $sql."<br/>";
        $results=$this->db->fetch_first($sql);
        return $results['nums'];
    }

    public function getrows($table,$params){
        $table=$this->db_config['DB_TABLEPRE'].$table;
        $fields=isset($params['fields'])?implode(',',$params['fields']):'*';
        $join=$this->format_left($params);
        $conditions=$this->format_condition($params);
        $group = $this->format_group($params);
        $sql='select '.$fields.' from '.$table.' '.$join.' '.$conditions.' '.$group;
        //echo $sql."<br/>";
        $results=$this->db->fetch_first($sql);
        return $results['nums'];
    }
    public function getrow($table,$params){
        $table=$this->db_config['DB_TABLEPRE'].$table;
        $fields=isset($params['fields'])?implode(',',$params['fields']):'*';
        $join=$this->format_left($params);
        $conditions=$this->format_condition($params);
        $group = $this->format_group($params);
        $sql='select '.$fields.' from '.$table.' '.$join.' '.$conditions.' '.$group;
        $results=$this->db->fetch_first($sql);
        return $results;
    }

    public function getone($table,$params){
        $table=$this->db_config['DB_TABLEPRE'].$table;
        if(isset($params['field'])){
            $field=$params['field'];
        }
        $conditions=$this->format_condition($params);
        $join=$this->format_left($params);
        $orders=$this->format_order($params);
        $sql='select '.$field.' from '.$table.' '.$join.' '.$conditions.' '.$orders.' limit 1';
        //echo $sql."<br/>";
        $results=$this->db->result_first($sql);
        return $results;
    }

    /*直接运行sql*/
    public function runSql($sql,$m,$paginate=''){
        if($m=='first'){
            $sql.=' limit 1';
            $results=$this->db->fetch_first($sql);
        }elseif($m=='list'){
            $data=$this->db->query($sql);
            while($query=$this->db->fetch_array($data)){
                $results[]=$query;
            }
        }else{
            $data=$this->db->query($sql);
            while($query=$this->db->fetch_array($data)){
                $results[$query[$m]]=$query;
            }
        }
        //echo $sql."<br/>";
        if($paginate){
            $multi_admin=Base::multi($paginate['datanums'],$paginate['pagenum'],$paginate['page'],$paginate['url'],$paginate['multi'],$paginate['link']);
            return array($results,$paginate['datanums'],$multi_admin);
        }else{
            return $results;
        }

    }

    public function update($table,$params){
        $table=$this->db_config['DB_TABLEPRE'].$table;
        if(isset($params['fields'])){
            $fields='set ';
            $i=0;
            foreach($params['fields'] as $k=>$v){
                if(strpos($k,'+')){
                    $fields.= $i? ",".(str_replace('+','',$k))."=$k$v":(str_replace('+','',$k))."=$k$v";
                }elseif(strpos($k,'-')){
                    $fields.= $i? ",".(str_replace('-','',$k))."=$k$v":(str_replace('-','',$k))."=$k$v";
                }else{
                    $fields.=$i?",".$k."='".$v."'":$k."='".$v."'";
                }

                $i++;
            }
            unset($v);
            unset($i);
        }
        $conditions=$this->format_condition($params);
        $sql='update '.$table.' '.$fields.' '.$conditions;
        //echo $sql."<br/>";
        $this->db->query($sql);
    }

    public function insert($table,$params){
        $table=$this->db_config['DB_TABLEPRE'].$table;
        if(isset($params)){
            $fields='(';
            $i=0;
            foreach($params['key'] as $v){
                $fields.=$i?','.$v:$v;
                $i++;
            }
            unset($i);
            unset($v);
            $fields.=') values (';
            $i=0;
            foreach($params['value'] as $v){
                $fields.=$i?",'".$v."'":"'".$v."'";
                $i++;
            }
            unset($i);
            unset($v);
            $fields.=')';
        }
        $sql="insert into ".$table." ".$fields;
        //echo $sql."<br/>";
        $this->db->query($sql);
        return $this->db->insert_id();
    }

    /*复制数据*/
    public function copyTotable($source,$target,$params){
        $source = $this->db_config['DB_TABLEPRE'] . $source;
        $target = $this->db_config['DB_TABLEPRE'] . $target;
        $conditions=$this->format_condition($params);
        $skey = implode (',', $params['field']['skey']);
        $tkey = implode (',', $params['field']['tkey']);
        $sql = 'insert '.$target.'('.$tkey.') select '.$skey.' from '.$source.' '.$conditions;
        $this->db->query($sql);
        return $this->db->insert_id();
    }


    public function del($table,$params){
        $table=$this->db_config['DB_TABLEPRE'].$table;
        $conditions=$this->format_condition($params);
        $sql='delete from '.$table.' '.$conditions;
        //echo $sql."<br/>";
        $this->db->query($sql);
    }

    public function format_conditionstr($k,$v,$i){
        if($k){
            $conditions='';
            if($k == '[fields]'){
                $conditions .= $i ? ' and ':'';
                $_temp = '';
                foreach($v As $kk => $vv){
                    foreach($vv As $kkk => $vvv){
                        $_temp .= '`'.$kkk.'`=`'.$vvv.'` AND';
                    }
                }
                if($_temp){
                    $conditions .= substr($_temp,0,strlen($_temp) - 3);
                }
            }elseif($k!='or'){
                $conditions .= $i?' and ':'';
                if(strpos($k,'>')||strpos($k,'<')||strpos($k,'>=')||strpos($k,'<=')||strpos($k,'!=')||strpos($k,'like')){
                    $conditions.=$k."'".$v."'";
                }elseif(substr($k,-3) == ' in' || substr($k,-7) == ' not in'){
                    $ins=explode(',',$v);
                    $conditions.=$k."(";
                    foreach($ins as $n=>$m){
                        $conditions.=$n==0?"'".$m."'":",'".$m."'";
                    }
                    $conditions.=")";
                    //$conditions.=$k."(".$v.")";
                }else{
                    $conditions.=$k."='".$v."'";
                }
            }else{
                $conditions.=$i?' or (':'(';
                $j=0;
                foreach($v as $b){
                    foreach($b as $c=>$d){
                        if(strpos($c,'>')||strpos($c,'<')||strpos($c,'>=')||strpos($c,'<=')||strpos($c,'!=')||strpos($c,'like')){
                            $conditions.=$i?($j?' and '.$c." '".$d."'":$c." '".$d."'"):($j?' or '.$c." '".$d."'":$c." '".$d."'");
                        }elseif(strpos($c,'in')||strpos($k,'not in')){
                            $conditions.=$i?($j?' and '.$c." (".$d.")":$c." (".$d.")"):($j?' or '.$c." (".$d.")":$c." (".$d.")");
                        }else{
                            $conditions.=$i?($j?' and '.$c."='".$d."'":$c."='".$d."'"):($j?' or '.$c."='".$d."'":$c."='".$d."'");
                        }
                        $j++;
                    }
                }
                unset($j);
                $conditions.=$i?')':')';
            }
        }
        return $conditions;
    }

    public function format_left($params){
        if(isset($params['LEFT'])){
            if($params['LEFT']['table'] && $params['LEFT']['ON']){
                return 'LEFT JOIN '.$this->db_config['DB_TABLEPRE'].$params['LEFT']['table'].' ON ('.$params['LEFT']['ON'].')';
            }else{
                $left = $params['LEFT'];
                $sql = '';
                foreach($left As $key => $val){
                    if(is_array($val)){
                        $sql .= ' LEFT JOIN '.$this->db_config['DB_TABLEPRE'].$val['table'].' As '.$key.' ON ('.$val['ON'].') ';
                    }
                }
                return $sql;
            }

        }
        return '';
    }

    public function format_order($params){
        if(isset($params['orders'])){
            $orders='order by ';
            $i=0;
            foreach($params['orders'] as $k=>$v){
                $orders.=$i?','.$k.' '.$v:$k.' '.$v;
                $i++;
            }
            return $orders;
        }
        return '';
    }

    public function format_group($params){
        if(isset($params['group'])){
            return " group by ".$params['group'];
        }
        return '';
    }

    public function format_condition($params){
        if(isset($params['conditions'])){
            $conditions='where ';
            $i=0;
            foreach ($params['conditions'] as $k=>$v){
                $conditions.=$this->format_conditionstr($k,$v,$i);
                $i++;
            }
            return $conditions;
        }
        return '';
    }

    public function format_limit($params){
        if(isset($params['limit'])){
            $limit='limit ';
            $i=0;
            foreach($params['limit'] as $v){
                $limit.=$i?','.$v:$v;
                $i++;
            }
            return $limit;
        }
        return '';
    }
}

?>