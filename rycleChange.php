<?php
include('cms/public/api.php');

if(isset($_SESSION['rycle'])){
	$action = $_REQUEST['action'];

	if(!isset($_SESSION['rycle'][$_REQUEST['id']]['Количество'])) 
		$_SESSION['rycle'][$_REQUEST['id']]['Количество'] = 1;
	$count = $_SESSION['rycle'][$_REQUEST['id']]['Количество'];
	if($action == 'plus')
		$count += 1;
	elseif($action == 'minus')
		$count -=1;
	
	$_SESSION['rycle'][$_REQUEST['id']]['Количество'] = $count;
	$_SESSION['Сумма'] = intval($_REQUEST['sum']);
}
?>