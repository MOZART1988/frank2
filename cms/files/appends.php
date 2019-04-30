<?
class appends{
var $db;
	
	function __construct(){
		$this->db = new mysql('localhost', 'kirking14_frank', 'kirking14_frank', 'oldsquare123');
	}
	
	function vd($text){		
		echo '<pre>';
		print_r($text);
		echo '</pre>';
	}
	
	function auth(){
		return @$this->checkAuth( $_SESSION['cms_root_auth']['u'], $_SESSION['cms_root_auth']['p']);
	}
	
	function checkAuth( $l, $p ){
		if( !file_exists(_CACHE_ABS_.'/auth.php') || !(include(_CACHE_ABS_.'/auth.php')) ){ 
			$this->changeAuth('fp', 'prohvost');
			return false;
		}
		if( $l!=$login || md5($p)!=$pass) return false;
		return true;
	}
	
	function changeAuth($l, $p){
		$cache = array('<?');
		$cache[]='$login="'.str_replace('"', '', $l).'";';
		$cache[]='$pass="'.md5($p).'";';
		$cache[]='?>';
		if( file_put_contents( _CACHE_ABS_.'/auth.php', join("\n", $cache)) ) return 'ok';
		return 'error';
	}
	
	function areaJS( $text_or_array ){
		if(is_array($text_or_array)) $text_or_array = join("\n", $text_or_array);
		return '<script type="text/javascript">'.$text_or_array.'</script>'."\n";
	}
	
	function initJS(){
		if(!$this->js) return null;
		$out = array();
		foreach($this->js as $file){
			$out[]='<script type="text/javascript" src="'.$file.'"></script>';
		}
		return join("\n", $out)."\n";
	}
	
	function initCSS(){
		if(!$this->css) return null;
		$out = array();
		foreach($this->css as $file){
			$out[]='@import url("'.$file.'");';
		}
		return join("\n", $out)."\n";
	}
	
	function newError( $str ){
		$this->errors[]=$str;
		return true;
	}
	
	function callErrors(){
		if( !$this->errors ) return '';
		return '<div class="error"><div>'.join('</div><div>', $this->errors).'</div></div><br>';
	}
	
	function lower($text){
		$UP_CASE=array('A'=>'a', 'B'=>'b', 'C'=>'c', 'D'=>'d', 'E'=>'e', 'F'=>'f', 'G'=>'g', 'H'=>'h', 'I'=>'i', 'J'=>'j', 'K'=>'k', 'L'=>'l', 'M'=>'m', 'N'=>'n', 'O'=>'o', 'P'=>'p', 'Q'=>'q', 'R'=>'r', 'S'=>'s', 'T'=>'t', 'U'=>'u', 'V'=>'v', 'W'=>'w', 'X'=>'x', 'Y'=>'y', 'Z'=>'z', 'А'=>'а', 'Б'=>'б', 'В'=>'в', 'Г'=>'г', 'Д'=>'д', 'Е'=>'е', 'Ё'=>'ё', 'Ж'=>'ж', 'З'=>'з', 'И'=>'и', 'Й'=>'й', 'К'=>'к', 'Л'=>'л', 'М'=>'м', 'Н'=>'н', 'О'=>'о', 'П'=>'п', 'Р'=>'р', 'С'=>'с', 'Т'=>'т', 'У'=>'у', 'Ф'=>'ф', 'Х'=>'х', 'Ц'=>'ц', 'Ч'=>'ч', 'Ш'=>'ш', 'Щ'=>'щ', 'Ъ'=>'ъ', 'Ы'=>'ы', 'Ь'=>'ь', 'Э'=>'э', 'Ю'=>'ю', 'Я'=>'я');
		return strtr($text,  $UP_CASE);
	}

	function upper($text){
		$LOW_CASE=array("a"=>"A", "b"=>"B", "c"=>"C", "d"=>"D", "e"=>"E", "f"=>"F", "g"=>"G", "h"=>"H", "i"=>"I", "j"=>"J", "k"=>"K", "l"=>"L", "m"=>"M", "n"=>"N", "o"=>"O", "p"=>"P", "q"=>"Q", "r"=>"R", "s"=>"S", "t"=>"T", "u"=>"U", "v"=>"V", "w"=>"W", "x"=>"X", "y"=>"Y", "z"=>"Z", "а"=>"А", "б"=>"Б", "в"=>"В", "г"=>"Г", "д"=>"Д", "е"=>"Е", "ё"=>"Ё", "ж"=>"Ж", "з"=>"З", "и"=>"И", "й"=>"Й", "к"=>"К", "л"=>"Л", "м"=>"М", "н"=>"Н", "о"=>"О", "п"=>"П", "р"=>"Р", "с"=>"С", "т"=>"Т", "у"=>"У", "ф"=>"Ф", "х"=>"Х", "ц"=>"Ц", "ч"=>"Ч", "ш"=>"Ш", "щ"=>"Щ", "ъ"=>"Ъ", "ы"=>"Ы", "ь"=>"Ь", "э"=>"Э", "ю"=>"Ю", "я"=>"Я");
		return strtr($text,  $LOW_CASE);
	}
	
	function sklon($num, $arr){
		if($num==1) $out = $arr[0];
		else if($num>=2 && $num<=4) $out = $arr[1];
		else if(($num>=5 && $num <=19) or $num==0) $out = $arr[2];
		else{
			$num1 = substr($num,-1,1);
			$num2 = substr($num,-2,1);
			if($num2==1) $out = $arr[2];
			else if($num1==1) $out = $arr[0];
			else if($num1>=2 && $num1<=4) $out = $arr[1];
			else if(($num1>=5 && $num1 <=9) or $num1==0) $out = $arr[2];
		}
		return $num." ".$out;
	}
	
	function pages($total_count, $on_one_page, $view_page_links_per_side=5, $page_attributes=array(), $url = false){
		if(!$total_count || !$on_one_page || $total_count == 0) array('html'=>'', 'start'=>'0');
		if(!isset($_REQUEST['pg']) || !is_numeric($current_page = $_REQUEST['pg']) || $current_page>ceil($total_count/$on_one_page)) $current_page = 1;
		$attrs = array();
		foreach($page_attributes as $k=>$v){
			if ($k != 'pg') { $attrs[]=$k.'='.$v; }
		}
		$attrs = join("&", $attrs);
		
		$index = $view_page_links_per_side;
		$count_pages = ceil( $total_count/$on_one_page );
		$start_from  = ( $current_page * $on_one_page ) - $on_one_page;
		
		if($count_pages < 2) return array('html'=>'', 'start'=>'0');
		
		$start = 1; $end = ($index*2+1<=$count_pages?$index*2+1:$count_pages);
		if($current_page>$index+1){
			if($current_page+$index<=$count_pages){
				$start = $current_page-$index;
				$end = $current_page+$index;
			}else{
				$start = $current_page - ($index*2-($count_pages - $current_page))<=0?1:$current_page - ($index*2-($count_pages - $current_page));
				$end = $count_pages;
			}
		}
		
		$html = array('<div class="page-links">');
		if($current_page>1){ 
			if($start>1){ 
				if(!$url){ 
					$html[]='<a href="?pg=1'.($attrs?'&'.$attrs:'').'">вначало</a>';
				}else $html[]='<a href="'.str_replace("#pg#", 1, $url).'">вначало</a>';
			}
			if(!$url){ 
				$html[]='<a href="?pg='.($current_page-1).($attrs?'&'.$attrs:'').'">назад</a>';
			}else $html[]='<a href="'.str_replace("#pg#", ($current_page-1), $url).'">назад</a>';
		}
		
		for($i = intval($start); $i<=intval($end); $i++){
			if($i==$current_page) $html[]='<span>'+$i+'</span>';
			else{ 
				if(!$url){
					$html[]='<a href="?pg='.($i).($attrs?'&'.$attrs:'').'">'.$i.'</a>';
				}else $html[]='<a href="'.str_replace("#pg#", $i, $url).'">'.$i.'</a>';
			}
		}
		
		if($current_page!=$count_pages){ 
			if(!$url){ 
				$html[]='<a href="?pg='.($current_page+1).($attrs?'&'.$attrs:'').'">вперед</a>';
			}else $html[]='<a href="'.str_replace("#pg#", ($current_page+1), $url).'">вперед</a>';
			if($end!=$count_pages){ 
				if(!$url){
					$html[]='<a href="?pg='.$count_pages.($attrs?'&'.$attrs:'').'">в конец</a>';
				}else $html[]='<a href="'.str_replace("#pg#", ($count_pages-1), $url).'">в конец</a>';
			}
		}
		$html[]='</div>';
		$from = (($current_page-1)*$on_one_page+1);
		$to = ($current_page-1)*$on_one_page+$on_one_page;
		$html[]='<div>показано '.$from.'&ndash;'.($to>$total_count?$total_count:$to).' из '.$total_count.'</div>';
		
		
		return array('html'=>join("\n", $html), 'start'=>$start_from);
	}
		
	function json($a=false){
		if(is_null($a)) return 'null';
		if($a === false) return 'false';
		if($a === true) return 'true';
		if(is_scalar($a)){
			if(is_float($a)){
				return floatval(str_replace(",", ".", strval($a)));
			}else if(is_numeric($a)){
				return $a;
			}else{
				$jsonReplaces = array("\\"=>'\\\\', "/"=>'\\/', "\n"=>'\\n', "\t"=>'\\t', "\r"=>'\\r', "\b"=>'\\b', "\f"=>'\\f', '"'=>'\"');
				return '"'.strtr($a, $jsonReplaces).'"';
			}
		}else if( !is_array($a) ) return false;
		$isList = true;
		$checkIndex = 0;
		foreach($a as $k=>$v){
			if( !is_numeric($k) || $k!=$checkIndex++ ){
				$isList = false;
				break;
			}
		}
		$result = array();
		if($isList){
			foreach ($a as $v) $result[] = $this->json($v);
			return '[' . join(',', $result) . ']';
		}else{
			foreach ($a as $k => $v) $result[] = $this->json($k).':'.$this->json($v);
			return '{' . join(',', $result) . '}';
		}
	}
	
	function getFileExtension($fileName){
		return substr($fileName, strrpos($fileName, '.' )+1);
	}
}
?>