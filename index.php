<?
include('cms/public/api.php');
$api->header(array('page-title'=>'Главная'));
?>
<!--<a href="/news.php"><div style="background:transparent;position:absolute; top:365px;left:799px;width:100px;height:100px;z-index:100;"></div></a>
<a href="/action.php"><div style="background:transparent;position:absolute; top:550px;left:337px;width:100px;height:100px;z-index:100;"></div></a>-->
<ul class="bxslider">
  <?php 
       $list = $api->objects->getFullObjectsListByClass(2136,4);
       foreach($list as $item){
           echo '
           <li>
        			<a href="'.$item['Название'].'">
        				<img src="'._UPLOADS_.'/'.$item['Ссылка'].'">
        				<span class="title">'.$item['Текст'].'</span>
        			</a>
           </li>';
       }
  ?>
</ul>
<!--object:[12][2]-->
<?
$api->footer();
?>