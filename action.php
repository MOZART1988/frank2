<?
# Edited by ShadoW
# 9.09.2010
include('cms/public/api.php');

$object_id 	= 690;			# ID объекта в котором лежат новости
$class_id	= 8;			# ID класса новостей
$onepage	= 15;			# На страницу
$vars = array(
	"ru"=>array(
		"news"=>'Акции',
		"back"=>'Вернуться',
		"noNews"=>'Акций нет.'
	),
	"en"=>array(
		"news"=>'Actions',
		"back"=>'Back to actions list',
		"noNews"=>'There is no any actions yet.'
	)
);
# ЗАГРУЖЕНА НОВОСТЬ
if(isset($_REQUEST['id']) && is_numeric($id=$_REQUEST['id']) && ($o = $api->objects->getFullObject($id)) && ($o['class_id']==$class_id))
{
	$api->header(array('page-title'=>htmlspecialchars($o['Название'])));
	echo '
	<div class="news" id="news-'.$o['id'].'">
		<div style="margin:10px 0;">'.$api->strings->date($o['Дата']).'</div>
		<!---'.(!empty($o['Изображение']) ? '<div class="actimg"><img src="'._UPLOADS_.'/'.$o['Изображение'].'" /></div>':'').'-->
		<div>'.$o['Текст'].'</div>
	</div>
	<!--smart:{
		id : '.$id.',
		actions : ["edit"],
		p:{
			remove:"#news-'.$o['id'].'"
		}
	}-->
	<br />
	<div>&larr; <a href="/'.$api->lang.'/action/">'.$vars[$api->lang]['back'].'</a></div>';
}
# -----------------------------------------------------------------------
# ЗАГРУЖЕН СПИСОК НОВОСТЕЙ
else 
{
	$api->header(array('page-title'=>$vars[$api->lang]['news']));
	
	# страницы
	$pages = $api->pages($api->objects->getObjectsCount($object_id, $class_id, "AND o.active='1'"), $onepage, 5, array("lang"=>$api->lang));
	
	# получаем страницу
	if($news = $api->objects->getFullObjectsListByClass($object_id, $class_id, "AND o.active='1' ORDER BY c.field_19 DESC LIMIT ".$pages['start'].", $onepage"))
	{
		$html = array();
		#$html[]='<img src="/images/for_news.jpg" width="735" height="284">';
		foreach($news as $n)
		{
			if($n['Название'])
			{
				$html[]='
				<br />
				<hr style="margin-top:30px;">
				<div class="news" id="news-'.$n['id'].'">
					'.(!empty($n['Изображение']) ? '<div class="actimg"><a href="/'.$api->lang.'/action/'.$n['id'].'/"><img src="'._UPLOADS_.'/'.$n['Изображение'].'" /></a></div>':'').'
					<div class="date">'.$api->strings->date($n['Дата']).'</div>
					<div class="name"><a href="/'.$api->lang.'/action/'.$n['id'].'/">'.$n['Анонс'].'</a></div>
				</div>';
				
			} else $html[]='<div class="news"><font color="red">Языковая версия не заполнена.</font></div>';
			
			$html[]= '
				<!--smart:{
					id : '.$n['id'].',
					actions : ["edit", "remove"],
					p:{
						remove:"#news-'.$n['id'].'"
					}
				}-->
				<br>';
		}
		
		# страницы
		$html[]='<div style="margin-top:20px;">'.$pages['html'].'</div>';
					
		echo join("\n", $html);
	}
	
	# новостей нет
	else echo $vars[$api->lang]['noNews'];
	
	echo '
	<!--smart:{
		id : '.$object_id.',
		title : "новостей",
		actions : ["add"],
		p:{
			add:['.$class_id.']
		},
		info:{
			add : "добавить&nbsp;новость"
		}
	}-->';
}

$api->footer();
?>