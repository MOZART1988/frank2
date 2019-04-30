<?
include('cms/public/api.php');
$api->header(array('page-title'=>'Фотогалерея'));

if ($gallerys = $api->objects->getObjectsList(22))
{
	# ТЕКУЩАЯ ГАЛЛЕРЕЯ
	$current_gal = 0;
	if (!empty($_GET['id']) && is_numeric($gal_id = $_GET['id']) && ($gallery = $api->objects->getFullObject($gal_id)) && ($gallery['head'] == 22)) {
		$current_gal = $gallery['id'];
	}

	$out = array();
	foreach($gallerys as $o)
	{
		if ($o['id'] != $current_gal)
		{
			$out[] = '<div class="gallery_cat"><a href="/'.$api->lang.'/gallery/'.$o['id'].'/">'.$o['name'].'</a></div>';
		}	
	}
	echo join("\n", $out);
	
	# ГАЛЕРЕЯ ЗАДАНА
	if (!empty($current_gal) && !empty($gallery))
	{
		echo '
		<br/><h1>'.$gallery['name'].'</h1><br/>';

		# ФОТКИ ГАЛЛЕРЕИ
		if ($gallery_photos = $api->objects->getFullObjectsListByClass($gallery['id'], 4))
		{
			$n = 0;
			$out = array();
			foreach($gallery_photos as $o)
			{
				$n++;
				if ($n == 1) $out[] = '<tr valign="top">';
				
				$out[] = '
				<td>
					<div><a class="galleryphoto cropphoto" title="'.htmlspecialchars($o['Название']).'" rel="group" href="'._UPLOADS_.'/'.$o['Ссылка'].'" target="_blank"><img src="'._IMG_.'/?url='._UPLOADS_.'/'.$o['Ссылка'].'&w=216"></a></div>
					<div>'.$o['Название'].'</div>
				</td>';
				
				if ($n == 3) { $out[] = '</tr>'; $n = 0; } 
			
			}

			echo '<table class="gallery">'.join("\n", $out).'</table>';
		
		} else echo '<div style="padding:70px; text-align:center;">Фотогалерей в альбоме нет!</div>';		
	}

} else echo '<div style="padding:70px; text-align:center;">Фотогалерей нет!</div>';

$api->footer();
?>