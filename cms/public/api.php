<?
/*
Title: РАСЧУДЕСНОЕ МНОГОЯЗЫКОВОЕ API-ШАБЛОНИЗАТОР С ДВОЙНОЙ БУФЕРИЗАЦИЕЙ ВЫВОДА, МОДУЛЬ-САЙД ИНКЛЮДАМИ И ВСПОМОГАТЕЛЬНЫМИ ЮЗЕР-ФУНКЦИЯМИ
Author: Derevyanko Mikhail <m-derevyanko@ya.ru>
Last UpDate: 21.05.2010
*/
error_reporting(E_ALL);
ini_set("display_errors", "On");
session_start();
include(str_replace("\\", "/", dirname(__FILE__)).'/../cfg.php');
include_once(_FILES_ABS_."/mysql.php");
include_once(_FILES_ABS_."/appends.php");
include_once(_PUBLIC_ABS_."/objects.php");
include_once(_PUBLIC_ABS_."/strings.php");

class api extends appends{

public $template;
public $objects;
public $strings;
public $body;
public $arguments;
public $lang;
public $languages;

	function __construct(){
		parent::__construct();
		
		$this->template = '/pages.html';
		
		$this->body = null;
		$this->arguments = array();
		$this->languages = array(
			"ru"=>"Русский"
		);
		$this->lang = 'ru';
		
		$this->objects = new objects( $this->lang );
		$this->strings = new Strings( $this->lang );
	}
	
	function arg($name, $value){
		$this->arguments[$name] = $value;
	}
	
	function args($arr){
		if($arr) $this->arguments = array_merge($this->arguments, $arr);
	}
	
	function flush($buffer){
		$this->content = explode("#CONTENT#", $buffer);
		$this->content = $this->content[0].$this->body.$this->content[1];
		#INIT HEAD
		$this->content = str_replace('<head>', "<head>\n".$this->initHead(), $this->content);
		
		$this->run();
		$temp = array();
		foreach($this->arguments as $name => $value){
			$temp['<!--#'.$name.'#-->'] = $value;
		}
		$this->content = strtr($this->content, $temp);
		
		#INIT ALL TYPES OF INSIDE OBJECTS
		$this->content = $this->convertSimpleObjects($this->content);
		return $this->content;
	}
	
	function convertSimpleObjects($buffer){
		return preg_replace_callback("/<!--\s*object:(.*)\s*-->/sU", array($this, 'activateSimpleObject'), $buffer);
	}
	
	function activateSimpleObject($ok){
		if(@!preg_match("/^\[(\d+)\]\[([^\]]+)\]$/", $ok[1], $p) || !($o = $this->objects->getFullObject($p[1], false)) || empty($o[$p[2]])) return '';
		return $o[$p[2]];
	}
	
	function header($args=array()){
		$this->args($args);
		ob_start(array($this, 'flush'));
		include(_HTML_ABS_.$this->template);
		ob_start();
		return true;
	}
	
	function footer(){
		$this->body = ob_get_contents();	
		ob_end_clean();
	}

	#ЭТА НИФИГОВАЯ ФУНКЦИЯ ДЕЛАЕТ ИНКЛЮДЫ СТИЛЕЙ И ЖОВОСКРИПТА В САМЫЙ ВЕРХ
	function initHead(){
		$files = array(
			'<meta http-equiv="content-type" content="text/html; charset=utf-8" />',
			'<meta name="viewport" content="width=device-width, initial-scale=1.0">',
			'<title><!--object:[5][18]--> &mdash; <!--#page-title#--></title>',
			'<meta name="keywords" content="<!--object:[6][18]-->">',
			'<meta name="description" content="<!--object:[7][18]-->">',			
			'<script type="text/javascript" src="'._WWW_.'/jquery.js"></script>',			
			'<script type="text/javascript" src="'._WWW_.'/js.js"></script>',			
			'<script type="text/javascript" src="'._WWW_.'/js/fancybox/jquery.fancybox-1.3.0.pack.js"></script>',
			'<script type="text/javascript" src="'._WWW_.'/js/fancybox/jquery.easing-1.3.pack.js"></script>',
			'<script type="text/javascript" src="'._WWW_.'/js/fancybox/jquery.mousewheel-3.0.2.pack.js"></script>',
			'<link rel="stylesheet" href="'._WWW_.'/js/fancybox/jquery.fancybox-1.3.0.css" type="text/css" media="screen">',
			'<link rel="stylesheet" href="'._WWW_.'/js/bxslider/jquery.bxslider.css" type="text/css" media="screen">',
			'<script type="text/javascript" src="'._WWW_.'/js/bxslider/jquery.bxslider.min.js"></script>',
		);
		
		return join("\n", $files);
	}
	
	#USE IT FOR ACTIONS BY DEFAULT;
	function run(){
		$this->arg('top_menu', $this->topMenu());
		$this->arg('cat_menu', $this->catMenu());
		$this->arg('news-list', $this->newsList(9));
		$this->arg('banners-list-1', $this->bannersList(2));
	}
	
################
#USER FUNCTIONS#
################	

	function getRycle($mail = false){
		global $api;
		$result = array();
		$result[] = '
			<table class="table-basket">

			<tbody>

			<tr class="table-basket-head">

				<th width="50%"></th>

				<th width="20%">Количество</th>

				<th width="20%">Цена</th>

				<th width="10%"></th>

			</tr>
		';

		$total_sum = 0;
		$count = 0;

		foreach($_SESSION['rycle'] as $item => $keys){
			$result[] = '
				<tr class="table-basket-body">

					<td class="table-basket-body-unit">

						<a href="'.($mail?$_SERVER['HTTP_HOST']:'').'/catalog.php?cat='.$keys['head'].'&item='.$item.'">

							<span class="table-basket-img"><img src="'._UPLOADS_.'/'.$keys['Изображение'].'" alt="" style="width:100%;"></span>

							<span class="table-basket-name">'.$keys['Название'].'</span>

							<span class="table-basket-title">'.$keys['Брэнд'].'</span>

						</a>

					</td>

					<td class="table-basket-body-quantily">

						<div class="table-basket-quantily">

							<button class="plus" price="'.$keys['Цена'].'" data-id="'.$item.'">+</button>

							<input class="quantily" type="text" value="'.(isset($_SESSION['rycle'][$item]['Количество'])?$_SESSION['rycle'][$item]['Количество']:'1').'">

							<button class="minus" price="'.$keys['Цена'].'" data-id="'.$item.'">-</button>

						</div>

					</td>

					<td class="table-basket-body-price">

						<span class="table-basket-price">'.$keys['Цена'].' тг</span>

					</td>

					<td class="table-basket-body-close">

						<a href="#" num="'.$item.'" class="table-basket-del"></a>

					</td>

				</tr>
			';
			$total_sum += intval($keys['Цена']);
			$count++;
		}

		if(!isset($_SESSION['Сумма'])) $_SESSION['Сумма'] = $total_sum;

		$result[] = '
				<tr class="table-basket-total">

					<td colspan="2">

						<span class="table-basket-total-left"><span>'.$count.'</span> ед.</span>

					</td>

					<td colspan="3">

						<span class="table-basket-total-right" '.($mail?'style="margin-left: 100px;"':'').'>Итого: <span>'.$_SESSION['Сумма'].' тг</span></span>

					</td>

				</tr>

				</tbody>

			</table>
		';

		return join("\n",$result);
	}

	function getOrderForm(){
		return '
			<div class="rycle-block">

				<h1>Оформление заказа</h1>

				<form method="POST">

					<div class="form-row">

						<input type="text" class="half" placeholder="Имя" name="name" required="required">

						<input type="text" class="half right" placeholder="Фамилия" name="lastname" required="required">

						<input type="text" class="half input-phone" placeholder="+7 (___) ___-____" name="phone" required="required">

						<input type="email" class="half right" placeholder="E-mail" name="email" required="required">

					</div>

					<div class="form-row">

						<h4>Доставка</h4>

						<div class="rycle-block-check">

							<input id="one" type="radio" name="change" value="Курьером" checked="">

		                    <label for="one">Курьером</label>

		                    <input id="two" type="radio" name="change" value="Самовывоз">

		                    <label for="two">Самовывоз</label>

						</div>

						<input class="address" type="text" placeholder="Адрес" name="address" required="required">

					</div>

					<div class="form-row">

						<textarea name="comment" cols="30" rows="6" placeholder="Комментарий"></textarea>

					</div>

					<div class="form-row">

						<input type="submit" class="btn" value="Оформить заказ">

					</div>

				</form>

			</div>
		';
	}

	#ОТЗЫВЫ В ЭЛЕМЕНТЕ КАТАЛОГА

	public function getReviews($catalogItemId)
    {
        $items = $this->objects->getFullObjectsListByClass($catalogItemId, 13);

        if (!$items) {
            return false;
        }

        $out = [];

        foreach ($items as $item) {
            $out[] = "<div class='review'>
                        <div class='name'>{$item['Имя']}</div>
                        <div class='review-text'>{$item['Текст']}</div>
                    </div>
                    <hr class='dotted'>
                    ";
        }

        if (empty($out)) {
            return false;
        }

        return '<div class="reviews-wrapper">
                    <h3>Отзывы</h3>
                    <div class="reviews">'.implode("\n", $out).'</div>
                </div>';
    }

	# ВЕРХНЕЕ МЕНЮ
	function topMenu()
	{
		$out = array();		
		if($menus = $this->objects->getFullObjectsList(4))
		{
			foreach($menus as $o)
			{
				$submenus = $this->topSubMenu($o['id']); 
			
				if($this->objects->last == $o['id']) $out[]='<li class="setmenu"><div>'.$o['Название'].'</div>'.$submenus.'</li>';
				else{ 
					if($o['class_id'] == 2)
					{
						if($_SERVER['REQUEST_URI']=='/'.$this->lang.$o['Ссылка'] || $_SERVER['SCRIPT_NAME']==$o['Ссылка']) $out[]='<li class="setmenu"><div>'.$o['Название'].'</div>'.$submenus.'</li>';
						else
						{
							if(strstr($o['Ссылка'], '.php'))
							{
								$out[]='<li><div><a href="'.$o['Ссылка'].'?lang='.$this->lang.'"'.($o['В новом окне']?' target="_blank"':'').'>'.$o['Название'].'</a></div>'.$submenus.'</li>';
							}
							else $out[]='<li><div><a href="/'.$this->lang.$o['Ссылка'].'"'.($o['В новом окне']?' target="_blank"':'').'>'.$o['Название'].'</a></div>'.$submenus.'</li>';
						}
					}
					else $out[]='<li><div><a href="/'.$this->lang.'/pages/'.$o['id'].'.html"'.$class.'>'.$o['Название'].'</a></div>'.$submenus.'</li>';
				}
			}
		}

		return '<ul id="topmenu">'.join("\n", $out).'</ul>';
	}
	
	
	# ПОДМЕНЮ
	function topSubMenu($id) 
	{
		$out = array();		
		if($submenus = $this->objects->getFullObjectsList($id))
		{
			foreach($submenus as $o)
			{
				if($this->objects->last == $o['id']) $out[]='<div>'.$o['Название'].'</div>';
				else{ 
					if($o['class_id'] == 2)
					{
						if($_SERVER['REQUEST_URI']=='/'.$this->lang.$o['Ссылка'] || $_SERVER['SCRIPT_NAME']==$o['Ссылка']) $out[]='<div>'.$o['Название'].'</div>';
						else
						{
							if(strstr($o['Ссылка'], '.php'))
							{
								$out[]='<div><a href="'.$o['Ссылка'].'?lang='.$this->lang.'"'.($o['В новом окне']?' target="_blank"':'').'>'.$o['Название'].'</a></div>';
							}
							else $out[]='<div><a href="/'.$this->lang.$o['Ссылка'].'"'.($o['В новом окне']?' target="_blank"':'').'>'.$o['Название'].'</a></div>';
						}
					}
					else $out[]='<div><a href="/'.$this->lang.'/pages/'.$o['id'].'.html"'.$class.'>'.$o['Название'].'</a></div>';
				}
			}
			
			return '<div class="topsubmenu">'.join("\n", $out).'</div>';
		}

		return '';	
	}	
	
	# МЕНЮ КАТАЛОГА
	function catMenu()
	{
	    // class for directory $class_id = 5


		if (!$list = $this->objects->getFullObjectsListByClass(27, 0)) {
		    return false;
        }

		$out = [];


		foreach ($list as $cat) {

            $subCats = $this->objects->getFullObjectsListByClass($cat['id'], 0,  "AND o.active='1'");

            $setcat = false;

            if (!empty($subCats)) {

                $subcats = [];

                foreach ($subCats as $item) {
                    if (!empty($_GET['cat']) && ((int)$_GET['cat'] === (int)$item['id'])) {
                        $setcat = true;
                        $subcats[] = '<li>'.$item['name'].'</li>';
                    } else {
                        $subcats[] = '<li><a href="/catalog.php?cat='.$item['id'].'&brand=all" title="'.$item['name'].'">'.$item['name'].'</a></li>';
                    }
                }
            }

            if (!empty($subcats)) {
                $out[] = '
				<div class="cat_menu_element'.($setcat ? ' setcat' : '').'">
					<a class="catalog_lnk" href="#" title="'.$cat['name'].'">'.$cat['name'].'</a>
					<ul class="subcats '.($_GET['cat'] === $cat['id'] ? 'active' : '').'"'.($setcat ? ' style="display:block"' : '').'>'.join("\n", $subcats).'</ul>
				</div>';
            } else {
                $out[] = '
				<div class="cat_menu_element'.($setcat ? ' setcat' : '').'">
					<a class="catalog_lnk" href="#" title="'.$cat['name'].'">'.$cat['name'].'</a>
				</div>';
            }



        }
		
		return '<div id="cat_menu" class="open"><a href="#" class="cat_menu_btn">каталог</a> '.join("\n", $out).'</div>';
	}
	/*
	function catMenu()
	{	
		if (!$list = $this->objects->getFullObjectsListByClass(27, 0)) return '';
		$out = array();
		foreach($list as $o)
		{
			$out[] = '
			<div class="cat_menu_element'.(!empty($_GET['cat']) && ($_GET['cat'] == $o['id']) ? ' setcat' : '').'">
				<a href="/catalog.php?cat='.$o['id'].'" title="'.$o['name'].'">'.$o['name'].'</a>
			</div>';
		}
		
		return '<div id="cat_menu">'.join("\n", $out).'</div>';
	}
	
	# МЕНЮ КАТАЛОГА
	function catMenu()
	{	
		if (!$list = $this->objects->getFullObjectsListByClass(27, 0)) return '';
		$out = array();
		foreach($list as $o)
		{
			if ($subcats_list = $this->objects->getFullObjectsListByClass($o['id'], 0))
			{
				$subcats = array();
				$setcat  = false;
				foreach($subcats_list as $sc)
				{
					if (!empty($_GET['cat']) && ($_GET['cat'] == $sc['id']))
					{	
						$setcat = true;
						$subcats[] = '<li>'.$sc['name'].'</li>';
					}
					else $subcats[] = '<li><a href="/catalog.php?cat='.$sc['id'].'" title="'.$sc['name'].'">'.$sc['name'].'</a></li>';
				}
			
				$out[] = '
				<div class="cat_menu_element'.($setcat ? ' setcat' : '').'">
					<a class="catalog_lnk" href="#" title="'.$o['name'].'">'.$o['name'].'</a>
					<ul class="subcats"'.($setcat ? ' style="display:block"' : '').'>'.join("\n", $subcats).'</ul>
				</div>';
			}
		}
		
		return '<div id="cat_menu">'.join("\n", $out).'</div>';
	}
	*/
	
	
	function getSubMenus($id){
		if(!$list = $this->objects->getFullObjectsList($id)) return '';
		$out = array('<ul class="sub-menu" id="sub-menu-'.$id.'">');
		foreach($list as $o){
			if($this->objects->last == $o['id']) $out[]='<li class="active">'.$o['Название'].'</li>';
			else{ 
				if($o['class_id']==2){
					if($_SERVER['REQUEST_URI']=='/'.$this->lang.$o['Ссылка']) $out[]='<li class="active">'.$o['Название'].'</li>';
					else $out[]='<li><a href="/'.$this->lang.$o['Ссылка'].'"'.($o['В новом окне']?' target="_blank"':'').'>'.$o['Название'].'</a></li>';
				}else{
					$out[]='<li><a href="/'.$this->lang.'/pages/'.$o['id'].'.html">'.$o['Название'].'</a></li>';
				}
			}
			$out[]='<br>';
		}
		$out[]='</ul>';
		return join("\n", $out);
	}
	
	# НОВОСТНАЯ ЛЕНТА, ВЫВОД НА ГЛАВНОЙ ДВУХ НОВОСТЕЙ
	function newsList($id)
	{
		if(!$list = $this->objects->getFullObjectsListByClass($id, 8, "AND o.active=1 ORDER BY c.field_19 DESC LIMIT 2")) return 'Новостей нет.';
		$html = array('<h2 class="text">&nbsp;&nbsp;Новости</h2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="/'.$this->lang.'/news/">все новости</a>
		<br />
		<img src="/images/for_news.jpg" width="735" height="284">
		<hr style="margin-top:30px;">
		');
		foreach($list as $o)
		{
			$html[]='
				<div class="datenews">'.$this->strings->date($o['Дата'], 'sql', 'textdateday').'</div>
				<div class="shortnews">'.$o['Анонс'].'
				<br /><a href="/'.$this->lang.'/news/'.$o['id'].'/">подробнее</a></div>';
		}
		$smart = '
		<!--smart:{
			id : '.$id.',
			title : "новостей",
			actions : ["add"],
			p : {
				add : [8]
			}
		}-->';  
		return join("\n", $html).$smart;
	}
	
	# СПИСОК БАННЕРОВ	
	function bannersList($id)
	{	
		$smart_global = '
		<!--smart:{
			id : '.$id.',
			title : "списка&nbsp;баннеров",
			actions : ["add"],
			p : {
				add : [6, 7]
			},
			info : {
				"add" : "добавить&nbsp;баннер"
			},
			css : {
				marginTop:20
			}
		}-->';
		
		if(!$list = $this->objects->getFullObjectsList($id)) return $smart_global;
		$out = array();
		foreach($list as $o)
		{
			if($o['class_id'] == 6)
			{
				if($this->lower($this->getFileExtension($o['Баннер'])) == 'swf')
				{
					$html = '
					<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0"'.($o['width']?' width="'.$o['width'].'"':'').($o['height']?' height="'.$o['height'].'"':'').'>
						<param name="movie" value="'._UPLOADS_.'/'.$o['Баннер'].'">
						<param name="quality" value="high">
						<param name="wmode" value="transparent">
						<embed src="'._UPLOADS_.'/'.$o['Баннер'].'" quality="high" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash"'.($o['width']?' width="'.$o['width'].'"':'').($o['height']?' height="'.$o['height'].'"':'').'>
					</object>';
				} else $html = '<a href="'.$o['Ссылка'].'"'.($o['В новом окне']?' target="_blank"':'').'><img src="'._UPLOADS_.'/'.$o['Баннер'].'" border="0"'.($o['width']?' width="'.$o['width'].'"':'').($o['height']?' height="'.$o['height'].'"':'').'></a>';
			} else $html = htmlspecialchars_decode($o['Значение']);
			$smart = '
			<!--smart:{ 
				id:'.$o['id'].',
				title : "баннера",
				actions : ["edit", "remove"],
				p :{
					remove : "#banner-'.$o['id'].'"
				},
				info : {
					"remove" : "удалить&nbsp;баннер"
				}
			}-->';
			$out[]='<div id="banner-'.$o['id'].'" class="banners">'.$html.$smart.'</div>';
		}
		return join("\n", $out).$smart_global;
	}
}

$api = new api();
if(isset($_REQUEST['lang']) && array_key_exists($_REQUEST['lang'], $api->languages)) $api->lang = $_REQUEST['lang'];
?>