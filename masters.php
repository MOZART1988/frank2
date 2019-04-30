<?
include('cms/public/api.php');
$api->header(array('page-title'=>'Мастера'));

# ПОЛУЧАЕМ РАЗДЕЛЫ МАТЕРОВ
if ($masters_cats = $api->objects->getFullObjectsList(20))
{
	foreach($masters_cats as $c)
	{
		echo '
		<div id="masster_div">
			<div class="gallery_cat"><a href="#">'.$c['name'].'</a></div>
			<div class="masters_container" style="display:none;">';
	
			# МАСТЕРА
			if ($masters = $api->objects->getFullObjectsListByClass($c['id'], 10))
			{
				$out = array();
				foreach($masters as $o)
				{
					$out[] = '
					<div class="master">			
						'.(!empty($o['Фото']) ? '<div class="masterphoto"><a class="masterphoto" title="'.htmlspecialchars($o['Имя']).'" href="'._UPLOADS_.'/'.$o['Фото'].'" target="_blank"><img src="'._IMG_.'?url='._UPLOADS_.'/'.$o['Фото'].'&w=216" title="'.htmlspecialchars($o['Имя']).'"/></a></div>':'').'
						<div class="masterinfo">
							<div class="mastertype">'.$o['Должность'].'</div>
							<div class="mastername"><h2>'.$o['Имя'].'</h2></div>
							<div class="mastertext">'.$o['О мастере'].'</div>
						</div>
						<div class="clear"></div>
					</div>';
				}
				
				echo join('<hr class="dotted" />', $out);
			
			}
			else echo '<div style="padding:70px; text-align:center;">Информации о мастерах нет!</div>';
		
		echo '
			</div>
		</div>';
		
	}
	
	echo '
	<script type="text/javascript">
	 $(function()
	 {
		$(".gallery_cat a").click(function() {
			
			if ($(this).parent().parent().find(".masters_container").css("display") == "none") {
			
				$(".masters_container").hide();
				$(this).parent().parent().find(".masters_container").slideDown("fast");
			
			} else {
			
				$(".masters_container").slideUp("fast");
				
			}	

			return false;
		}); 
	 });	
	</script>';

}
else echo '<div style="padding:70px; text-align:center;">Разделов нет!</div>'; 

$api->footer();
?>