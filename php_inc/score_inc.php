<?php

	/*
		SimCity Online Region Exchange(SCORE)

		score_inc.htm contain general functions needed throughout SCORE
	*/

	
	/*
	**	User Callable Functions (ie: via Lib/Call)
	*/
	if(isset($_SESSION['score_user'])) // general user functions
	{
		// functions registered to score can be called from anywhere
		// in the system
		$GLOBALS['SCORE']['call_functions']['score'][]		= 'logout';

		$GLOBALS['SCORE']['call_functions']['settings'][]	= 'settings';	
		$GLOBALS['SCORE']['call_functions']['settings'][]	= 'UpdateUserInfo';
		
		$GLOBALS['SCORE']['call_functions']['region'][]		= 'Upload';
		$GLOBALS['SCORE']['call_functions']['region'][]		= 'checkoutCity';
		$GLOBALS['SCORE']['call_functions']['region'][]		= 'resignCity';
		$GLOBALS['SCORE']['call_functions']['region'][]		= 'downloadCityZip';
		$GLOBALS['SCORE']['call_functions']['region'][]		= 'requestCity';
		$GLOBALS['SCORE']['call_functions']['region'][]		= 'cancelCityRequest';

		$GLOBALS['SCORE']['call_functions']['popup'][]		= 'viewCityHistory';

		$GLOBALS['SCORE']['call_functions']['main'][]		= 'downloadRegionZip';
		$GLOBALS['SCORE']['call_functions']['main'][]		= 'clearUserMessages';
		$GLOBALS['SCORE']['call_functions']['main'][]		= 'AnswerRequests';

		// adding just an empty array makes iframe a 
		// valid lib but doesn't register any functions
		$GLOBALS['SCORE']['call_functions']['iframe'][]		= '';
	} 
		elseif (isset($_SESSION['score_guest'])) // limited guest functions
	{
		$GLOBALS['SCORE']['call_functions']['login'][]		= 'Login';
		$GLOBALS['SCORE']['call_functions']['register'][]	= 'Register';
	}

	// restricted functions
	if( isset($_SESSION['score_user']) && $_SESSION['score_user']->data['privileges'] >= 100 )
	{
		$GLOBALS['SCORE']['call_functions']['admin'][]		= 'UpdateRegionSettings';
		$GLOBALS['SCORE']['call_functions']['admin'][]		= 'UpdateSCORESettings';
		$GLOBALS['SCORE']['call_functions']['admin'][]		= 'deleteRegion';
		$GLOBALS['SCORE']['call_functions']['admin'][]		= 'ImportRegion';
	}
	
	/*
	**	DEBUG FUNCTIONS
	*/

	function decho($text)
	{
		echo $text . "\n<br>";
	}

	function print_array($array)
	{
		echo "<pre style=\"text-align:left\">";
		print_r($array);
		echo "</pre>";
	}



	/*
	**	LIB/CALL Handling Functions
	*/

	function func_request () {

		// valid function names have to be prefixed by lib used in lib_request
		if(isset($_REQUEST['score_call']))
		{
			// strip out spaces from form Submit buttons
			$_REQUEST['score_call'] = str_replace(' ','',$_REQUEST['score_call']);

			if ( @in_array($_REQUEST['score_call'], $GLOBALS['SCORE']['call_functions'][$_SESSION['score_lib']])
					||
					@in_array($_REQUEST['score_call'], $GLOBALS['SCORE']['call_functions']['score'])
				)
			{
				return $_REQUEST['score_call']();

			} else {

				$GLOBALS['SCORE']['errors'][] = "Sorry, that function does not seem to exist.";
				return false;

			}

		} else {

			return false;

		}
	}



	function lib_request () {
		// in future we'll make a preference file storing names of valid libs
		if ( isset($_REQUEST['score_lib']) )
		{
			$score_lib = basename($_REQUEST['score_lib']);
			if ( isset($score_lib) && array_key_exists($score_lib, $GLOBALS['SCORE']['call_functions']) && file_exists("php_inc/{$score_lib}_inc.php")	)
			{
				$_SESSION['score_lib'] = $score_lib;
			} else {
				logout();
				return false;
			}
		}

		if (isset($_SESSION['score_lib']))
		{
			include("php_inc/" . $_SESSION['score_lib'] . "_inc.php");
		}

	}



	/*
	**	Score End-User Functions
	*/

	function logout () {
		// Unset all of the session variables. 
		session_unset();
		// Finally, destroy the session. 
		session_destroy();

		echo "
			<div style=\"color: #CC0000\"> Logged Out - Session Over</div>
			<br>
			(<a href=\"". basename($_SERVER['PHP_SELF']) ."?score_lib=login\">return to login</a>)
		";
	}



	/*
	**		Purpose: generates the region images form the city tile images
	**
	**		Called When:
	**			- a city file update occurs
	**			- region rebuild
	*/

	function gen_region_images(&$region, $doImap=false)
	{
		// generate the region image
		$scRegionImg = new scRegionImg();
		
		$scRegionImg->drawEntireRegion($region, true);
		if ($scRegionImg->status === false)
		{
			$GLOBALS['SCORE']['errors'][] = 'Could not generate Region Image';
		}

		$scRegionImg->loadCurrent();

		// resize according to our settings in score_sys (y is proportionate)
		$scRegionImg->Resize($GLOBALS['SCORE']['SC4REGION_IMG_WIDTH'],'');

		// determine scale for calculating the image map vectors
		if ( substr($GLOBALS['SCORE']['SC4REGION_IMG_WIDTH'],-1) == '%' ) // if its in percent
			$scale = substr($GLOBALS['SCORE']['SC4REGION_IMG_WIDTH'],0,-1) / (100);
		else
			$scale = (imagesx($scRegionImg->current)/$sizeX);


		if($doImap)
		{
			if(isset($scale)) 
				$scRegionImg->Imap = scale_Imap($scRegionImg->Imap, $scale);

			$sql = "UPDATE regions SET imagemap='".mysql_real_escape_string(serialize($scRegionImg->Imap))."' WHERE id = {$region['id']}";
			dbsql($sql);
		}

		$scRegionImg->setAlphaTrans($scRegionImg->current, false, false, 0,0,0);
		$scRegionImg->writeImg("{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$region['name']}/{$region['name']}.png");

		/// generate thumbnail for region image
		$scRegionImg->loadCurrent();
		$scRegionImg->Resize('258','149');
		$scRegionImg->setAlphaTrans($scRegionImg->current, false, false, 0,0,0);
		$scRegionImg->writeImg("{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$region['name']}/{$region['name']}_thumb.png");
	}



	/*	
	**	Purpose: provided with a valid Imap array (by the scRegionImg Class)
	**	and a numeric to scale by, scale_Imap will adjust the Imap array by $scale.
	** For example, you resze the original region image smaller by 20% - so you
	**	want to call scale_Imap to adjust the Imap vectors use $scale of 0.2
	**
	**	Called When: gen_region_images() is called with $doImap set to True
	*/
	function scale_Imap($Imap, $scale)
	{
		foreach($Imap as $key => $tile)
			{
				$Imap[$key]['x1'] = ceil($tile['x1'] * $scale);
				$Imap[$key]['y1'] = ceil($tile['y1'] * $scale);

				$Imap[$key]['x2'] = ceil($tile['x2'] * $scale);
				$Imap[$key]['y2'] = floor($tile['y2'] * $scale);

				$Imap[$key]['x3'] = floor($tile['x3'] * $scale);
				$Imap[$key]['y3'] = floor($tile['y3'] * $scale);

				$Imap[$key]['x4'] = floor($tile['x4'] * $scale);
				$Imap[$key]['y4'] = ceil($tile['y4'] * $scale);

				$Imap[$key]['x5'] = ceil($tile['x5'] * $scale);
				$Imap[$key]['y5'] = ceil($tile['y5'] * $scale);
			}
		return $Imap;
	}



	/*
	**	Internal use functions
	*/

	function write_file ($path, &$data)
		{
			if($fp=fopen($path,"wb"))
			{
				fwrite($fp, $data);
				fclose($fp);
				return true;
			} else {
				return false;
			}
		}


	// part of a simple system for score to provider users
	// with feedback/errors to their actions. Append your
	// functions feedback to the $GLOBALS['SCORE']['errors']
	// array. The feedback will be displayed to the user
	// automaticly.
	function scoreErrorsOut()
		{
			if(! sizeof($GLOBALS['SCORE']['errors']) )
				return false;

			echo "
			<div class=\"warn_text\">
			";

			foreach($GLOBALS['SCORE']['errors'] as $error)
			{
				echo $error;
			}

			echo "
			</div>
			";
		
		}


		// recursively removes specified dir and subcontents
		function remove_dir($dir)
		{

			if (file_exists($dir)) {
			
				$d=dir($dir);
				while (false !== ($entry = $d->read()) ) {
					if( $entry != "." && $entry != ".." ) {
						if ( is_dir($dir."/".$entry) ){
							$dirs[] = $entry;
						} else {
							$files[] = $entry;
						}
					}
				}

				$d->close();
			
				if(isset($files))
				{
					for($i=0; $i < sizeof($files); $i++){
						$fileresult=unlink($dir."/".$files[$i]);
					}
				}

				if(isset($dirs))
				{
					for($i=0; $i < sizeof($dirs); $i++){
						remove_dir($dir."/".$dirs[$i]);
					}
				}

				$dirresult=rmdir($dir);
			}
		}


		function isChecked($var_to_check)
			{
				if(strtolower($var_to_check) == 'on')
					echo ' CHECKED';
			}

		function inputValue($var)
			{
				if (isset($var))
					echo $var;
			}


		function set_score_prefs()
			{
				if(!	isset($GLOBALS['SCORE']) )
						$GLOBALS['SCORE'] = array();

				$r = dbq('SELECT * FROM score_sys');
				$GLOBALS['SCORE'] = array_merge($GLOBALS['SCORE'],$r['0']);
				$GLOBALS['SCORE']['SC4VERSIONS'] = unserialize($GLOBALS['SCORE']['SC4VERSIONS']);
			}


		function score_maintenance()
			{

				// determine if time interval since last maintenance exceeds maintenance limit
				if ( (time() - strtotime($GLOBALS['SCORE']['last_maintenance'])) > 14400) // 4 hours
				{
					// expires stale accounts that have never
					//	logged in after registering
					sm_expire_stale_unregistered();

					// do system wide maintenance
					if ( $GLOBALS['SCORE']['idle_account_limit'] > 0 )
						sm_expire_stale_accounts();

					// do region specific maintenance
					$Regions = dbq("SELECT name, id, checkout_timelimit FROM regions");
					foreach($Regions as $region)
					{
						$Cities = dbq("SELECT m.login as mayor_name, m.id as mayor_id, c.name, c.id, c.modified, c.mayor_id, c.checkout FROM cities c, users m WHERE c.region_id='{$region['id']}' AND c.checkout IS NOT NULL AND c.mayor_id=m.id");

						if ( $region['checkout_timelimit'] > 0 )
							sm_expire_cities($region, $Cities);

					/* abandoned due to time constraints
						if ( $region['idle_city_limit'] > 0 )
							sm_expire_idle_cities($region, $Cities);
					*/
					}

					// update 'last_maintenance'
					//dbq('UPDATE score_sys SET last_maintenance=now()');
				}
			}


/* abandoned due to time constraints
		// looks for cities that have been checked out
		//	but not updated for more than X days; send
		// email notifications/messages and unasigns.
		function sm_expire_idle_cities(&$region, &$Cities)
		{
			$day = 86400;

			// look for idle flagged cities

			foreach($Cities as $city)
			{
				// if last modified in seconds is < now + idle_city_limit_warn
				$modified = strtotime($city['modified']);
				$now = time();
				$limit = $region['idle_city_limit_warn'] * $day;

				if ( ($modified + $limit) < $now )
					decho("idle city limit warning: ".($modified + $limit)." < $now ");
			}
		}
*/


		// looks for cities past their checkout
		//	time-limit and resigns them.
		function sm_expire_cities(&$region, &$Cities)
		{
			$day = 86400;
			foreach($Cities as $city)	
			{
				if ( ( strtotime($city['checkout']) + ($region['checkout_timelimit']*$day)) < time() )
				{
					// resign this mayor
					dbq("UPDATE cities SET mayor_id=NULL WHERE id='{$city['id']}'");
					add_message('user', $city['mayor_id'], "Your term as mayor of {$city['name']} in {$region['name']} has expired.");
					add_city_log($city['id'], "Mayor {$city['mayor_name']} Resigns. (term expired)");
				}
			}
		}


		// looks for accounts which haven't been
		//	used in X months and expires them
		function sm_expire_stale_accounts()
		{
			// we'll be using this often
			$day = 86400;

			//	look for flagged stale accounts
			$idleusers = dbq("SELECT id, flagged_idle FROM users WHERE flagged_idle IS  NOT  NULL AND `privileges` =0");


			foreach($idleusers as $user)
			{
				// if time since flagged_idle and now exceeds idle_account_limit_warn
				$flagged_idle = strtotime($user['flagged_idle']);
				$limit = $day*$GLOBALS['SCORE']['idle_account_limit'];

				if ( time() > ($flagged_idle+$limit) )
				{
					// disasociate user from cities, messages & requests
					dbq("UPDATE cities SET mayor_id=NULL WHERE mayor_id='{$user['id']}'");
					dbq("UPDATE cities SET requested_mayor_id=NULL WHERE requested_mayor_id='{$user['id']}'");
					dbq("DELETE FROM messages WHERE relation_id='{$user['id']}'");

					// remove user
					dbq("DELETE FROM users WHERE id='{$user['id']}'");
				}
			}
			
			// flag any acitvated accounts as 'newly-stale' - as they say
			// user's last login was more than idle_account_limit_warn (days) ago
			$when = ( time() - ($day*$GLOBALS['SCORE']['idle_account_limit_warn']) );
			$when = date('Y-m-d', $when); //0000-00-00 00:00:00

			// we'll need the email addresses of these users to notify them that their accounts will expire soon
			$notify_users = dbq("SELECT email FROM users WHERE `lastlogin` < '{$when}' AND `flagged_idle` IS NULL AND `privileges` =0");

			$headers = array('From' => $GLOBALS['SCORE']['ADMIN_EMAIL'],'Subject' => 'SCORE: Account Expiration');

			foreach($notify_users as $user)
			{
				$body ="
This email is an automatic reminder that you have an active SCORE account at:
http://{$_SERVER['SERVER_NAME']}{$_SERVER['PHP_SELF']}

However the account will expire in {$GLOBALS['SCORE']['idle_account_limit']} days if you do not show renewed activity.

Thank you.
";			

				$r = score_mail($user['email'], $body, $headers);
			}

			$sql = "UPDATE users SET `flagged_idle`=now() WHERE `lastlogin` < '{$when}' AND `flagged_idle` IS NULL AND `privileges` =0";
			dbq($sql);
		}

		// remove any stale accounts not activated - hard coded to 10 days
		function sm_expire_stale_unregistered()
		{
			$day = 86400;
			$when = ( time() - ($day*10) );
			$when = date('Y-m-d', $when); //0000-00-00 00:00:00
			dbq("DELETE FROM users WHERE created < '{$when}' AND lastlogin IS NULL AND `privileges` =0");
		}


		function html_adminLink()
			{
				if ( $_SESSION['score_user']->data['privileges'] > 50 )
				{
					echo "
					|
					<a href=\"".basename($_SERVER['PHP_SELF'])."?score_lib=admin\">Admin</a>";
				}
			}



		function add_message($type, $relation_id, $text)
		{
			dbq("INSERT INTO messages (type, relation_id, text, created) VALUES ('$type', '$relation_id', '$text', now());");
		}

		function add_city_log($city_id, $text)
		{
			dbq("INSERT INTO city_log (city_id, text, created) VALUES ('$city_id','$text', now())");
		}
		
		
	/* handles all mail sending */	
	function score_mail( $to, $body, $head)
		{
			if (! @require_once('Mail.php'))
			{
				$GLOBALS['SCORE']['errors'][] = "Could not include PEAR::Mail.php - Pear installed? Pear's Mail Class installed?";
				return false;
			} 	
			
			if ( strlen(trim($GLOBALS['SCORE']['SMTP_HOST'])) ) // we got a specified smtp host
			{
				$auth_flag = (strlen(trim($GLOBALS['SCORE']['SMTP_USER']))) ? true : false;
				$mailer_obj =& Mail::factory('smtp', array('host' => $GLOBALS['SCORE']['SMTP_HOST'], 'port' => '25', 'auth' => $auth_flag, 'username' => $GLOBALS['SCORE']['SMTP_USER'], 'password' => $GLOBALS['SCORE']['SMTP_PASS']));
			} else {
				$mailer_obj =& Mail::factory('mail', "-f{$GLOBALS['SCORE']['ADMIN_EMAIL']}");
			}

			$r = $mailer_obj->send($to, $head, $body);
			
			if (PEAR::isError($r))
			{
				decho("problem sending the email - result:" . print_array($r) );
				$GLOBALS['SCORE']['errors'][] = 'There was a problem sending mail: ' . $r->getMessage();
				return false;
			}
			
			return true;
		}		
	?>