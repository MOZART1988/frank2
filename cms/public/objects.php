<?
/*
Title: OBJECTS PUBLIC API v 1.5.3
Author: Derevyanko Mikhail <m-derevyanko@ya.ru>
Date: 17.08.2009
*/
class objects extends appends{
	
	var $last;
	var $lang;
	function __construct(&$lang){
		parent::__construct();
		$this->last = null;
		$this->lang = &$lang;
	}
	#ÂÛÁÎĞÊÀ ÃĞÓÏÏÛ ÎÁÚÅÊÒÎÂ ÈÇ ĞÎÄÈÒÅËß (0 - êîğåíü), âòîğûì ïàğàìåòğîâ îãğàíè÷èâàåòñÿ ëèìèò âûáîğêè (SQL)
	function getObjectsList($head, $limit=false){
		if(!is_numeric($head) || !($list = $this->db->select("objects as o1", "LEFT JOIN objects as o2 ON o2.head=o1.id WHERE o1.active='1' AND o1.head='".$head."' GROUP BY o1.id ORDER BY sort,id".($limit?' LIMIT '.$limit:''), "o1.*, COUNT(o2.id) as inside"))) return array();
		return $list;
	}
	#ÂÛÁÎĞÊÀ ÃĞÓÏÏÛ ÎÁÚÅÊÒÎÂ ÈÇ ĞÎÄÈÒÅËß Ñ ÏÎËßÌÈ ÇÍÀ×ÅÍÈÉ ÎÁÚÅÊÒÎÂ
	function getFullObjectsList($head, $limit=false){
		if(!is_numeric($head) || !($list = $this->getObjectsList($head, $limit))) return array();
		$out = array();
		if(!empty($list['id']) && !empty($list['class_id'])) return $list+$this->getObjectFields($list['id'], $list['class_id']);
		foreach($list as $o){
			$out[]=$o+$this->getObjectFields($o['id'], $o['class_id']);
		}
		return $out;
	}
	#ÂÛÁÎĞÊÀ ÊÎËÈ×ÅÑÒÂÀ ÎÁÚÅÊÒÎÂ ÈÇ ĞÎÄÈÒÅËß Ñ ÇÀÄÀÍÍÛÌ ÊËÀÑÑÎÌ
	function getObjectsCount($head, $class_id, $sql="AND o.active='1'"){
		$sql = "WHERE ".(!!$class_id ? "c.lang='".$this->lang."' AND ":'').($head != -1 ? "o.head='".$head."' AND " : '')."o.class_id='".$class_id."'".(@$sql?" ".$sql:'');
		if(!!$class_id) $sql = "LEFT JOIN class_".$class_id." as c ON o.id=c.object_id ".$sql;
		if(!is_numeric($head) || !is_numeric($class_id) || !($count = $this->db->count("objects as o", $sql)) ) return 0;
		return $count;
	}
	#ÂÛÁÎĞÊÀ ÃĞÓÏÏÛ ÎÁÚÅÊÒÎÂ ÈÇ ĞÎÄÈÒÅËß Ñ ÇÀÄÀÍÍÛÌ ÊËÀÑÑÎÌ, âîçìîæíà ñîğòèğîâêà ïî ïîëÿì êëàññà
	function getObjectsListByClass($head, $class_id, $sql="AND o.active='1' ORDER BY o.sort"){
		$sql = "WHERE ".(!!$class_id ? "c.lang='".$this->lang."' AND ":'').($head != -1 ? "o.head='".$head."' AND " : '')."o.class_id='".$class_id."'".(@$sql?" ".$sql:'');
		if(!!$class_id) $sql = "LEFT JOIN class_".$class_id." as c ON o.id=c.object_id ".$sql;
		if(!is_numeric($head) || !is_numeric($class_id) || !($list = $this->db->select("objects as o", $sql, "o.*")) ) return array();
		return $list;
	}
	#ÂÛÁÎĞÊÀ ÃĞÓÏÏÛ ÎÁÚÅÊÒÎÂ ÈÇ ĞÎÄÈÒÅËß Ñ ÇÀÄÀÍÍÛÌ ÊËÀÑÑÎÌ ÑÎ ÇÍÀ×ÅÍÈßÌÈ ÏÎËÅÉ ÎÁÚÅÊÒÎÂ
	function getFullObjectsListByClass($head, $class_id, $sql="AND o.active=1 ORDER BY o.sort"){
		if(!is_numeric($head) || !($list = $this->getObjectsListByClass($head, $class_id, $sql))) return array();
		$out = array();
		if (!empty($list['id']) && !empty($list['class_id'])) return $list+$this->getObjectFields($list['id'], $list['class_id']);
		foreach($list as $o){
			$out[]=$o+$this->getObjectFields($o['id'], $o['class_id']);
		}
		return $out;
	}
	#ÂÛÁÎĞÊÀ ÎÄÍÎÃÎ ÎÁÚÅÊÒÀ Ñ ÇÀÄÀÍÍÛÌ ID, âòîğûì ïàğàìåòğîì óêàçûâàåòñÿ çàïîìíèòñÿ ëè ID äàííîãî îáúåêòà â ïåğåìåííîé $this->last èëè íåò.
	function getObject( $id, $remember=true ){
		if(!is_numeric($id) || !($obj = $this->db->select("objects as o1", "LEFT JOIN objects as o2 ON o2.head=o1.id WHERE o1.id='".$id."' GROUP BY o1.id LIMIT 1", "o1.*, COUNT(o2.id) as inside"))) return false;
		if(!!$remember) $this->last = $obj['id'];
		return $obj;
	}
	#ÂÛÁÎĞÊÀ ÎÄÍÎÃÎ ÎÁÚÅÊÒÀ Ñ ÇÀÄÀÍÍÛÌ ID ÂÊËŞ×Àß ÇÍÀ×ÅÍÈß ÏÎËÅÉ
	function getFullObject( $id, $remember=true ){
		if(!is_numeric($id) || !($obj = $this->getObject( $id, $remember ))) return false;
		return $obj+$this->getObjectFields($obj['id'], $obj['class_id']);
	}
	#ÂÛÁÎĞÊÀ ÇÍÀ×ÅÍÈÉ ÏÎËÅÉ ÎÁÚÅÊÒÀ ÏÎ ID ÎÁÚÅÊÒÀ È ID ÊËÀÑÑÀ
	function getObjectFields($object_id, $class_id){
		if(!$class_id) return array();
		$fields = array();
		$field_values = $this->db->select('class_'.$class_id, "WHERE `object_id`='".$object_id."' AND `lang`='".$this->lang."' LIMIT 1");
		foreach($this->db->select('fields', "WHERE `class_id`='".$class_id."' ORDER BY sort") as $f){
			if($f['type']=='html'){
				$value = isset($field_values['field_'.$f['id']]) ? htmlspecialchars_decode($field_values['field_'.$f['id']]) : '';
			}else $value = isset($field_values['field_'.$f['id']]) ? $field_values['field_'.$f['id']] : '';
			$fields[$f['name']] = $value;
			$fields[$f['id']] = $value;
		}
		return $fields;
	}
	#ÂÛÁÎĞÊÀ ÏÎËÅÉ ÊËÀÑÑÀ, Ñ ÑÎÎÒÂÅÑÒÂÓŞÙÈÌÈ ÏÎËßÌÈ, ÊÎÒÎĞÛÅ ÇÀÊĞÅÏËÅÍÛ ÇÀ ÏÎËßÌÈ (Â ÒÀÁËÈÖÅ ÏÎËÅÉ)
	function getClassFields($class_id){
		return $this->db->select('fields', "WHERE `class_id`='".$class_id."' ORDER BY sort");
	}
	#ÑÎÇÄÀÍÈÅ ÎÁÚÅÊÒÀ È ÏÎËÅÉ ÎÄÍÈÌ ÌÅÒÎÄÎÌ
	function createObjectAndFields($object, $fields){
		if( empty($object['name']) || !is_numeric($object['class_id']) ) return false;
		return $this->createObjectFields( $this->createObject($object), $fields );
	}
	#ÑÎÇÄÀÍÈÅ ÎÁÚÅÊÒÀ
	function createObject($object){
		if(empty($object['sort'])) $object['sort'] = time();
		if(!isset($object['active'])) $object['active'] = 1;
		if( $this->db->insert('objects', $object) ){ 
			return mysql_insert_id($this->db->link);
		}
		return false;
	}
	#ÑÎÇÄÀÍÈÅ ÏÎËÅÉ ÎÁÚÅÊÒÀ
	function createObjectFields($object_id, $fields){
		if(!$object = $this->db->select("objects", "WHERE `id`='".$object_id."' LIMIT 1")) return false;
		
		$field_ids = array();
		foreach($this->getClassFields($object['class_id']) as $f){
			$field_ids[$f['name']]=$f['id'];
		}		
		
		$out = array();
		foreach($fields as $id=>$value){
			if(!is_numeric($id)){
				if(!empty($field_ids[$id])) $id = $field_ids[$id];
				else continue;
			}
			$out['field_'.$id]=$value;
		}
		$out['lang'] = $this->lang;
		$out['object_id'] = $object['id'];
		if( $this->db->insert("class_".$object['class_id'], $out) ) return $object_id;
		return false;
	}
	#ĞÅÄÀÊÒÈĞÎÂÀÍÈÅ ÎÁÚÅÊÒÀ È ÏÎËÅÉ ÎÄÍÎÂĞÅÌÅÍÍÎ
	function editObjectAndFields($object, $fields){
		if( !isset($object['id']) || !is_numeric($object['class_id'])) return false;
		return ( $this->editObject($object) && $this->editObjectFields($object['id'], $fields) );
	}
	#ĞÅÄÀÊÒÈĞÎÂÀÍÈÅ ÎÁÚÅÊÒÀ
	function editObject($object){
		if( empty($object['id']) ) return false;
		return $this->db->update("objects", $object, "WHERE `id`='".$object['id']."'");
	}
	#ĞÅÄÀÊÒÈĞÎÂÀÍÈÅ ÏÎËÅÉ ÎÁÚÅÊÒÀ
	function editObjectFields($object_id, $fields){
		if(!$object = $this->db->select("objects", "WHERE `id`='".$object_id."' LIMIT 1")) return false;
		
		$field_ids = array();
		foreach($this->getClassFields($object['class_id']) as $f){
			$field_ids[$f['name']]=$f['id'];
		}	
		
		$out = array();
		foreach($fields as $id=>$value){
			if(!is_numeric($id)){
				if(!empty($field_ids[$id])) $id = $field_ids[$id];
				else continue;
			}
			$out['field_'.$id]=$value;
		}
		$out['object_id'] = $object['id'];
		$out['lang'] = $this->lang;
		#ÏĞÎÂÅĞßÅÌ ÅÑÒÜ ÇÀÏÈÑÜ Â ÏÎËßÕ Ó ÊËÀÑÑÀ ÎÁÚÅÊÒÀ, ÅÑËÈ ÍÅÒ ÇÍÀ×ÈÒ ÏĞÈ ĞÅÄÀÒÈĞÎÂÀÍÈÈ ÊËÀÑÑ ÁÛË ÈÇÌÅÍÅÍ
		if( $langs = $this->db->select('class_'.$object['class_id'], "WHERE `object_id`='".$object['id']."'", 'lang') ){
			#ÊËÀÑÑ ÍÅ ÌÅÍßËÈ, ÍÓÆÍÎ ÏĞÎÂÅĞÈÒÜ ÅÑÒÜ ËÈ ÈÍÒÅĞÅÑÍÛÉ ÍÀÌ ßÇÛÊ ÈËÈ ÍÓÆÍÎ ÅÃÎ ÑÎÇÄÀÒÜ
			#ßÇÛÊ ÅÑÒÜ, ÑÎÕĞÀÍßÅÌ ÏÎËß
			if( !!in_array($this->lang, $langs) )	return $this->db->update('class_'.$object['class_id'], $out, "WHERE `object_id`='".$object['id']."' AND `lang`='".$this->lang."'");
			else return $this->db->insert('class_'.$object['class_id'], $out);
		}else{
			#ÊËÀÑÑ ÌÅÍßËÈ, ÑÓ×ÊÈ!
			#×ÈÑÒÈÌ ÂÑÅ ÊËÀÑÑÛ ÎÒ ÂÎÇÌÎÆÍÎÃÎ ÏĞÈÑÓÒÑÒÂÈß ÎÁÚÅÊÒÀ
			foreach($this->db->select("classes", "ORDER BY id") as $c){
				$this->db->delete('class_'.$c['id'], "WHERE `object_id`='".$object['id']."'");
			}
			
			#ÄÎÁÀÂËßÅÌ ÍÎÂÓŞ ÇÀÏÈÑÜ Ñ ÏÎËßÌÈ Â ÒÀÁËÈÖÓ ÊËÀÑÑÀ
			return $this->db->insert('class_'.$object['class_id'], $out);
		}
	}
	#ÑÎĞÒÈĞÎÂÊÀ ÎÁÚÅÊÒÀ
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
	#ÎÏĞÅÄÅËÅÍÈÅ HTML-ÊÎÄÀ ÏÎËß ÏÎ ÒÈÏÓ
	function getFieldInput($k, $f){
		switch( $f['type'] ){
			case 'text': return '<input type="text" name="fields['.$k.']" value="'.(@$f['value']?$f['value']:'').'" style="width:'.$f['p1'].'px;">';
			case 'number': return '<input type="text" name="fields['.$k.']" value="'.(@$f['value']?$f['value']:'').'" style="width:'.$f['p1'].'px;">';
			case 'date': return 'none';
			case 'checkbox': return 'none';
			case 'textarea': return '<textarea name="fields['.$k.']" style="width:'.$f['p1'].'px; height:'.$f['p2'].'px;"></textarea>';
			case 'html': return 'none';
			case 'select': return 'none';
			case 'radio': return 'none';
			case 'file': return 'none';
			default: return '<input type="text" name="fields['.$k.']" value="'.(@$f['value']?$f['value']:'').'" style="width:'.$f['p1'].'px;">';
		}
	}
	#ĞÅÊÓĞÑÈÂÍÎÅ ÓÄÀËÅÍÈÅ ÎÁÚÅÊÒÀ È ÂÑÅÕ ÅÃÎ ÏÎÒÎÌÊÎÂ (Ñ ÓÄÀËÅÍÈÅÌ ÇÍÀ×ÅÍÈÉ ÏÎËÅÉ ÂÑÅÕ ßÇÛÊÎÂÛÕ ÂÅĞÑÈÉ)
	function deleteObject($id){
		if(!!($object = $this->db->select("objects", "WHERE `id`='".$id."' LIMIT 1"))){ 
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
}
?>