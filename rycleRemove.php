<?php 
	include('cms/public/api.php');
	if(isset($_REQUEST['id'])){
		unset($_SESSION['rycle'][$_REQUEST['id']]);
		if(count($_SESSION['rycle']) == 0){
			unset($_SESSION['rycle']);
			unset($_SESSION['Сумма']);
		}
	}

    $total_summ = 0;

    foreach ($_SESSION['rycle'] as $item => $keys) {
        $total_summ += intval($keys['Цена']);
    }

    $_SESSION['Сумма'] = $total_summ;
?>