<?
# AJAX FEEDBACK
include('cms/public/api.php');

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))
{
	# Если отправка письма
	if (
		!empty($_POST['action']) && ($_POST['action'] == 'send') &&
		!empty($_POST['email']) && preg_match('/^(.*)@(.*)\.(.*){2,4}$/', $_POST['email']) && 
		!empty($_POST['name']) && 
		!empty($_POST['text'])
	   )
	{
		# Подготовка данных
		$email = $api->db->prepare($_POST['email']);
		$name  = $api->db->prepare($_POST['name']);
		$text  = nl2br($api->db->prepare($_POST['text']));

		# Подключаем почтовый класс
		include_once(_FILES_ABS_.'/mail.php');
		$smail = new mime_mail();

		$smail->to 			= 'olgafpa@mail.ru';
		$smail->from 		= 'admin@'.str_replace('www.', '', $_SERVER['HTTP_HOST']);
		$smail->subject		= 'Сообщение с сайта '.str_replace('www.', '', $_SERVER['HTTP_HOST']);
		$smail->body		= '<html>
<body>
Отправлено: '.date('d.m.Y').' в '.date('h:i').' с IP '.$_SERVER['REMOTE_ADDR'].'<br/>
<br/>
<b>E-Mail:</b> <a href="mailto:'.$email.'">'.$email.'</a><br/>
<br/>
<b>Имя:</b> '.$name.'<br/>
<br/>
<b>Сообщение:</b><br/>
'.$text.'<br/>
<br/>
</body>
</html>';

		# отправляем
		$smail->send($smail->to);
		
		echo $api->JSON(array('status'=>'ok'));
	}
	
	# ФОРМА ОБРАТНОЙ СВЯЗИ
	else 
	{
	?>
	<div id="feedback_form" style="margin:10px 0;">
		<div class="form_row">
			<div>Ваше имя:</div>
			<div><input type="text" id="f_name" value="" style="width:450px;" maxlength="100" /></div>
		</div>
		<div class="form_row">
			<div>Ваш e-mail:</div>
			<div><input type="text" id="f_email" value="" style="width:450px;" maxlength="100" /></div>
		</div>
		<div class="form_row">
			<div>Ваше сообщение:</div>
			<div><textarea id="f_text" style="width:450px; height:150px;"></textarea></div>
		</div>
		<div id="f_status" style="font-size:14px;"></div>
		<div class="form_row">
			<input type="button" id="f_send_btn" value="ОТПРАВИТЬ СООБЩЕНИЕ" />
		</div>
	</div>
	<script type="text/javascript">
	$("#f_send_btn").click(function()
	{
		var err_msg = '';
		var focused = false;
		
		if (($("#f_name").val() == '') || ($("#f_name").val().length < 3)) 		{ err_msg = err_msg+"Укажите Ваше имя\n"; 					if (!focused) { $("#f_name").focus(); focused = true; } }
		if (!CheckEmail($("#f_email").val()))									{ err_msg = err_msg+"Укажите правильный e-mail\n"; 			if (!focused) { $("#f_email").focus(); focused = true; } }
		if (($("#f_text").val() == '') || ($("#f_text").val().length < 10))		{ err_msg = err_msg+"Слишком короткий текст сообщения\n"; 	if (!focused) { $("#f_text").focus(); focused = true; } }
	
		if (err_msg == '')
		{
			$("#f_status").html("Отправка сообщения...");
			$.post("<?=$_SERVER['PHP_SELF']?>", 
				{
					'action': 'send',
					'name'	: $("#f_name").val(),
					'email'	: $("#f_email").val(),
					'text'	: $("#f_text").val() 
				}, function(json) {
				
					if (json.status == 'ok') 
					{					
						$("#f_status").html('<span style="color:green; font-size:16px;">Сообщение успешно отправлено!</span>');
						$('#feedback_form input[type="text"], #feedback_form textarea').val("");
					} 
					else if ((json.status == 'error') && (json.msg)) 
					{
						alert("Ошибка: "+json.msg);
					}			
				}, 'json');
		}
		else alert(err_msg);
	});
	</script>
	
	<?
	}
}
?>