<?
include('cms/public/api.php');


# ЕСЛИ ПОСЛЕН ID ВИДЕО
if (!empty($_GET['video']) && is_numeric($vid = $_GET['video']) && ($video = $api->objects->getFullObject($vid)) && ($video['class_id'] == 14))
{
	echo $video['HTML код'];
	exit;
} 

$api->header(array('page-title'=>'Видеогалерея'));

# СПИСОК ГАЛЕРЕЙ
if ($gallerys = $api->objects->getObjectsList(70))
{
	# ТЕКУЩАЯ ГАЛЛЕРЕЯ
	$current_gal = 0;
	if (!empty($_GET['id']) && is_numeric($gal_id = $_GET['id']) && ($gallery = $api->objects->getFullObject($gal_id)) && ($gallery['head'] == 70)) {
		$current_gal = $gallery['id'];
	}

	$out = array();
	foreach($gallerys as $o)
	{
		if ($o['id'] != $current_gal)
		{
			$out[] = '<div class="gallery_cat"><a href="/'.$api->lang.'/vgallery/'.$o['id'].'/">'.$o['name'].'</a></div>';
		}	
	}
	echo join("\n", $out);
	
	# ГАЛЕРЕЯ ЗАДАНА
	if (!empty($current_gal) && !empty($gallery))
	{
		echo '
		<br/><h1>'.$gallery['name'].'</h1><br/>';

		# ВИДЕО ГАЛЛЕРЕИ
		if ($gallery_videos = $api->objects->getFullObjectsListByClass($gallery['id'], 14))
		{
			$n = 0;
			$out = array();
			foreach($gallery_videos as $o)
			{
				$n++;
				if ($n == 1) $out[] = '<tr valign="top">';
				
				$out[] = '
				<td>
					<div><a class="galleryvideo" title="'.htmlspecialchars($o['Название']).'" rel="group" href="/vgallery.php?video='.$o['id'].'" target="_blank"><img src="'._IMG_.'/?url='._UPLOADS_.'/'.$o['Предпросмотр'].'&w=216"></a></div>
					<div>'.$o['Название'].'</div>
				</td>';
				
				if ($n == 3) { $out[] = '</tr>'; $n = 0; } 
			
			}

			echo '<table class="gallery">'.join("\n", $out).'</table>';
		
		} else echo '<div style="padding:70px; text-align:center;">Видеозаписей в альбоме нет!</div>';		
	}

} else echo '<div style="padding:70px; text-align:center;">Видеозаписей нет!</div>';

$api->footer();
?>