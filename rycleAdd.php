<?php 
	include('cms/public/api.php');

	$obj = $api->objects->getFullObject($_REQUEST['id']);

	$fields = array('Название','Брэнд','Изображение','Цена','head');

	$info = array();

	foreach($obj as $item => $key){
		if(in_array($item, $fields)){
			$info[$item] = $key;
		}
	}

	$_SESSION['rycle'][$obj['id']] = $info;

    $total_summ = 0;

    foreach ($_SESSION['rycle'] as $item => $keys) {
        $total_summ += intval($keys['Цена']);
    }

    $_SESSION['Сумма'] = $total_summ;
?>