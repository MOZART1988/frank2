<?php 
	include('cms/public/api.php');
	if(isset($_REQUEST['id'])){
		unset($_SESSION['rycle'][$_REQUEST['id']]);
		if(count($_SESSION['rycle']) == 0){
			unset($_SESSION['rycle']);
			unset($_SESSION['Сумма']);
		}
	}
?>