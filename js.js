$(function()
{

	$('.bxslider').bxSlider({
		auto: true,
		mode: 'fade',
		speed: 1200,
		pause: 3000
	});
	$('#CallOrder').submit(function(){
		$.ajax({
			url: '/send.php',
			data: $(this).serialize(),
			type: "POST",
			success: function(data){
				$('.result').html(data);
			}
		});
		return false;
	});

	$('.table-basket-del').click(function(){
		var id = $(this).attr('num');
		$.ajax({
			url: '/rycleRemove.php',
			data: "id="+id,
			type: "POST",
			success: function(data){
				location.reload();
			}
		});
		return false;
	});
	// Покупка товара
	$('.itemBuy').click(function(){
		var id = $(this).attr('data-id');
		$.ajax({
			url: '/rycleAdd.php',
			data: "id="+id,
			type: "POST",
			success: function(data){
				location.reload();
			}
		});
		return false;
	});
	// Просмотр фоток
	$("#map, .masterphoto, .galleryphoto, .galleryvideo, #subsribe").fancybox();
		
	// Верхнее меню
	$("#topmenu li").hover(
		function() { $(this).find(".topsubmenu").slideDown("fast"); },
		function() { $(this).find(".topsubmenu").hide(); }	
	);

	$('.nav--btn').on('click', function(){
		if ($('#topmenu').is(':visible')) {
			$('#topmenu').slideUp();
			$(this).removeClass('open');
		} else {
			$('#topmenu').slideDown();
			$(this).addClass('open');
		}
		return false;
	});

	$('.phones--btn').on('click', function(){
		if($('#topcontacts').is(':visible')) {
			$('#topcontacts').slideUp().removeClass('open');
		} else {
			$('#topcontacts').slideDown().addClass('open');
		}
		$(document).bind("touchstart",function(e) {
		    if (!$(e.target).closest("#topcontacts.open").length) {
		        $('#topcontacts').slideUp().removeClass('open');
		    }
		    e.stopPropagation();
		});
		return false;
	});

	$('.cat_menu_btn').on('click', function(){
		$(this).parent().toggleClass('open');
		return false;
	});

	
	// Прайсы
	$(".price_line").hover(
		function() { $(this).find("div").addClass("price_hover"); },
		function() { $(this).find("div").removeClass("price_hover"); }
	);
	
	$('.needClear')
		 .focus(function(){
			  if(this.value==this.title) this.value='';
		 })
		 .blur(function(){
			  if(this.value=='') this.value=this.title;
	});
	
	
	// Меню гарможка в каталогах
	$('a.catalog_lnk').click(function()
	{
		$('#cat_menu').find('.cat_menu_element').removeClass('setcat').find('.subcats').hide();
		$(this).parent().addClass('setcat').find('.subcats').slideDown();
		
		return false;
	});

	// Модальное окно

	$('.rycle-block .btn').click(function(){
		$('.modal').fadeIn();
	});

	$('.modal .close-btn').click(function(){
		$('.modal').fadeOut();
		return false;
	});

	// Инпут количества

	$('.minus').click(function () {
       var $input = $(this).parent().find('.quantily');
       var count = parseInt($input.val());
       var price = parseInt($(this).attr('price'));
       var sum = parseInt($('.table-basket-total-right span').text());

       var total_sum = sum;
       if(count > 1){
       	total_sum = sum - price;
       	$.ajax({
	    	url: '/rycleChange.php',
	    	data: "sum="+total_sum+"&id="+$(this).attr('data-id')+"&action=minus",
	    	type: "POST",
	    	success: function(data){

	    	}
	    });
       }

       count = count - 1;
       count = count < 1 ? 1 : count;
       $input.val(count);
	   $('.table-basket-total-right span').text(total_sum + ' тг');
       $input.change();
       return false;
    });
	$('.plus').click(function () {
	    var $input = $(this).parent().find('.quantily');
	    var count = parseInt($input.val()) + 1;
	    $input.val(count);
	    $input.change();
	    var price = parseInt($(this).attr('price'));
	    var total_sum = parseInt($('.table-basket-total-right span').text()) + price;
	    $('.table-basket-total-right span').text(total_sum + ' тг');
	    $.ajax({
	    	url: '/rycleChange.php',
	    	data: "sum="+total_sum+"&id="+$(this).attr('data-id')+"&action=plus",
	    	type: "POST",
	    	success: function(data){

	    	}
	    });
	    return false;
	});

	// Чекбокс

	$('input[type="radio"]').change(function() {
		if ($('#two').is(':checked')) {
			$('.address').removeAttr('required').fadeOut();
		} else {
			$('.address').attr('required','required').fadeIn();
		}
	});
	

	// Элементы каталога
	$(".cat_img").fancybox();
	
	setInterval(function()
	{ 
	     var $active = $("#slideshow P.active");
	     var $next =  $active.next().length ? $active.next()
		: $("#slideshow P:first");

	     $active.addClass("last-active");

	     $next.css({opacity: 0.0})
		.addClass("active")
		.animate({opacity: 1.0}, 2000, function() {
			$active.removeClass("active last-active");
		});  
	}, 5000);
	setInterval(function()
	{ 
	     var $active = $("#slideshow2 P.active");
	     var $next =  $active.next().length ? $active.next()
		: $("#slideshow2 P:first");

	     $active.addClass("last-active");

	     $next.css({opacity: 0.0})
		.addClass("active")
		.animate({opacity: 1.0}, 2000, function() {
			$active.removeClass("active last-active");
		});  
	}, 5000);
});


// Проверка E-Mail'ов
function CheckEmail(value)
{ 
  var re = /^\w+([\.-]?\w+)*@(((([a-z0-9]{2,})|([a-z0-9][-][a-z0-9]+))[\.][a-z0-9])|([a-z0-9]+[-]?))+[a-z0-9]+\.([a-z]{2}|(com|net|org|edu|int|mil|gov|arpa|biz|aero|name|coop|info|pro|museum))$/i; 
  if(re.test(value)) { return true; } else { return false; } 
}

// Инпут маска
(function($){var pasteEventName=($.browser.msie?'paste':'input')+".mask";var iPhone=(window.orientation!=undefined);$.mask={ definitions:{'9':"[0-9]",'a':"[A-Za-z]",'*':"[A-Za-z0-9]"}};$.fn.extend({ caret:function(begin,end){if(this.length==0)return;if(typeof begin=='number'){end=(typeof end=='number')?end:begin;return this.each(function(){if(this.setSelectionRange){this.focus();this.setSelectionRange(begin,end);}else if(this.createTextRange){var range=this.createTextRange();range.collapse(true);range.moveEnd('character',end);range.moveStart('character',begin);range.select();}});}else{if(this[0].setSelectionRange){begin=this[0].selectionStart;end=this[0].selectionEnd;}else if(document.selection&&document.selection.createRange){var range=document.selection.createRange();begin=0-range.duplicate().moveStart('character',-100000);end=begin+range.text.length;}
return{begin:begin,end:end};}},unmask:function(){return this.trigger("unmask");},mask:function(mask,settings){if(!mask&&this.length>0){var input=$(this[0]);var tests=input.data("tests");return $.map(input.data("buffer"),function(c,i){return tests[i]?c:null;}).join('');}
settings=$.extend({placeholder:"_",completed:null},settings);var defs=$.mask.definitions;var tests=[];var partialPosition=mask.length;var firstNonMaskPos=null;var len=mask.length;$.each(mask.split(""),function(i,c){if(c=='?'){len--;partialPosition=i;}else if(defs[c]){tests.push(new RegExp(defs[c]));if(firstNonMaskPos==null)
firstNonMaskPos=tests.length-1;}else{tests.push(null);}});return this.each(function(){var input=$(this);var buffer=$.map(mask.split(""),function(c,i){if(c!='?')return defs[c]?settings.placeholder:c});var ignore=false; var focusText=input.val();input.data("buffer",buffer).data("tests",tests);function seekNext(pos){while(++pos<=len&&!tests[pos]);return pos;};function shiftL(pos){while(!tests[pos]&&--pos>=0);for(var i=pos;i<len;i++){if(tests[i]){buffer[i]=settings.placeholder;var j=seekNext(i);if(j<len&&tests[i].test(buffer[j])){buffer[i]=buffer[j];}else
break;}}
writeBuffer();input.caret(Math.max(firstNonMaskPos,pos));};function shiftR(pos){for(var i=pos,c=settings.placeholder;i<len;i++){if(tests[i]){var j=seekNext(i);var t=buffer[i];buffer[i]=c;if(j<len&&tests[j].test(t))
c=t;else
break;}}};function keydownEvent(e){var pos=$(this).caret();var k=e.keyCode;ignore=(k<16||(k>16&&k<32)||(k>32&&k<41)); if((pos.begin-pos.end)!=0&&(!ignore||k==8||k==46))
clearBuffer(pos.begin,pos.end); if(k==8||k==46||(iPhone&&k==127)){ shiftL(pos.begin+(k==46?0:-1));return false;}else if(k==27){ input.val(focusText);input.caret(0,checkVal());return false;}};function keypressEvent(e){if(ignore){ignore=false; return(e.keyCode==8)?false:null;}
e=e||window.event;var k=e.charCode||e.keyCode||e.which;var pos=$(this).caret();if(e.ctrlKey||e.altKey||e.metaKey){ return true;}else if((k>=32&&k<=125)||k>186){ var p=seekNext(pos.begin-1);if(p<len){var c=String.fromCharCode(k);if(tests[p].test(c)){shiftR(p);buffer[p]=c;writeBuffer();var next=seekNext(p);$(this).caret(next);if(settings.completed&&next==len)
settings.completed.call(input);}}}
return false;};function clearBuffer(start,end){for(var i=start;i<end&&i<len;i++){if(tests[i])
buffer[i]=settings.placeholder;}};function writeBuffer(){return input.val(buffer.join('')).val();};function checkVal(allow){ var test=input.val();var lastMatch=-1;for(var i=0,pos=0;i<len;i++){if(tests[i]){buffer[i]=settings.placeholder;while(pos++<test.length){var c=test.charAt(pos-1);if(tests[i].test(c)){buffer[i]=c;lastMatch=i;break;}}
if(pos>test.length)
break;}else if(buffer[i]==test[pos]&&i!=partialPosition){pos++;lastMatch=i;}}
if(!allow&&lastMatch+1<partialPosition){input.val("");clearBuffer(0,len);}else if(allow||lastMatch+1>=partialPosition){writeBuffer();if(!allow)input.val(input.val().substring(0,lastMatch+1));}
return(partialPosition?i:firstNonMaskPos);};if(!input.attr("readonly"))
input.one("unmask",function(){input.unbind(".mask").removeData("buffer").removeData("tests");}).bind("focus.mask",function(){focusText=input.val();var pos=checkVal();writeBuffer();setTimeout(function(){if(pos==mask.length)
input.caret(0,pos);else
input.caret(pos);},0);}).bind("blur.mask",function(){checkVal();if(input.val()!=focusText)
input.change();}).bind("keydown.mask",keydownEvent).bind("keypress.mask",keypressEvent).bind(pasteEventName,function(){setTimeout(function(){input.caret(checkVal(true));},0);});checkVal();});}});})(jQuery);

$(function(){
	$('.input-phone').mask('+7 (999) 999-9999');

	$('.call-form-btn').click(function(){
		$('.call-form').fadeIn();
	});
	$('.close-form').click(function(){
		$('.call-form').fadeOut();
	});

	$('#CallOrder').submit(function(){
		var name = $('#CallName').val();
		var phone = $('#CallPhone').val();
		var email = $('#CallEmail').val();
		var emailList = $('#EmailList').val();
		$.ajax({
			type: "POST",
			url: "../cms/public/sendmail.php",
			data: "name="+name+"&phone="+phone+"&email="+email+"&emailList="+emailList,
			success: function(data){
				if(data == 'success'){
					$('.form-result').text('Заявка успешно отправлена.');
					$('.form-result').css('color', '#2ecc71');
				}
			}
		});
	});
})

