<?php
	
	/*
		SimCity Online Region Exchange(SCORE)

		main_inc.htm contains libarary of functions needed for the main page
	*/


	function html_show_all_regions ()
	{
		$regions = dbq('SELECT * FROM regions;');

		if ( !is_array($regions) )
				return false;

		echo "<br>";
		foreach($regions as $key=>$region)
		{
			echo "
			<div style=\"margin-top: 10px; width: 100%;\">

					<a href=\"" . basename($_SERVER['PHP_SELF']) . "?score_lib=region&score_region_id={$region['id']}\" title=\"Region Detail View\">
						<img src=\"{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$region['name']}/{$region['name']}_thumb.png\" align=\"right\" border=\"0px\">
					</a>

					
						<span class=\"title_text\">
							<a href=\"" . basename($_SERVER['PHP_SELF']) . "?score_lib=region&score_region_id={$region['id']}\">{$region['name']}</a>
						</span>
						<br>
						- <b>Created</b>: {$region['created']}
						<br>
						- <b>Updated</b>: {$region['modified']}
						<p>
						- <b>TotalPop</b>: ".number_format($region['total_pop'])."
						<br>
						&nbsp;&nbsp;
						<b>R</b>: ".number_format($region['total_R'])."
						<br>
						&nbsp;&nbsp;
						<b>C</b>: ".number_format($region['total_C'])."
						<br>
						&nbsp;&nbsp;
						<b>I</b>: ".number_format($region['total_I'])."
						<p>
						- <b>Total Funds</b>: $".number_format($region['total_money'])."
						<p>
					

					<div>
					".nl2br($region['description'])."
					</div>
					

				";
			
			// are we making available zip archives of each region?
			echo "<div style=\"margin-top: 8px; border: 1px dashed black; background: white; width: 98%; height: 32px; padding: 4px\">";
			if (strtolower($GLOBALS['SCORE']['USE_ZIP']) == 'on' && strtolower($region['complete_region_dl']) == 'on')
				echo "
						<a href=\"".$_SERVER['PHP_SELF']."?score_call=downloadRegionZip&id={$region['id']}&noheader=true\" title=\"Download Region Zipped\"><img src=\"download.png\" align=\"left\" style=\"border: none; padding-left: 10px;\"></a>";
		
			// do we have config.bmp and terrain maps for this region?
			if (is_file("{$GLOBALS['SCORE']['SC4PATH']}/{$region['name']}/config.bmp"))
				echo "
						<a href=\"{$GLOBALS['SCORE']['SC4PATH']}/{$region['name']}/config.bmp\" title=\"Download Region config.bmp\"><img src=\"config.png\" align=\"left\" style=\"border: none; padding-left: 10px;\"></a>";
			
			if(is_file("{$GLOBALS['SCORE']['SC4PATH']}/{$region['name']}/terrain.jpg"))
				echo "
						<a href=\"{$GLOBALS['SCORE']['SC4PATH']}/{$region['name']}/terrain.jpg\" title=\"Download Terrain Map\"><img src=\"terrain.png\" align=\"left\" style=\"border: none; padding-left: 10px;\"></a>";

			if(is_file("{$GLOBALS['SCORE']['SC4PATH']}/{$region['name']}/terrain.png"))
				echo "
						<a href=\"{$GLOBALS['SCORE']['SC4PATH']}/{$region['name']}/terrain.png\" title=\"Download Terrain Map\"><img src=\"terrain.png\" align=\"left\" style=\"border: none; padding-left: 10px;\"></a>";

			if(is_file("{$GLOBALS['SCORE']['SC4PATH']}/{$region['name']}/terrain.bmp"))
				echo "
						<a href=\"{$GLOBALS['SCORE']['SC4PATH']}/{$region['name']}/terrain.bmp\" title=\"Download Terrain Map\"><img src=\"terrain.png\" align=\"left\" style=\"border: none; padding-left: 10px;\"></a>";

			// close out the div
				echo "
				</div><!-- image div -->
			</div><!-- individual region div -->
			<div style=\"clear:both\">
			";
		}
	}


	function clearUserMessages()
	{
		if( ! is_numeric($_SESSION['score_user']->data['id']) )
			return false;

		dbq("DELETE FROM messages WHERE type='user' AND relation_id='{$_SESSION['score_user']->data['id']}'");
	}

	
	function html_show_your_messages()
		{
			if ( ! is_numeric($_SESSION['score_user']->data['id']) )
				return false;

			$messages = dbq("SELECT * FROM messages WHERE type='user' AND relation_id='{$_SESSION['score_user']->data['id']}' ORDER BY created");
			
			if(sizeof($messages) < 1)
			{
				echo "<p>You have no messages.<p>";
				return false;
			}

			foreach($messages as $m)
			{
				echo 
					"	<div>
						 - {$m['text']} -- <i>{$m['created']}</i>
						</div>
				";
			}

			echo 
				"
					<p>
					<div style=\"text-align: right\">
						<a href=\"".basename($_SERVER['PHP_SELF'])."?score_call=clearUserMessages\" onclick=\"jsconfirm('Delete all your messages?')\">Delete Messages</a>
					</div>

			";
		}



	function html_show_your_cities ()
		{
			$sql = "SELECT r.id rid,r.checkout_timelimit, r.name rname, c.id cid, c.name cname, c.pop, c.R, c.C, c.I, c.created, c.modified, c.mayor_id, c.requested_mayor_id, c.checkout FROM cities c, regions r WHERE (c.mayor_id='{$_SESSION['score_user']->data['id']}' OR c.requested_mayor_id='{$_SESSION['score_user']->data['id']}') AND c.region_id=r.id ORDER BY r.name";
			$myCities = dbq($sql);

			if(sizeof($myCities) < 1)
			{
				echo "<p>You are not currently playing a city.<p>";
				return false;
			}



			// prep vars for html
			$php_self = basename($_SERVER['PHP_SELF']);


			foreach($myCities as $city)
			{
				if ( $city['mayor_id'] )
				{
					$checkout = strtotime($city['checkout']);
					$now		 = time();
					$days_as_mayor = ceil(($now - $checkout)/60/60/24);

					// show optional time remaining for regions with checkout timelimits
					if ($city['checkout_timelimit'] > 0)
					{
						$remainder = ($city['checkout_timelimit'] - $days_as_mayor);
						$checkout_limit_html = " You have $remainder days left."; 
					} else {
						$checkout_limit_html = '';
					}

					echo "
						<a href=\"{$php_self}?score_lib=region&score_region_id={$city['rid']}&cityid={$city['cid']}\" title=\"View City\">{$city['cname']}</a> of {$city['rname']}
						<br>
						 - You've been mayor for {$days_as_mayor} days. $checkout_limit_html
						 <p>
					";

				} elseif ($city['requested_mayor_id']) {
					echo "
					<a href=\"{$php_self}?score_lib=region&score_region_id={$city['rid']}&cityid={$city['cid']}\" title=\"View City\">{$city['cname']}</a> of {$city['rname']}
					<br>
					- You have requested a term as mayor of this city.
					<p>
					";
				}
			}
		}


	function downloadRegionZip()
		{
			if( ! strtolower($GLOBALS['SCORE']['USE_ZIP']) == 'on' )
				return false;

			if( ! isset($_REQUEST['id']) || ! is_numeric($_REQUEST['id']))
				return false;

			$region = new scRegion($_REQUEST['id']);
			if ( ! $region->status)
			{
				return false;
			}

			if (! strtolower($region->data['complete_region_dl']) == 'on')
				return false;
			
			require('php_inc/zip_inc.php');

			$rDir = new scDir();
			$rDir->Load("{$GLOBALS['SCORE']['SC4PATH']}/{$region->data['name']}");
			
			if(! $rDir->status)
				return false;

			$regionZip = new zipfile();

			foreach($rDir->getFileContents() as $file)
			{
				$regionZip->addFile(file_get_contents("{$GLOBALS['SCORE']['SC4PATH']}/{$region->data['name']}/$file"), "{$region->data['name']}/{$file}");
			}

			header("Content-type: application/zip"); 
			header("Content-Disposition: attachment; filename=".str_replace(' ', '_', $region->data['name']).".zip");
			header("Content-Length: ".strlen($regionZip->file()));
			echo($regionZip->file());
		}



		function html_show_admins_cityrequests()
		{
			if(! $_SESSION['score_user']->data['privileges'] > 100)
				return false;
			
			$city_requests = dbq("SELECT c.id cityid, c.name cityname, r.id regionid, r.name regionname, u.id userid, u.login FROM cities c, regions r, users u WHERE c.requested_mayor_id IS NOT NULL AND c.region_id=r.id AND c.requested_mayor_id=u.id");

			if (empty($city_requests))
				return false;


		echo"
			<form action=\"".basename($_SERVER['PHP_SELF'])."\" method=\"post\">
			<div class=\"column_header background_color2\">
				Pending City Requests
			</div>
			<br>
			<div class=\"column_content\">
			
				<div class=\"ldiv\" style=\"width:70%\">
					&nbsp;
				</div>
				<div class=\"rdiv\">
					Yes | No
				</div>				
			";
			
			foreach($city_requests as $r)
			{
				echo 
				"
					<div class=\"ldiv\" style=\"width:70%\">
						<b>{$r['login']}</b> requests <a href=\"http://localhost:81/~andykoch/score/score.php?score_lib=region&score_region_id=23&cityid={$r['cityid']}\">{$r['cityname']}</a> in <a href=\"http://localhost:81/~andykoch/score/score.php?score_lib=region&score_region_id=23\">{$r['regionname']}</a>.
					</div>
					<div class=\"rdiv\">
						<input type=\"radio\" name=\"sf[TEXT][auth_city][{$r['cityid']}]\" value=\"yes\">
						<input type=\"radio\" name=\"sf[TEXT][auth_city][{$r['cityid']}]\" value=\"no\">
					</div>
					<br>				
				";
			}

			echo"
				<br>
		
				<div style=\"text-align:right\">
					<input type=\"submit\" name=\"score_call\" value=\"Answer Requests\">
				</div>
			</div>
			</form>		
			";
		}

		function AnswerRequests()
		{
			if(! $_SESSION['score_user']->data['privileges'] > 100)
				return false;

			$SC =& $GLOBALS['SCORE']['sc_REQUEST'];

			if( ! isset($SC->values['TEXT']['auth_city']) || ! is_array($SC->values['TEXT']['auth_city']) )
				return false;

			foreach( $SC->values['TEXT']['auth_city'] as $cityid => $response)
				handleRequestResponse($cityid, $response);

		}


		function handleRequestResponse($cityid, $response)
		{
				// lookup this info from the database
				$dbdata = dbq("SELECT c.id cityid, c.name cityname, c.requested_mayor_id, r.id regionid, r.name regionname FROM cities c, regions r WHERE c.region_id=r.id AND c.id='$cityid'");

				if(empty($dbdata))
					return false;

				// set request flag to null on city, set mayor id to new mayor id if 'yes' given
				// send user a message informing them of if they were approved or denied
				if($response == 'yes')
				{
					dbq("UPDATE cities SET requested_mayor_id=NULL, mayor_id='{$dbdata[0]['requested_mayor_id']}',checkout=now() WHERE id='{$cityid}' LIMIT 1");
					add_message('user', $dbdata[0]['requested_mayor_id'], "Your request for term as mayor of {$dbdata[0]['cityname']} in {$dbdata[0]['regionname']} has been <b>granted</b>.");
				} elseif($response == 'no') {
					dbq("UPDATE cities SET requested_mayor_id=NULL WHERE id='{$cityid}' LIMIT 1");
					add_message('user', $dbdata[0]['requested_mayor_id'], "Your request for term as mayor of {$dbdata[0]['cityname']} in {$dbdata[0]['regionname']} has been <b>denied</b>.");
				}
		}
?>