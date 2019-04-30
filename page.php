<?
include('cms/public/api.php');
if(!isset($_REQUEST['id']) || !is_numeric($id=$_REQUEST['id']) || !($obj = $api->objects->getFullObject($id)) || (($obj['class_id'] != 1) && ($obj['class_id'] != 3))){ 
	exit( header('location: /404.php') );
}

$vars = array(
	"ru"=>array(
		"attachedPages"=>'Прикреплённые страницы',
		"attachedFiles"=>'Прикреплённые файлы',
		"attachedPhotos"=>'Прикреплённые фото'
	),
	"en"=>array(
		"attachedPages"=>'Attached pages',
		"attachedFiles"=>'Attached files',
		"attachedPhotos"=>'Attached pictures'
	),
	"kz"=>array()
);

$api->header(array('page-title'=>$obj['Название']));

# ВЛОЖЕННОСТЬ
$mothers = array();
function getMothers($id)
{
	global $api;
	global $mothers;
	
	if (is_numeric($id) && ($id != 0) && ($o = $api->objects->getObject($id, false)) && (($o['class_id'] == 1) || ($o['class_id'] == 3)))
	{
		$mothers[] = $o['id'];
		getMothers($o['head']);
	}
}
getMothers($obj['head']);
$mothers = array_reverse($mothers);

# ХЛЕБНЫЕ КРОШКИ
if (sizeof($mothers) > 0)
{
	$out = array();
	foreach($mothers as $obj_id)
	{
		if (is_numeric($obj_id) && ($path_obj = $api->objects->getFullObject($obj_id, false))) $out[] = '<a href="/'.$api->lang.'/pages/'.$path_obj['id'].'.html">'.$path_obj['Название'].'</a>';	
	}	
	//echo '<div style="margin:10px 0; font-weight:bold;">'.join(' / ', $out).'</div>';
}

echo '
<div id="page-text">
'.($obj['id'] == 18 ? '
<div id="slideshow">
	<p class="active"><img src="/images/contacts1.jpg" /></p>
	<p><img src="/images/contacts2.jpg" /></p>
</div>':'')
.$obj['Текст'].'
</div>';
?>
<!--smart:{
	id:<?=$obj['id']?>,
	title:'&laquo;<?=$obj['Название']?>&raquo;',
	actions:['edit', 'add'],
	p:{
		add:[3,4,5],
		edit:{
			fields:{
				<?=(@$obj['class_id']==1?1:6)?>:'#page-title',
				<?=(@$obj['class_id']==1?2:7)?>:'#page-text'
			}
		}
	},
	info : {
		add : 'прикрепить&nbsp;данные'
	}
}-->
<?

echo '<div>';

# ВЛОЖЕННЫЕ СТРАНИЦЫ
if($api->auth())
{
	if($pages = $api->objects->getFullObjectsListByClass($obj['id'], 3))
	{
		//echo '<br><h2>'.$vars[$api->lang]['attachedPages'].'</h2>';
		$out = array();
		foreach($pages as $page)
		{
			$out[] = '
			<li id="li-'.$page['id'].'">
				<a href="'._WWW_.'/'.$api->lang.'/pages/'.$page['id'].'.html" target="_blank">'.$page['Название'].'</a>
				<div>
				<!--smart:{
					id:'.$page['id'].',
					actions:["edit", "remove"],
					p:{
						remove : "#li-'.$page['id'].'"
					}
				}-->
				</div>			
			</li>';
		}
		echo '<ul style="list-style-type:decimal;">'.join("\n", $out).'</ul>';
	}
}

# ВЛОЖЕННЫЕ ФАЙЛЫ
$lang = $api->lang;
$api->lang='ru';
if($files = $api->objects->getFullObjectsListByClass($obj['id'], 5))
{
	//echo '<br><h2>'.$vars[$lang]['attachedFiles'].'</h2>';
	$out = array();
	foreach($files as $file)
	{
		$ico = _WWW_.'/ext/file.gif';
		$ext = $api->lower($api->getFileExtension($file['Ссылка']));
		if (file_exists(_WWW_ABS_.'/ext/'.$ext.'.gif')) $ico = _WWW_.'/ext/'.$ext.'.gif';
		$out[] = '
		<li id="li-'.$file['id'].'">
			'.($file['Ссылка']?'<img src="'.$ico.'" border="0" style="margin-bottom:-5px;">&nbsp;<a href="'._UPLOADS_.'/'.$file['Ссылка'].'" target="_blank">'.$file['Название'].'.'.$ext.'</a>':$file['Название']).'		
			<div>
			<!--smart:{
				id:'.$file['id'].',
				actions:["edit", "remove"],
				p:{
					remove : "#li-'.$file['id'].'"
				},
				css : {marginLeft:32}
			}-->
			</div>
		</li>';
	}
	echo '<ul style="list-style-type:decimal;">'.join("\n", $out).'</ul>';
}


# ФОТОГАЛЛЕРЕЯ
$onepage = 12;
$pages = $api->pages($api->objects->getObjectsCount($obj['id'], 4), $onepage, 5, array(), "/".$api->lang."/pages/".$obj['id']."_#pg#.html#photos-list");
if($photos = $api->objects->getFullObjectsListByClass($obj['id'], 4, "AND o.active='1' ORDER BY o.sort LIMIT ".$pages['start'].", $onepage"))
{
	//echo '<br><h2>'.$vars[$lang]['attachedPhotos'].'</h2>';
	$n=0;
	$out = array();
	foreach($photos as $photo){
		$n++;
		if ($n == 1) { $out[] = '<tr valign="top">'; }
		$out[] = '
		<td id="photo-'.$photo['id'].'" align="center">
			<a class="photo" href="'._UPLOADS_.'/'.$photo['Ссылка'].'" rel="photo_group_'.$obj['id'].'" title="'.$photo['Название'].'"><img style="padding:5px;border:1px solid #c0c0c0; background-color:#fff;" src="'._IMG_.'?w=200&url='._UPLOADS_.'/'.$photo['Ссылка'].'"></a>
			<div>
			<!--smart:{
				id:'.$photo['id'].',
				actions:["edit", "remove"],
				p:{
					remove : "#photo-'.$photo['id'].'"
				}
			}-->
			</div>
		</td>';
		if ($n == 3) { $out[] = '</tr>'; $n = 0; }
	}
	if ($out[sizeof($out)-1] != '</tr>') $out[] = '</tr>'; 
	echo '<table id="photos-list" width="100%" cellpadding="7" cellspacing="0">'.join("\n", $out).'</table>';
}

$api->lang = $lang;

echo '
	<div style="margin:10px 0;">
		'.$pages['html'].'
	</div>
</div>
<script type="text/javascript">
$(function(){
	$("a.photo").fancybox({
		"speedIn"			: 600, 
		"speedOut"			: 200, 
		"overlayShow"		: true,
		"centerOnScroll"	: true,
		"titlePosition"		: "over"
	});
});
</script>';

# НАЗАД
if (sizeof($mothers) > 0) echo '<br/>&larr; <a href="/'.$api->lang.'/pages/'.$mothers[sizeof($mothers)-1].'.html">Назад</a>';

# ВЛОЖЕНОСТЬ
#if (sizeof($mothers) > 0) $api->objects->last = $mothers[0];

$api->footer();
?>