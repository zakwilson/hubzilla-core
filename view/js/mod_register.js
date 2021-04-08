$(document).ready(function() {

	// set in Module
	//typeof(window.tao) == 'undefined' ? window.tao = {} : '';
	//tao.zar = { vsn: '2.0.0', form: {}, msg: {} };
	//tao.zar.patano = /^d[0-9]{6}$/;
	//tao.zar.patema = /^[a-z0-9.-]{2,64}@[a-z0-9.-]{4,32}\.[a-z]{2,12}$/;

	$('#zar014').click( function () { $('#zar015').toggle(); });

	$('#id_email').change(function() {
		tao.zar.form.email = $('#id_email').val();
		if (tao.zar.patano.test(tao.zar.form.email) == true ) {
			//ano
		} else {
			if (tao.zar.patema.test(tao.zar.form.email) == false ) {
				$('#help_email').removeClass('text-muted').addClass('text-danger').html(tao.zar.msg.ZAR0239E);
				zFormError('#help_email',true);
			} else {
				$.get('register/email_check.json?f=&email=' + encodeURIComponent(tao.zar.form.email), function(data) {
				$('#help_email').removeClass('text-muted').addClass('text-danger').html(data.message);
				zFormError('#help_email',data.error);
				});
			}
		}
		if ($('#id_email').val().length > 0) {
			$('#newchannel-submit-button').removeAttr('disabled');
		}
	});

	$('#id_password').change(function() {
		if(($('#id_password').val()).length < 6 ) {
			$('#help_password').removeClass('text-muted').addClass('text-danger').html(aStr.pwshort);
			zFormError('#help_password', true);
		}
		else {
			$('#help_password').html('');
			zFormError('#help_password', false);
			$('#id_password2').focus();
			$('#id_password2').val().length > 0 ? $('#id_password2').trigger('change') : '';
		}
	});
	$('#id_password2').change(function() {
		if($('#id_password').val() != $('#id_password2').val()) {
			$('#help_password2').removeClass('text-muted').addClass('text-danger').html(aStr.pwnomatch);
			zFormError('#help_password2', true);
			$('#id_password').focus();
		}
		else {
			$('#help_password2').html('');
			zFormError('#help_password2', false);
		}
	});

	$('#id_name').blur(function() {
		$('#name-spinner').show();
		var zreg_name = $('#id_name').val();
		$.get('new_channel/autofill.json?f=&name=' + encodeURIComponent(zreg_name),function(data) {
			$('#id_nickname').val(data);
			if(data.error) {
				$('#help_name').html('');
				zFormError('#help_name',data.error);
			}
			$('#name-spinner').hide();
		});
	});
	$('#id_nickname').blur(function() {
		$('#nick-spinner').show();
		var zreg_nick = $('#id_nickname').val();
		$.get('new_channel/checkaddr.json?f=&nick=' + encodeURIComponent(zreg_nick),function(data) {
			$('#id_nickname').val(data);
			if(data.error) {
				$('#help_nickname').html('');
				zFormError('#help_nickname',data.error);
			}
			$('#nick-spinner').hide();
		});
	});

	//$("buttom[name='submit']").submit((function() {
	$('#register-form').submit(function(e) {
		if ( $('.zform-error').length > 0 ) {
			e.preventDefault();
			return false;
		}
	});
});
