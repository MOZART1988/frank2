<?
include('cms/public/api.php');
$api->header(array('page-title'=>'Каталог продукции'));

$brands = explode("\n", $api->db->select("`fields`", "WHERE id='32' LIMIT 1", 'p3'));

?>
    <table width="100%" cellpadding="0" cellspacing="0">
 <tr valign="top">
	<td width="155">
		<hr class="dotted" />
		<!--#cat_menu#-->
	</td>

	<td style="padding-left:15px;">
        <div class="forms" style="margin-top: 10px">
            <form id="searchincat_form" style="width: 30%; float: left;" action="<?=$_SERVER['PHP_SELF']?>" method="POST">
                <div id="searchincat_input">
                    <input type="text" name="code" value="<?=(!empty($_POST['code']) ? $api->db->prepare($_POST['code']) : 'Поиск по товарам')?>" title="Поиск по товарам" class="needClear" />
                    <input type="submit" value="" />
                </div>
            </form>
            <form style="width: 30%; float: right;" id="brand_filter_form" action="<?=$_SERVER['PHP_SELF']?>" method="GET">
                <?=((!empty($_GET['cat']) && is_numeric($_GET['cat'])) ? '<input type="hidden" name="cat" value="'.intval($_GET['cat']).'" />':'')?>
                <select id="brand_select" name="brand" onchange="$('#brand_filter_form').submit()">
                    <option value="all">Все брэнды</option>
                    <?
                    # ФИЛЬТРАЦИЯ ПО БРЭНДАМ

                    if (!empty($_GET['cat'])) {
                        $brands = [];
                        $products = $api->objects->getFullObjectsListByClass($_GET['cat'], 12);
                        foreach ($products as $product) {
                            $brands[] = $product['Брэнд'];
                        }

                        $brands = array_unique($brands);
                    }



                    if (!empty($brands))
                    {
                        $out = array();
                        foreach($brands as $v)
                        {
                            $selected = false;

                            # Сравнение брэнда
                            if (!empty($_GET['brand']) && ($_GET['brand'] == $v))
                            {
                                $selected = true;
                                $brand = $v;
                            }

                            $out[] = '<option value="'.$v.'"'.($selected ? ' selected':'').'>'.$v.'</option>';
                        }
                        echo join("\n", $out);
                    }
                    ?>
                </select>
            </form>
        </div>
	<?
		# СООБЩЕНИЕ ОБ ОШИБКЕ
		$error_msg = '<div style="padding:50px; text-align:center; font-style:italic;">По вашему запросу ничего не найдено</div>';

		# ЭЛЕМЕНТ
		if (!empty($_GET['item']) && is_numeric($item_id = $_GET['item']) && ($item = $api->objects->getFullObject($item_id)) && ($item['class_id'] == 12))
		{
			echo '
			<div class="full_item">
				<div class="cat_full_img">
					<a class="galleryphoto" href="'._UPLOADS_.'/'.$item['Изображение'].'" target="_blank"><img src="'._IMG_.'?url='._UPLOADS_.'/'.$item['Изображение'].'&w=265&type=square" /></a>
				</div>
				<div class="cat_full_main">
					<div><span class="cat_full_code">'.$item['Номер'].'</div>
					<div class="cat_full_name">'.$item['Название'].'</div>
					<div class="cat_full_brand">'.$item['Брэнд'].'</div>
					<div class="cat_mini_desc">'.$item['Мини описание'].'</div>
					<div class="page_coast">Цена:&nbsp;'.$item['Цена'].'&nbsp;тг</div>
				</div>
				<div class="clear"></div>
				<div class="cat_full_text">
					'.$item['Описание'].'
				</div>
			</div>
			<div align="right">
				'.(isset($_SESSION['rycle']) && array_key_exists($item['id'],$_SESSION['rycle'])?'<a href="/ru/rycle/" class="order_btn">Перейти в корзину</a>':'<input type="button" class="order_btn itemBuy" data-id="'.$item['id'].'" '.($item['Цена'] == 0?'style="display:none;"':'').' value="Купить">').'
			</div>';

		}
		# КАТАЛОГ
		else if (
			!empty($_GET['cat']) && is_numeric($cat_id = $_GET['cat']) && ($cat = $api->objects->getFullObject($cat_id)) &&
			!empty($_GET['brand']) && ($brand = addslashes($_GET['brand']))
		)
		{

		    $brand = $_GET['brand'];


			echo '<h1 style="margin-top:50px;">'.$cat['name'].'</h1>';

			# Количество товаров
			$elements_count = $api->objects->getObjectsCount($cat['id'], 12, "AND o.active='1'".($brand != 'all' ? " AND c.field_32='$brand'" : ''));

			if ($elements_count > 0)
			{
				# Страницы
				$pages = $api->pages($elements_count, 9, 5, array(), $_SERVER['PHP_SELF'].'?cat='.$cat['id'].'&brand='.$brand.'&pg=#pg#');

				# Получаем страницу товаров
				if($cat_elements = $api->objects->getFullObjectsListByClass($cat['id'], 12, "AND o.active='1'".($brand != 'all' ? " AND c.field_32='$brand'" : '')." ORDER BY c.field_46 ASC LIMIT ".$pages['start'].", 9"))
				{
					$out = array();
					foreach($cat_elements as $o)
					{
						$out[] = '
						<li>
							<div class="cat_element_tb"></div>
							<div class="cat_element_c">
								<div class="cat_element_in">
									<div>'.$o['Брэнд'].'</div>
								</div>
								<div class="cat_element_img">
									<a href="'.$_SERVER['PHP_SELF'].'?cat='.$o['head'].'&item='.$o['id'].'"><img src="'._IMG_.'?url='._UPLOADS_.'/'.$o['Изображение'].'&h=180&type=square" /></a>
									'.(!empty($o['Номер'])? '<div class="cat_element_code">'.$o['Номер'].'</div>' : '').'
								</div>
								<div class="cat_element_in">
									<div class="cat_element_name">'.$o['Название'].'</div>
									<div class="cat_element_coast">Цена ............ '.number_format($o['Цена'], 0, '.', ' ').' тг</div>
								</div>
								<div class="cat_element_btn">
									'.(isset($_SESSION['rycle']) && array_key_exists($o['id'],$_SESSION['rycle'])?'<a class="cat_element_btn_buy" href="/ru/rycle/">Перейти в корзину</a>':'<a class="cat_element_btn_buy itemBuy" href="#" data-id="'.$o['id'].'" '.($o['Цена'] == 0?'style="display:none;"':'').'>Купить</a>').'
								</div>
							</div>
							<div class="cat_element_tb"></div>
						</li>';
					}
					echo '
					<ul id="cat_elements">'.join("\n", $out).'</ul>
					<div class="clear"></div>
					<div class="cat_pages">
						'.$pages['html'].'
					</div>';
				}
				else echo $error_msg;
			}
			else echo $error_msg;
		}

		# ПОИСК ПО НОМЕРУ
		else if (!empty($_POST['code']))
		{
			echo '<h1>Результаты поиска</h1>';

			$code = $api->db->prepare($_POST['code']);
            $search_elements = $api->objects->getFullObjectsListByClass(
                    -1,
                    12,
                    "AND o.active='1' 
                    AND (c.field_31 LIKE '$code' 
                    OR c.field_33 LIKE '%$code%'
                    OR c.field_32 LIKE '%$code%') 
                    ORDER BY o.sort DESC");

			# Ищем по номеру
			if(!empty($search_elements))
			{
				$out = array();
				$mothers = array();
				foreach($search_elements as $o)
				{
					if (!isset($mothers[$o['head']]))
					{
						$mother = $api->objects->getObject($o['head']);
						$mothers[$mother['id']] = array('id'=>$mother['id'], 'name'=>$mother['name']);
					}

					$out[] = '
					<li>
						<div class="cat_element_tb"></div>
						<div class="cat_element_c">
							<div class="cat_element_in">
								<div>'.$o['Брэнд'].'</div>
								<div><a href="/catalog.php?cat='.$mothers[$o['head']]['id'].'">'.$mothers[$o['head']]['name'].'</a></div>
							</div>
							<div class="cat_element_img">
								<a href="'.$_SERVER['PHP_SELF'].'?cat='.$o['head'].'&item='.$o['id'].'"><img src="'._IMG_.'?url='._UPLOADS_.'/'.$o['Изображение'].'&h=180&type=square" /></a>
								'.(!empty($o['Номер'])? '<div class="cat_element_code">'.$o['Номер'].'</div>' : '').'
							</div>
							<div class="cat_element_in">
								<div class="cat_element_name">'.$o['Название'].'</div>
								<div class="cat_element_coast">Цена ............ '.number_format($o['Цена'], 0, '.', ' ').' тг</div>
							</div>
						</div>
						<div class="cat_element_tb"></div>
					</li>';
				}

				echo '
				<ul id="cat_elements">'.join("\n", $out).'</ul>
				<div class="clear"></div>';
			}
			else echo $error_msg;
		}

		# НОВИНКИ
		else
		{
			echo '<h1 styke="margin-top:50px;">Новинки</h1>';
			if ($new_elements = $api->objects->getFullObjectsListByClass(-1, 12, "AND o.active='1' AND c.field_36='1' ORDER BY o.sort DESC"))
			{
				$out = array();
				$mothers = array();
				foreach($new_elements as $o)
				{
					if (!isset($mothers[$o['head']]))
					{
						$mother = $api->objects->getObject($o['head']);
						$mothers[$mother['id']] = array('id'=>$mother['id'], 'name'=>$mother['name']);
					}
					//'.$mothers[$o['head']]['id'].'
					//'.$mothers[$o['head']]['name'].'
					$out[] = '
					<li>
						<div class="cat_element_tb"></div>
						<div class="cat_element_c">
							<div class="cat_element_in">
								<div>'.$o['Брэнд'].'</div>
								<div><a href="/catalog.php?cat="></a></div>
							</div>
							<div class="cat_element_img">
								<a href="'.$_SERVER['PHP_SELF'].'?cat='.$o['head'].'&item='.$o['id'].'"><img src="'._IMG_.'?url='._UPLOADS_.'/'.$o['Изображение'].'&h=180&type=square" /></a>
								'.(!empty($o['Номер'])? '<div class="cat_element_code">'.$o['Номер'].'</div>' : '').'
							</div>
							<div class="cat_element_in">
								<div class="cat_element_name">'.$o['Название'].'</div>

							</div>
						</div>
						<div class="cat_element_tb"></div>
					</li>';
				}
				echo '
				<ul id="cat_elements">'.join("\n", $out).'</ul>
				<div class="clear"></div>';
			}
		}
	?>
	</td>
 </tr>
</table>
<?
$api->footer();
?>