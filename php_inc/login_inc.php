<?php


	function Login ()
	{
		$sc_REQUEST =& $GLOBALS['SCORE']['sc_REQUEST']; // just makes referencing this a little easier

		if ( isset($sc_REQUEST->values['TEXT']['score_login']) && isset($sc_REQUEST->values['TEXT']['score_pass']) )
		{	
			$login_result = dbq("SELECT * FROM users WHERE login='{$sc_REQUEST->values['TEXT']['score_login']}' AND pass='{$sc_REQUEST->values['TEXT']['score_pass']}'");
			// valid login?

			if ( $login_result && sizeof($login_result) === 1 && $login_result[0]['login'] == $_POST['sf']['TEXT']['score_login'] && $login_result[0]['pass'] == $_POST['sf']['TEXT']['score_pass'])
			{
				// login looks good
				// - setup general session info regarding user in question
				$_SESSION['score_user'] =  new scUser($login_result[0]);
				$_SESSION['score_lib'] = 'main';
				unset($_SESSION['score_guest']);
				dbq("UPDATE users SET lastlastlogin=users.lastlogin, lastlogin=now(), flagged_idle=NULL WHERE id={$_SESSION['score_user']->data['id']}");
			}
		}

		// either way we'll redirect them
		header("Location: http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/".basename($_SERVER['PHP_SELF'])); 
	}


	function show_public_register()
		{
			if(strtolower($GLOBALS['SCORE']['PUBLIC_REG']) === 'on' )
				echo "(<a href=\"".basename($_SERVER['PHP_SELF'])."?score_lib=register\">Register</a>)";
			else
				echo '&nbsp;';
		}
?>