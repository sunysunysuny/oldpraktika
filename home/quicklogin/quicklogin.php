<?php
$emptyText = "eMail-Adresse oder LoginID";
?>

<div id="login_ajax">
	<form method="post" action="#" id="quickloginForm" name='login_form' onsubmit="return ajaxlogin(); return false;">
		<fieldset>
			<p id="ajax_input_username">
				<input type="text" class="<?= !empty($_COOKIE['c_lid']) ? 'active' : 'inactive' ?>" name="username" id="username" onblur="if (!this.value) { this.value = '<?= $emptyText; ?>'; this.setAttribute('class', 'inactive'); }" onfocus="if (this.value == '<?= $emptyText; ?>') { this.value = ''; this.setAttribute('class', 'active'); }" onkeyup="checkEnter(event);" value="<?= !empty($_COOKIE['c_lid']) ? $_COOKIE['c_lid'] : $emptyText; ?>" />
			</p>

			<p>
				<input type="password" class="inactive" onblur="if (!this.value) { this.value = 'Passwort'; this.setAttribute('class', 'inactive'); }" onfocus="if (this.value == 'Passwort') { this.value = ''; this.setAttribute('class', 'active'); }" name="password" id="password" onkeyup="checkEnter(event);" value="" />
			</p>
			<p class="links">
				 <a href="/neuanmeldung/" class="floatleft">Neu registrieren!</a>
				 <a onclick="smallboxManager.getById('login').loadUrl('','/home/quicklogin/passwdwindow_smallbox.phtml','',{width:440, title:'Passwort vergessen'}); return false;" class="floatright" href="/home/quicklogin/passwdwindow.phtml">Passwort vergessen?</a>
			</p>
		</fieldset>
		<fieldset class="control_panel">
			<p><input type="submit" class="button small" name="send" onclick="ajaxlogin(); return false;" value="EINLOGGEN" /></p>
		</fieldset>
		<div class="loginStatus" id="loginStatus" style="display:none;margin-top:10px;"></div>				
	</form>
</div>

<!--
<p><fb:login-button autologoutlink="true"></fb:login-button></p>
<p><fb:like></fb:like></p>

<div id="fb-root"></div>

<script>
	window.fbAsyncInit = function() {
		FB.init({appId: '139054006146702', status: true, cookie: true, xfbml: true});
	};
	(function() {
		var e = document.createElement('script');
		e.type = 'text/javascript';
		e.src = document.location.protocol + '//connect.facebook.net/de_DE/all.js';
		e.async = true;
		document.getElementById('fb-root').appendChild(e);
	}());
</script>
-->
    

<script type="text/javascript">
	function checkEnter(e) {
		var keycode;

		if (window.event)
		{
				keycode = window.event.keyCode;
		}
		else if (e)
		{
				keycode = e.keyCode;
		}
		if (keycode == 13) {
			ajaxlogin();
		}
	}

	function ajaxlogin() {
		if ($('#username').val() == "<?php echo $emptyText; ?>") return false;

		 $('#loginStatus').html('<img src="/styles/images/ajax/loading_2.gif" alt="L&auml;dt" />');
		document.body.style.cursor = 'wait';

		$.ajax({
			  type: 'POST',
			  url: '/home/quicklogin/remoteLogin.php',
			  data: {'username': $('#username').val(), 'passwort': $('#password').val()},
			  success: function(text) {
					document.body.style.cursor = 'default';

					text = text.split('#');

					if (text[0] == 'true') {
						smallbox.hide('true');
					} else if(text[0] == 'false') {
						smallbox.shake();
						
						$('#loginStatus').html('<p class="hint error" style="margin:0;">Benutzername/Passwort nicht korrekt</p>');
						if($('#loginStatus').css("display") == "none") {
							$('#loginStatus').slideDown("fast");
						}
					} else if(text[0] == "verify_mail") {
						location.href = '/home/praktikanten/verify_account.phtml?id=' + text[1];
					} else if(text[0] == 'old_data') {
						location.href = '/home/praktikanten/stammdaten_aktualisieren.phtml';
					} else {
						$('#loginStatus').html('<p class="hint error">Beim Login trat ein Fehler auf.</p>');
					}
			  },
			  dataType: 'html'
			});

		return false;
	}
</script>

<?
exit();
?>

<form method="post" action="" id="quickloginForm" onsubmit="return ajaxlogin(); return false;">

</form>