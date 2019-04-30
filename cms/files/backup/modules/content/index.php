<?
class content extends appends{
private $path;

public $title;
public $info;
protected $js;
protected $css;
public $ico;
public $hidden;
public $lang;
public $languages;

	function __construct(){
		parent::__construct();
		#PRIVATE
		$this->path = _MODULES_.'/'.basename(dirname(__FILE__));
		#CONFIG
		$this->title = 'Контент';
		$this->info = 'Динамическое содержимое страниц.';
		$this->js = array(
			$this->path."/js/init.js",
			$this->path."/js/classes.js",
			$this->path."/js/objects.js",
			_FILES_."/appends/ckeditor/ckeditor.js",
			_FILES_."/appends/ckeditor/adapters/jquery.js",
			_FILES_."/appends/ckfinder/ckfinder.js"
		);
		
		$this->css = array(
			$this->path."/css/styles.css",
			$this->path."/css/objects.css",
			$this->path."/css/classes.css"
		);
		$this->lang = 'ru';
		$this->languages = array(
			"ru"=>"русский",
			"en"=>"английкий",
			"kz"=>"казахский"
		);
		
		$this->ico = null;
	}
	
	function ajax(){
		global $api, $_months;
		if( @!$go=$_REQUEST['go'] ) return '';
		
		if( !empty($_REQUEST['lang']) && array_key_exists($_REQUEST['lang'], $this->languages) ) $this->lang = $_REQUEST['lang'];
		
		switch( $go ){
			case 'loadClasses': return $this->json( $this->getClasses() );
			case 'loadClassFields': 
				if(empty($_REQUEST['id']) || !is_numeric($class_id=$_REQUEST['id'])) return '[]';
				return $this->json( $this->getClassFields( $class_id ) );
			case 'deleteClass': 
				if(!empty($_REQUEST['id']) && is_numeric($class_id=$_REQUEST['id']) && $this->deleteClass( $class_id )) return '1';
				return '0';
			case 'saveClass':
				if(isset($_REQUEST['id']) && is_numeric($class_id=$_REQUEST['id'])){
					$class = array(
						"id"=>$class_id,
						"name"=>$_REQUEST['name'],
						"info"=>$_REQUEST['info']
					);
					$fields = $_REQUEST['fields'];
					if( !!$this->editClassAndFields($class, $fields) ) return '1';
				}else{
					$class = array(
						"name"=>$_REQUEST['name'],
						"info"=>$_REQUEST['info']
					);
					$fields = $_REQUEST['fields'];
					if( !!$this->createClassAndFields($class, $fields) ) return '1';
				}
				return '0';
			case 'loadObjects':
				$page = 0;
				if(isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) && $_REQUEST['page']>0) $page = $_REQUEST['page']-1;
				$onepage = 30;
				if(isset($_REQUEST['onepage']) && is_numeric($_REQUEST['onepage']) && $_REQUEST['onepage']>0) $onepage = $_REQUEST['onepage'];
				
				$limit = ' LIMIT '.($onepage*$page).', '.$onepage;
				if(isset($_REQUEST['head']) && is_numeric($head=$_REQUEST['head']) && (!!$list = $this->getObjects($head, $limit))) return $this->json( $list );
				return '[]';
			case 'loadObject':
				if(isset($_REQUEST['id']) && is_numeric($id=$_REQUEST['id']) && (!!$obj = $this->getObject($id))) return $this->json( $obj );
			case 'saveObject':
				$lang = !empty($_REQUEST['lang']) && array_key_exists($_REQUEST['lang'], $this->languages)? $_REQUEST['lang'] : 'ru';
				if(isset($_REQUEST['id']) && is_numeric($object_id=$_REQUEST['id'])){
					$object = array(
						"active"=>$_REQUEST['active'],
						"id"=>$object_id,
						"name"=>$_REQUEST['name'],
						"class_id"=>$_REQUEST['class_id']
					);
					if( $_REQUEST['class_id']>0 && isset($_REQUEST['fields']) && is_array($fields = $_REQUEST['fields']) ){
						if( !!$this->editObjectAndFields($object, $fields) ) return '1';
					}else if( !!$this->editObject($object) ) return '1';
				}else{
					$object = array(
						"active"=>$_REQUEST['active'],
						"head"=>$_REQUEST['head'],
						"name"=>$_REQUEST['name'],
						"class_id"=>$_REQUEST['class_id'],
						"sort"=>time()
					);
					if( $_REQUEST['class_id']>0 && isset($_REQUEST['fields']) && is_array($fields = $_REQUEST['fields']) ){
						if( !!$this->createObjectAndFields($object, $fields) ) return '1';
					}else if( !!$this->createObject($object) ) return '1';
				}
				return '0';
			case 'deleteObject':
				if( isset($_REQUEST['id']) && is_numeric($id=$_REQUEST['id']) && !!$this->deleteObject($id) ) return '1';
				return '0';
			case 'moveObject':
				if( !isset($_REQUEST['id']) || !is_numeric($id=$_REQUEST['id']) || !isset($_REQUEST['to']) || !is_numeric($to=$_REQUEST['to']) || !$this->moveObject($id, $to)) return '0';
				return '1';
			case 'sortObject':
				if(isset($_REQUEST['to']) && isset($_REQUEST['id']) && is_numeric($id = $_REQUEST['id']) && $this->sortObject($id, $_REQUEST['to'])) return '1';
				return '0';
			case 'uploadFiles':
				if(empty($_FILES)) return "[]";
				$out = array();
				foreach($_FILES['file']['tmp_name'] as $k => $v){
					if(!$v) continue;
					$type = '';
					if(preg_match("/\.([^\.]+)$/", $_FILES['file']['name'][$k], $ok)) $type = $this->lower($ok[1]);
					$new_name = "file_".time()."_".rand(0, 1000000000).($type ? ".".$type : "");
					move_uploaded_file( $v, _UPLOADS_ABS_."/".$new_name);
					$out['file-'.$k]=$new_name;
				}
				return $this->json( $out );
			default: return '142';
		}
	}
	
	function initHTML(){
		$out = array();
		$out[]='<table class="main-table" cellpadding="10" border="0">';
			$out[]='<tr>';
				$out[]='<td class="left-part">';
					$out[]='<div id="left-menu">';
						$out[]='<a href="#list">управление контентом</a>';
						$out[]='<a href="#config">настройка шаблонов</a>';
					$out[]='</div>';
					$out[]='<br><br>';
					$out[]='<div>По-умолчанию &mdash; <select id="current-lang">';
						foreach($this->languages as $code => $name){
							$out[]='<option value="'.$code.'">'.$name.'</option>';
						}
					$out[]='</select> язык.</div>';
				$out[]='</td>';
				$out[]='<td class="central-part" id="active-screen" width="530"><div>';
				$out[]='</div></td>';
			$out[]='</tr>';
		$out[]='</table>';
		return join("\n", $out);
	}
#USER FUNCTIONS	
	function deleteObject($id){
		if(!!($object = $this->db->select("objects", "WHERE `id`='".$id."' LIMIT 1"))){ 
			
			if( $object['class_id']>0 && !!($fields = $this->getClassFields($object['class_id'])) ){
				$values = $this->getObjectValues($id, $object['class_id']);
				foreach($fields as $f){
					if(@$f['type']=='file' && !!$values['field_'.$f['id']]){
						@unlink(_UPLOADS_ABS_."/".$values['field_'.$f['id']]);
					}
				}
			}
			$this->db->delete("class_".$object['class_id'], "WHERE `object_id`='".$object['id']."'");
			$this->db->delete("objects", "WHERE `id`='".$id."' LIMIT 1");
			if(!!$childs = $this->db->select("objects", "WHERE `head`='".$id."'")){
				foreach($childs as $ch){
					$this->deleteObject($ch['id']);
				}
			}
			return true;
		}
		return false;
	}

	function moveObject($id, $to){
		foreach( $this->getObjectParents($to) as $p){
			if($p['id']==$id) return false;
		}
		$this->db->update('objects', array("head"=>$to), "WHERE `id`='".$id."'");
		return true;
	}
	
	function sortObject($id, $to='up'){
	
		if(!is_numeric($id) || !in_array($to, array("up", "down"))) return false;
		if($to=='up'){
			if(!($obj = $this->db->select('objects', "WHERE `id`='".$id."' LIMIT 1")) || !($upper = $this->db->select('objects', "WHERE `head`='".$obj['head']."' AND `sort`<'".$obj['sort']."' ORDER BY sort DESC LIMIT 1"))) return false;
			$this->db->update('objects', array_merge($obj, array("sort"=>$upper['sort'])), "WHERE `id`='".$obj['id']."'");
			$this->db->update('objects', array_merge($upper, array("sort"=>$obj['sort'])), "WHERE `id`='".$upper['id']."'");
			return true;
		}
		if(!($obj = $this->db->select('objects', "WHERE `id`='".$id."' LIMIT 1")) || !($downer = $this->db->select('objects', "WHERE `head`='".$obj['head']."' AND `sort`>'".$obj['sort']."' ORDER BY sort LIMIT 1"))) return false;
		$this->db->update('objects', array_merge($obj, array("sort"=>$downer['sort'])), "WHERE `id`='".$obj['id']."'");
		$this->db->update('objects', array_merge($downer, array("sort"=>$obj['sort'])), "WHERE `id`='".$downer['id']."'");
		return true;
	}
	
	function getObjects($head=0, $limit=''){
		return array(
			"objects"=>$this->db->select("objects as o1", "LEFT JOIN objects as o2 ON o2.head=o1.id LEFT JOIN classes as c1 ON c1.id=o1.class_id WHERE o1.head='".$head."' GROUP BY o1.id ORDER BY sort,id".$limit, "o1.*, c1.name as class_name, COUNT(o2.id) as inside"),
			"total_count"=>$this->db->count('objects', "WHERE `head`='".$head."'"),
			"parents"=>$this->getObjectParents($head)
		);
	}
	
	function getObject( $id){
		if( !is_numeric($id) || !($obj = $this->db->select('objects', "WHERE `id`='".$id."' LIMIT 1")) ) return array();
		return array_merge( $obj, array('values'=>($obj['class_id']>0?$this->getObjectValues($id, $obj['class_id']):array())) );
	}
	
	function getObjectValues( $id, $class_id ){
		if( !is_numeric($class_id) ) return array();
		return $this->db->select('class_'.$class_id, "WHERE `object_id`='".$id."' AND `lang`='".$this->lang."' LIMIT 1");
	}
	
	function createObjectAndFields($object, $fields){
		if( empty($object['name']) || !is_numeric($object['class_id']) ) return false;
		return $this->createObjectFields( $this->createObject($object), $fields );
	}
	
	function createObject($object){
		if( $this->db->insert('objects', $object) ){ 
			return mysql_insert_id($this->db->link);
		}
		return false;
	}
	
	function createObjectFields($object_id, $fields){
		if(!$object = $this->db->select("objects", "WHERE `id`='".$object_id."' LIMIT 1")) return false;
		
		$temp = $this->getClassFields( $object['class_id'] );
		foreach($temp as $f){
			$class_fields[$f['id']]=$f;
		}
		
		$out = array();
		foreach($fields as $id=>$value){
			if($class_fields[$id]['type']=='password'){ 
				if(@$value) $out['field_'.$id]=sha1($value);
			}else $out['field_'.$id]=$value;
		}
		$out['lang'] = $this->lang;
		$out['object_id'] = $object['id'];
		if( $this->db->insert("class_".$object['class_id'], $out) ) return true;
		return false;
	}
	
	function editObjectAndFields($object, $fields){
		if( !isset($object['id']) || !isset($object['name']) || !is_numeric($object['class_id'])) return false;
		return ( $this->editObject($object) && $this->editObjectFields($object['id'], $fields) );
	}
	
	function editObject($object){
		if( empty($object['id']) || empty($object['name'])) return false;
		if(!$object['class_id']){
			if(!!($old = $this->db->select("objects", "WHERE `id`='".$object['id']."' LIMIT 1")) && !!$old['class_id']){
				$this->db->delete('class_'.$old['class_id'], "WHERE `object_id`='".$old['id']."'");
			}			
		}
		return $this->db->update("objects", $object, "WHERE `id`='".$object['id']."'");
	}
	
	function editObjectFields($object_id, $fields){
		if(!$object = $this->db->select("objects", "WHERE `id`='".$object_id."' LIMIT 1")) return false;
		
		$temp = $this->getClassFields( $object['class_id'] );
		foreach($temp as $f){
			$class_fields[$f['id']]=$f;
		}
		
		$out = array();
		foreach($fields as $id=>$value){
			if($class_fields[$id]['type']=='password'){ 
				if(@$value) $out['field_'.$id]=sha1($value);
			}else $out['field_'.$id]=$value;
		}
		$out['object_id'] = $object['id'];
		$out['lang'] = $this->lang;
		#ПРОВЕРЯЕМ ЕСТЬ ЗАПИСЬ В ПОЛЯХ У КЛАССА ОБЪЕКТА, ЕСЛИ НЕТ ЗНАЧИТ ПРИ РЕДАТИРОВАНИИ КЛАСС БЫЛ ИЗМЕНЕН
		if( $langs = $this->db->select('class_'.$object['class_id'], "WHERE `object_id`='".$object['id']."'", 'lang') ){
			#КЛАСС НЕ МЕНЯЛИ, НУЖНО ПРОВЕРИТЬ ЕСТЬ ЛИ ИНТЕРЕСНЫЙ НАМ ЯЗЫК ИЛИ НУЖНО ЕГО СОЗДАТЬ
			#ЯЗЫК ЕСТЬ, СОХРАНЯЕМ ПОЛЯ
			if( !!in_array($this->lang, $langs) )	return $this->db->update('class_'.$object['class_id'], $out, "WHERE `object_id`='".$object['id']."' AND `lang`='".$this->lang."'");
			else return $this->db->insert('class_'.$object['class_id'], $out);
		}else{
			#КЛАСС МЕНЯЛИ, СУЧКИ!
			#ЧИСТИМ ВСЕ КЛАССЫ ОТ ВОЗМОЖНОГО ПРИСУТСТВИЯ ОБЪЕКТА
			foreach($this->db->select("classes", "ORDER BY id") as $c){
				$this->db->delete('class_'.$c['id'], "WHERE `object_id`='".$object['id']."'");
			}
			
			#ДОБАВЛЯЕМ НОВУЮ ЗАПИСЬ С ПОЛЯМИ В ТАБЛИЦУ КЛАССА
			return $this->db->insert('class_'.$object['class_id'], $out);
		}
	}
	
	function getObjectParents($head, $parents=array()){
		if(!$head) return array_reverse($parents);
		$parents[]= $parent = $this->db->select("objects", "WHERE `id`='".$head."' LIMIT 1");
		return $this->getObjectParents($parent['head'], $parents);
	}
	
	function deleteClass( $class_id ){
		if( !is_numeric($class_id) || !($class = $this->db->select('classes', "WHERE `id`='".$class_id."' LIMIT 1"))) return false;
		$this->db->mysql_query('DROP TABLE class_'.$class_id);
		$this->db->delete('fields', "WHERE `class_id`='".$class_id."'");
		$this->db->delete('classes', "WHERE `id`='".$class_id."' LIMIT 1");
		$this->db->update('objects', array("class_id"=>0), "WHERE `class_id`='".$class_id."'");
		return true;
	}
	
	function createClassAndFields( $class, $fields ){
		if( empty($class['name']) || !count($fields) ) return false;
		return $this->createClassFields( $this->createClass($class), $fields );
	}
	
	function createClass( $class ){
		if( $this->db->insert('classes', $class) ){ 
			$class_id = mysql_insert_id($this->db->link);
			$this->db->mysql_query('CREATE TABLE `class_'.$class_id.'` ( `id` int(11) NOT NULL auto_increment, `object_id` int(11) NOT NULL default \'0\', `lang` VARCHAR(250) NOT NULL default \'ru\', PRIMARY KEY  (`id`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
			return $class_id;
		}
		return false;
	}
	
	function createClassFields( $class_id, $fields ){
		if( !$class_id || !$fields ) return false;
		$index = 0;
		foreach($fields as $f){
			$f['class_id'] = $class_id;
			if( !$this->db->insert('fields', $f) ) continue;
			$field_id = mysql_insert_id($this->db->link);
			$this->db->mysql_query('ALTER TABLE class_'.$class_id.' ADD field_'.$field_id.' '.$this->getFieldType($f['type']));
		}
		return true;
	}
	
	function editClassAndFields( $class, $fields ){
		if( empty($class['id']) || empty($class['name']) || !count($fields)) return false;
		return ( $this->editClass($class) && $this->editClassFields($class['id'], $fields) );
	}
	
	function editClass( $class ){
		if( empty($class['id']) || empty($class['name']) ) return false;
		return $this->db->update("classes", $class, "WHERE `id`='".$class['id']."'");
	}
	
	function editClassFields($class_id, $newFields){
		if(!$class_id || !($class = $this->db->select('classes', "WHERE `id`='".$class_id."' LIMIT 1")) || !count($newFields) || !($fields = $this->db->select('fields', "WHERE `class_id`='".$class_id."' ORDER BY sort,id"))) return false;
		foreach($newFields as $nf){
			$nf['class_id'] = $class_id;
			if( empty($nf['id']) ){
				if( !$this->db->insert('fields', $nf) ) continue;
				$field_id = mysql_insert_id($this->db->link);
				$this->db->mysql_query('ALTER TABLE class_'.$class_id.' ADD field_'.$field_id.' '.$this->getFieldType($nf['type']));
				continue;
			}
			foreach($fields as $key=>$f){
				if($nf['id'] == $f['id']){
					if($nf['sort'] != $f['sort'] || $nf['name'] != $f['name'] || $nf['type'] != $f['type'] || $nf['p1'] != $f['p1'] || $nf['p2'] != $f['p2'] || $nf['p3'] != $f['p3']){
						$this->db->update('fields', $nf, "WHERE `id`='".$nf['id']."' LIMIT 1");
						if( $nf['type'] != $f['type'] ){
							$this->db->mysql_query('ALTER TABLE class_'.$class_id.' MODIFY field_'.$nf['id'].' '.$this->getFieldType($nf['type']));
						}
					}
					unset($fields[$key]);
					break;
				}
			}
		}
		//ЧИСТКА УДАЛЕННЫХ ПОЛЕЙ, оставшиеся в массиве мы должны удалить, их нет в новом списке полей
		if( $fields ){
			$removeIDS = array();
			foreach($fields as $f){
				$removeIDS[]=$f['id'];
				$this->db->mysql_query('ALTER TABLE class_'.$class_id.' DROP COLUMN field_'.$f['id']);
			}
			$this->db->delete('fields', "WHERE `id` IN (".join(",", $removeIDS).")");
		}
		return true;
	}
	
	function getFieldType( $type ){
		switch( $type ){
			case 'text': return 'VARCHAR(250)';
			case 'password': return 'VARCHAR(250)';
			case 'number': return 'INT(11)';
			case 'date': return 'DATE';
			case 'datetime': return 'DATETIME';
			case 'checkbox': return 'TINYINT(1)';
			case 'textarea': return 'TEXT';
			case 'html': return 'LONGTEXT';
			case 'select': return 'TEXT';
			case 'radio': return 'TEXT';
			case 'file': return 'VARCHAR(250)';
			default: return 'VARCHAR(250)';
		}
	}
	
	function getClasses(){
		return $this->db->select('classes', "ORDER BY name");
	}
	
	function getClass( $id ){
		if( !is_numeric($id) || !($class = $this->db->select('classes', "WHERE `id`='".$id."' LIMIT 1")) ) return array();
		return array_merge( $class, array('fields'=>$this->getClassFields($id)) );
	}
	
	function getClassFields( $class_id ){
		if( !is_numeric($class_id) ) return array();
		return $this->db->select('fields', "WHERE `class_id`='".$class_id."' ORDER BY sort,id");
	}
}
?>