<?php

	function Register()
	{
	
		// validate we have arguments
		if (! isset($_REQUEST['sf']['TEXT']['score_register']) )
			return false;

		// just make this variable a little more convenient to use
		$sr =& $_REQUEST['sf']['TEXT']['score_register'];
		$srv =& $GLOBALS['SCORE']['sc_REQUEST']; // validated form data

		// validate num of arguments passed
		if (! sizeof($sr) === 2)
			return false;


		// validate login length
		if( strlen($sr['login']) <= 3 )
		{
			$GLOBALS['SCORE']['errors'][] = 'Sorry, your login is to short. Minimum 4 characters.';
			return false;
		}

		
		// valid looking email address?
		if( substr_count($sr['email'], '@') !== 1 )
		{
			$GLOBALS['SCORE']['errors'][] = 'Sorry, the email address you provided does not appear to be valid.';
			return false;
		}


		//	check database that user of this login doesn't already exist
		$sql = "SELECT login FROM users WHERE login='{$srv->values['TEXT']['score_register']['login']}'";
		$r = dbq($sql);

		if( sizeof($r) )
		{
			$GLOBALS['SCORE']['errors'][] = 'This login is taken already, try another.';
			return false;
		}


		//	check database that this email doesn't already exist in the database
		$sql = "SELECT email FROM users WHERE email='{$srv->values['TEXT']['score_register']['email']}'";
		$r = dbq($sql);

		if( sizeof($r) )
		{
			$GLOBALS['SCORE']['errors'][] = 'This email is taken already, try another.';
			return false;
		}


		// generate temporary password
		// 48-57,65-90,97-122

		//generate char array
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$chars_length = strlen($chars);
		$pass = '';
		for( $i=0; $i < 10; $i++ )
			$pass .= $chars[rand(0, $chars_length-1)];
			

		$body = "
Your SCORE Registration has been accepted.

Your login is:
{$sr['login']}

Your temporary password is:
{$pass}

Please login here:
http://{$_SERVER['SERVER_NAME']}{$_SERVER['PHP_SELF']}

Thank-you for participating.
";
		
		$headers = array('From' => $GLOBALS['SCORE']['ADMIN_EMAIL'],'Subject' => 'SCORE Registration Complete');
		

		if ( score_mail($sr['email'], $body, $headers) )
		{
			$sql = "INSERT INTO users (login, pass, email, created) VALUES ('{$srv->values['TEXT']['score_register']['login']}','{$pass}', '{$srv->values['TEXT']['score_register']['email']}', now())";
			dbsql($sql);
			$GLOBALS['register_ok'] = true;
		}


	}




function html_register_form()
{
	if( ! isset($GLOBALS['register_ok']) )
	{
		echo "
			
				<div class=\"ldiv\">
					Login
				</div>
				<div class=\"rdiv\">
					<input type=\"text\" name=\"sf[TEXT][score_register][login]\" size=\"32\" maxlength=\"255\" value=\"". @inputValue($_REQUEST['score_register']['login']) ."\">
				</div>

				<div class=\"ldiv\">
				Email
				</div>
				<div class=\"rdiv\">
					<input type=\"text\" name=\"sf[TEXT][score_register][email]\" size=\"32\" maxlength=\"255\" value=\"". @inputValue($_REQUEST['score_register']['email'])."\">
				</div>
				<br>
				<div style=\"text-align: right\">
					(<a href=\"". basename($_SERVER['PHP_SELF']) ."?score_lib=login\" tabindex=\"4\">return to login</a>)
					<input type=\"hidden\" name=\"score_lib\"	value=\"register\">
					<input type=\"submit\" name=\"score_call\" value=\"Register\">
				</div>

			";
	} else {
		echo "
		<p>
		Thank-you for Registering. An Email has been sent to the address you provided containing your temporary login password.
		<p>
		<div style=\"text-align: right;\">
			(<a href=\"". basename($_SERVER['PHP_SELF']) ."?score_lib=login\">return to login</a>)
		</div>
		";
	}
}

?>