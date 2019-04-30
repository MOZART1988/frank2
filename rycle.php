<?

include('cms/public/api.php');

$api->header(array('page-title'=>'Корзина'));

// unset($_SESSION['rycle']);
// unset($_SESSION['Сумма']);

if(isset($_POST['name'])){

	$fields = array(
		'Имя' => $_POST['name'],
		'Фамилия' => $_POST['lastname'],
		'Телефон' => $_POST['phone'],
		'Email' => $_POST['email'],
		'Тип доставки' => $_POST['change'],
		'Адрес' => @$_POST['address'],
		'Комментарий' => @$_POST['comment']
	);

	include_once(_FILES_ABS_.'/PHPMailer/PHPMailerAutoload.php');
	$smail = new PHPMailer;
	$smail->CharSet = "UTF-8";
	$smail->IsSMTP();

	$smail->Host = 'smtp.gmail.com';
	$smail->Port = 587;
	$smail->SMTPAuth = true;

	$smail->Username = 'franckprovost.sender@gmail.com';
	$smail->Password = 'YILwcGyXtUGq';

	$smail->SMTPSecure = 'tls'; 

	$smail->From = 'franckprovost.sender@gmail.com';
	$smail->FromName = 'noreply';

	$smail->Subject = 'Новый заказ на сайте '.$_SERVER['HTTP_HOST'];

	$smail->IsHTML(true);

	$html = array();
	foreach($fields as $item => $value){
	    $html[] = '<b>'.$item.'</b>: '.$value.'<br />';
	}
	$html[] = $api->getRycle(true);
	$smail->Body  = join("\n", $html);

	$emailObj = $api->objects->getFullObject(2021);
	$emails = explode(',', $emailObj['Значение']);

	foreach($emails as $item){
	    $smail->AddAddress($item);
	}

	$smail->AddAddress($_POST['email']);

	$smail->Send();

	unset($_SESSION['rycle']);
	unset($_SESSION['Сумма']);

	echo '
		<div class="modal">

			<div class="modal-block">

				<h2>Ваш заказ успешно оформлен!</h2>

				<a href="#" class="close-btn"></a>

			</div>

		</div>
	';

}

if(isset($_SESSION['rycle'])){
	echo $api->getRycle();
	echo $api->getOrderForm();
}else{
	echo 'В корзине нет товара.';
}

$api->footer();

?>