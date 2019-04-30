<?php 

include('cms/public/api.php');

$fields = array(
	'Имя' => $_REQUEST['name'],
	'Телефон' => $_REQUEST['phone']
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

$smail->Subject = 'Сообщение с формы обратной связи на сайте '.$_SERVER['HTTP_HOST'];

$smail->IsHTML(true);

$html = array();
foreach($fields as $item => $value){
    $html[] = '<b>'.$item.'</b>: '.$value.'<br />';
}
$smail->Body  = join("\n", $html);

$emailObj = $api->objects->getFullObject(2021);
$emails = explode(',', $emailObj['Значение']);

foreach($emails as $item){
    $smail->AddAddress($item);
}

$smail->Send();

echo '<p style="green">Сообщение успешно отправлено</p>';

?>