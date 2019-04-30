<?
include('cms/public/api.php');

# AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') && !empty($_POST['email']))
{
	$answer = array('status'=>'error', 'msg'=>'Не верно указан e-mail');
	
	if (preg_match('/^\w+([\.-]?\w+)*@(((([a-z0-9]{2,})|([a-z0-9][-][a-z0-9]+))[\.][a-z0-9])|([a-z0-9]+[-]?))+[a-z0-9]+\.([a-z]{2}|(com|net|org|edu|int|mil|gov|arpa|biz|aero|name|coop|info|pro|museum))$/i', $_POST['email']))
	{
		if (!$api->objects->getObjectsListByClass(11, 0, "AND o.name='".$_POST['email']."' LIMIT 1"))
		{
			if ($object_id = $api->objects->createObject(array('name'=>$_POST['email'], 'head'=>11, 'class_id'=>0)))
			{
				$answer = array('status'=>'ok', 'msg'=>'Вы успешно подписались на рассылку');		
			}
			else $answer['msg'] = 'Не возможно добавить подписку';
			
		}
		else $answer['msg'] = 'Вы уже подписаны';
	}
	
	exit($api->JSON($answer));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Подписаться на рассылку</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script type="text/javascript" src="/js/jquery.js"></script>
	<script type="text/javascript" src="/js.js"></script>
	<script type="text/javascript" src="/js/ui/ui.js"></script>
	<link href="/js/ui/ui.css" rel="stylesheet" type="text/css" />

</head>
<body>
<div style="padding:0 20px; overflow-x: hidden;	overflow-y: auto; background-color:#383838;">
	<h1>Подписаться на рассылку</h1>
	<div style="margin-top:7px;"><input type="text" id="s_email" title="Ваш e-mail" value="Ваш e-mail" style="width:300px; border:1px solid #c0c0c0; padding:3px;" /></div>
	<div style="margin:5px 0; height:16px;" id="subscribe_infobox"></div>
	<div style="margin-bottom:15px;"><input id="s_send_btn" style="width:150px;" type="button" value="Подписаться" /></div>
</div>
<script type="text/javascript">
$(document).ready(function()
{
	$('#s_email').focus(function(){
			  if(this.value==this.title) this.value='';
		 })
		 .blur(function(){
			  if(this.value=='') this.value=this.title;
	});
	
	$("#s_send_btn").click(function()
	{
		var err_msg = '';
		
		if (($('#s_email').val() == '')  || ($('#s_email').val() == 'Ваш e-mail') || (CheckEmail($('#s_email').val()) == false)) { err_msg = 'Укажите правельный E-Mail'; $('#s_email').focus(); }

		if (err_msg == '')
		{
			$('#subscribe_infobox').text('Идет подписка...');
			$.post('<?=$_SERVER['PHP_SELF']?>', { 'email' : $('#s_email').val() }, function(json) 
			{
				if (json.status == 'ok') {
					
					$('#subscribe_infobox').html('<span style="color:green;">'+json.msg+'</span>');
				} 
				else $('#subscribe_infobox').html('<span style="color:red;">Ошибка: '+json.msg+'</span>');
			}, 'json');
		}
		else alert(err_msg);	
	});
 });
</script>
</body>
</html>