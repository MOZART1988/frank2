<?
/*
Title: MYSQL-DB-INTERFACE v 2.3.1 (расчудесный интерфейс по работе с базой данных mysql)
Author: Derevyanko Mikhail <m-derevyanko@ya.ru>
Date: 12.03.2008
*/

error_reporting(E_ALL & ~E_DEPRECATED);

class mysql{
	var $HOST;
	var $BASE;
	var $BASE_USER;
	var $BASE_USER_PASS;
	var $link;
	var $debug;
	var $count;
 
	function __construct($HOST='localhost', $BASE='order', $BASE_USER='root', $BASE_USER_PASS=''){
		$this->HOST = $HOST;
		$this->BASE = $BASE;
		$this->BASE_USER = $BASE_USER;
		$this->BASE_USER_PASS = $BASE_USER_PASS;
		$this->link = false;
		$this->debug = false;
		$this->count = 0;
	}
	
	function __destruct(){
		$this->mysql_close();
	}

	function prepare($str){
		if(!is_numeric($str)){
			$str = strtr($str, array_flip(get_html_translation_table()));
			$str = htmlspecialchars($str);
			if(!get_magic_quotes_gpc()) $str = addslashes($str);
		}
		return $str;
	}
	
	function mysql_connect(){
		if( is_resource($this->link) && mysql_ping($this->link) ) return $this->link;
		if( !$this->link = mysql_connect($this->HOST, $this->BASE_USER, $this->BASE_USER_PASS) ) return false;
		mysql_query("SET NAMES 'utf8'", $this->link);
		mysql_select_db($this->BASE);
		return $this->link;
	}
	
	function mysql_close(){
		if( is_resource($this->link) ) mysql_close($this->link);
		$this->link = false;
		return true;
	}
	
	function mysql_query($sql){
		if( $this->mysql_connect() ){
			$this->count++;
			if( $this->debug ) echo $sql."<br>\n";
			if(!$view = mysql_query($sql, $this->link)) echo mysql_error($this->link);
			return $view;
		}else return false;
	}
	
	function select($table, $where, $what='*'){
		$r = $this->mysql_query("SELECT ".$what." FROM ".$table." ".$where);
		$out = array();
		if(mysql_num_rows($r)){
			
			if(preg_match("/limit\s+1$/i", $where)){ 
				if(mysql_num_rows($r) == 1 && mysql_num_fields($r) == 1) return mysql_result($r, 0);
				return mysql_fetch_array($r, MYSQL_ASSOC);
			}
			if(!strstr($what, '*') && mysql_num_fields($r) == 1){
				$i=0;
				while($i<mysql_num_rows($r)){
					$out[]=mysql_result($r, $i++);
				}
			}else{
				while($o = mysql_fetch_array($r, MYSQL_ASSOC)){
					$out[]=$o;
				}
			}
			mysql_free_result($r);//странно, но пишут что функция освобождения памяти сама жрёт память. Сука засада.
		}
		return $out;
	}
	
	function count($table, $where="", $what='*'){
		return mysql_result($this->mysql_query("SELECT COUNT(".$what.") FROM ".$table." ".$where), 0);
	}
                                
	function insert($table, $params){
		$str1 = array();
		$str2 = array();
		foreach($params as $name => $value){
			$str1[]= "`".$name."`";
			$str2[]= "'".$this->prepare($value)."'";
		}
		return $this->mysql_query("INSERT INTO ".$table." (".join(", ", $str1).") VALUES (".join(", ", $str2).")");
	}
	
	function update($table, $update, $where){
		$str = array();
		foreach($update as $name => $value){
			$str[]= "`".$name."`='".$this->prepare($value)."'";
		}
		return $this->mysql_query("UPDATE ".$table." SET ".join(", ", $str)." ".$where);          
	}
	
	function delete($table, $where){
		return $this->mysql_query("DELETE FROM ".$table." ".$where);
	}
}#CLASS
?>
