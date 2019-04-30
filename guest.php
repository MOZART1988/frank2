<?
include('cms/public/api.php');

$obj_id   = 62;
$class_id = 13;

# ДОБАВЛЕНИЕ КОММЕНТАРИЯ
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'))
{	
	$answer = array('status'=>'error', 'msg'=>'Неверный запрос');
	if (
		!empty($_POST['action']) && ($_POST['action'] == 'add') &&
		!empty($_POST['name']) && ($name = $api->db->prepare($_POST['name'])) && 
		!empty($_POST['email']) && ($email = $api->db->prepare($_POST['email'])) && (preg_match('/^(.*)@(.*)\.(.*){2,4}/', $email)) &&
		!empty($_POST['text']) && ($text = $api->db->prepare($_POST['text']))
		)
	{
		# ДОБАВЛЯЕМ
		if ($api->objects->createObjectAndFields(
			array(
				'name'		=> '['.date('d.m.Y H:i').'] Запись гостевой книги от '.$_SERVER['REMOTE_ADDR'],
				'head'		=> $obj_id,
				'class_id'	=> $class_id
			),			
			array(
				'Имя'		=> $name,
				'E-Mail'	=> $email,
				'Текст'		=> $text
			)
		))
		{
	
			$answer = array('status'=>'ok');
		}
		else $answer['msg'] = 'Невозможно создать объект';
	
	}
	
	exit($api->JSON($answer));
}
$api->header(array('page-title'=>'Гостевая книга'));


# ОТЗЫВЫ
$pages = $api->pages($api->objects->getObjectsCount($obj_id, $class_id, "AND o.active='1'"), 10, 5, array(), '/'.$api->lang.'/guest/#pg#/');

# Получаем записей
if($records = $api->objects->getFullObjectsListByClass($obj_id, $class_id, "AND o.active='1' ORDER BY o.sort DESC LIMIT ".$pages['start'].", 10"))
{
	$out = array();
	foreach($records as $o)
	{
		$out[] = '
		<div class="guest_record">
			<div class="quest_name"><a href="mailto:'.$o['E-Mail'].'">'.$o['Имя'].'</a></div>
			<div class="guest_date">'.$api->strings->date(date('Y-m-d H:i:s'), 'sql', 'textdatetime').'</div>
			<div class="guest_text">'.nl2br($o['Текст']).'</div>
			'.(!empty($o['Комментарий']) ? '<div class="guest_comment">'.nl2br($o['Комментарий']).'</div>' : '').'
		</div>';
	}
	
	echo '
	<div style="margin-top:20px;">
		'.join('<hr class="dotted" />', $out).'
	</div>
	<div style="margin-bottom:10px;">
		'.$pages['html'].'
	</div>';	
}
?>
<div id="guest_form">
	<h1>Оставьте Ваш отзыв</h1>
	<div class="form_row">
		<div>Ваше имя:</div>
		<div><input type="text" id="g_name" name="g_name" value="" style="width:450px;" maxlength="100" /></div>
	</div>
	<div class="form_row">
		<div>Ваш e-mail:</div>
		<div><input type="text" id="g_email" name="g_email" value="" style="width:450px;" maxlength="100" /></div>
	</div>
	<div class="form_row">
		<div>Ваш отзыв:</div>
		<div><textarea id="g_text" name="g_text" style="width:450px; height:150px;"></textarea></div>
	</div>
	<div id="g_status"></div>
	<div class="form_row">
		<input type="button" id="g_add_btn" value="ДОБАВИТЬ ОТЗЫВ" />
	</div>
</div>
<script type="text/javascript">
 $(function()
 {
	$("#g_add_btn").click(function()
	{
		var err_msg = '';
		var focused = false;
		
		if (($("#g_name").val() == '') || ($("#g_name").val().length < 3)) 		{ err_msg = err_msg+"Укажите Ваше имя\n"; 				if (!focused) { $("#g_name").focus(); focused = true; } }
		if (!CheckEmail($("#g_email").val()))									{ err_msg = err_msg+"Укажите правильный e-mail\n"; 		if (!focused) { $("#g_email").focus(); focused = true; } }
		if (($("#g_text").val() == '') || ($("#g_text").val().length < 10))		{ err_msg = err_msg+"Слишком короткий текст отзыва\n"; 	if (!focused) { $("#g_text").focus(); focused = true; } }
	
		if (err_msg == '')
		{
			$("#g_status").html("Добавление отзыва...");
			$.post("<?=$_SERVER['PHP_SELF']?>", 
				{
					'action': 'add',
					'name'	: $("#g_name").val(),
					'email'	: $("#g_email").val(),
					'text'	: $("#g_text").val() 
				}, function(json) {
				
					if (json.status == 'ok') 
					{					
						$("#g_status").html('<span style="color:green; font-size:16px;">Ваш отзыв успешно добавлен!</span>');
						$('#guest_form input[type="text"], #guest_form textarea').val("");
					} 
					else if ((json.status == 'error') && (json.msg)) 
					{
						alert("Ошибка: "+json.msg);
					}			
				}, 'json');
		}
		else alert(err_msg);
	}); 
 });
</script>
<?
$api->footer();
?>