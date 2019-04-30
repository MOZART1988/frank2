<?
/*
Title: Класс работы со строками
Author: ShadoW <shadow_root@mail.ru>
Date: 8.06.2010
*/
class Strings 
{
	public $lang;

	function __construct(&$lang){
		$this->lang = &$lang;
	}
	/*
	# Как юзать
	$api->strings->date(дата, из, в)

	Из – [sql, date]
	В [sql] ->
		day – день недели
		date – d.m.Y
		textdate – d месяц Y
		datetime – d.m.Y в h:i
		textdatetime - d месяц Y в h:i
		textdateday – d месяц Y, день недели
	*/
	function date($str, $from='sql', $to='textdate')
	{	
		# Локации
		$lang_mass = Array(
							'ru'=>Array(
										'at'=>'в',
										'mounth'=>Array(
													'01'=>'января',
													'02'=>'февраля',
													'03'=>'марта',
													'04'=>'апреля',
													'05'=>'мая',
													'06'=>'июня',
													'07'=>'июля',
													'08'=>'августа',
													'09'=>'сентября',
													'10'=>'октября',
													'11'=>'ноября',
													'12'=>'декабря'),
										'days'=>Array(
													'воскресенье',
													'понедельник',
													'вторник',
													'среда',
													'четверг',
													'пятница',
													'суббота')			
							),
							'en'=>Array(
										'at'=>'at',
										'mounth'=>Array(
													'01'=>'january',
													'02'=>'february',
													'03'=>'march',
													'04'=>'april',
													'05'=>'may',
													'06'=>'june',
													'07'=>'july',
													'08'=>'agust',
													'09'=>'september',
													'10'=>'october',
													'11'=>'november',
													'12'=>'december'),
										'days'=>Array(
													'sunday',
													'monday',
													'thusday',
													'Wednesday',
													'thursday',
													'friday',
													'saturday')
							),
							
							'kz'=>Array(
										'at'=>'',
										'mounth'=>Array(
													'01'=>'қаңтар',
													'02'=>'ақпан',
													'03'=>'наурыз',
													'04'=>'сәуір',
													'05'=>'мамыр',
													'06'=>'маусым',
													'07'=>'шілде',
													'08'=>'қазан',
													'09'=>'қыркүйек',
													'10'=>'қазан',
													'11'=>'қараша',
													'12'=>'желтоқсан'),
										'days'=>Array(
													'жексенбі',
													'дүйсенбі',
													'сейсенбі',
													'жағдай',
													'бейсенбі',
													'жұма',
													'сенбі')
							)
					);
						
		
		# Если из SQL формата
		if ($from == 'sql')
		{
			$date_time 	= explode(' ', $str);
			$date 		= explode('-', $date_time[0]);
			$time 		= @explode(':', $date_time[1]);
			$stamp		= @mktime(0, 0, 0, $date[1], $date[2], $date[0], 0);
			
			# в день недели
			if ($to == 'day') {
				return $lang_mass[$this->lang]['days'][date('w', $stamp)];
			}
			
			
			# в Обычный тип даты
			if ($to == 'date') {
				return $date[2].'.'.$date[1].'.'.$date[0];
			}
			
			# в Текстовая дата
			if ($to == 'textdate')	{
				if (substr($date[2], 0, 1) == 0) { $date[2] = substr($date[2], 1); }
				return $date[2].' '.$lang_mass[$this->lang]['mounth'][$date[1]].' '.$date[0];
			}
			
			# в Дата и время
			if ($to == 'datetime') {
				return $date[2].'.'.$date[1].'.'.$date[0].' '.$lang_mass[$this->lang]['at'].' '.$time[0].':'.$time[1];
			}
			
			# в Текстовые дата и время
			if ($to == 'textdatetime') {
				if (substr($date[2], 0, 1) == 0) { $date[2] = substr($date[2], 1); } 
				if (substr($time[0], 0, 1) == 0) { $time[0] = substr($time[0], 1); } 
				return $date[2].' '.$lang_mass[$this->lang]['mounth'][$date[1]].' '.$date[0].' '.$lang_mass[$this->lang]['at'].' '.$time[0].':'.$time[1];
			}
			
			# в Текстовые дата и день недели
			if ($to == 'textdateday') {
				if (substr($date[2], 0, 1) == 0) { $date[2] = substr($date[2], 1); } 
				return $date[2].' '.$lang_mass[$this->lang]['mounth'][$date[1]].' '.$date[0].', '.$lang_mass[$this->lang]['days'][date('w', $stamp)];
			}
		}
		
		# --------------------------------------------
		# Из обычного формата
		
		if ($from == 'date')
		{
			$date_time 	= explode('.', $str);

			# в SQL
			if ($type_to == 'sql')
			{
				return $date_time[2].'-'.$date_time[1].'-'.$date_time[0];
			}
		}
		
		return false;
	}
}
?>