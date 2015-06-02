<?php
	/*
		SimCity Online Region Exchange(SCORE)

		region function library
	*/

	// check for request to change _SESSION score_region_id
	if (	isset($_REQUEST['score_region_id']) && is_numeric($_REQUEST['score_region_id'])	)
		$_SESSION['score_region_id'] = $_REQUEST['score_region_id'];

	// if this library is being called then there ought to be a region id available to load a region object
	if ( isset($_SESSION['score_region_id']) )
		$GLOBALS['SCORE']['scRegion'] = new scRegion($_SESSION['score_region_id']);



	function html_show_region_detail_image()
		{

			$scRegion = &$GLOBALS['SCORE']['scRegion'];
			if( ! isset($scRegion->data['Cities']) )
				$scRegion->getCityList();

			echo "
				<map name=\"citymap\" id=\"citymap\">";
			foreach($scRegion->data['Cities'] as $city)
			{
				echo "
				" . html_generate_Imap($city,$scRegion->data['imagemap'][$city['id']]);
			}
			echo "
				</map>";
			echo "
				<img src=\"{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$GLOBALS['SCORE']['scRegion']->data['name']}/{$GLOBALS['SCORE']['scRegion']->data['name']}.png\" usemap=\"#citymap\" border=\"none\">";
		}



	function Upload ()
		{	

			set_time_limit ( 120 );

			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];
			if( ! isset($scRegion->data['Cities']) )
				$scRegion->getCityList();

			// valid cityid who user is mayor of?
			if(! isset($_REQUEST['cityid']) || ! isset($scRegion->data['Cities'][$_REQUEST['cityid']]) )
			{
				$GLOBALS['SCORE']['errors'][] = "Could not load file \"{$_FILES['score_file']['name']}\" due to invalid cityid.";
				unlink("temp/{$_FILES['score_file']['name']}");
				return false;
			}

			//	confirm this user is in fact the mayor of this city
			if($scRegion->data['Cities'][$_REQUEST['cityid']]['mayor_id'] !== $_SESSION['score_user']->data['id'])
			{
				$GLOBALS['SCORE']['errors'][] = "Could not load file \"{$_FILES['score_file']['name']}\". You don't appear to be the mayor.";
				unlink("temp/{$_FILES['score_file']['name']}");
				return false;
			}
			

			// did they upload any1pxg?
			if ( ! is_array($_FILES['score_file']) )
			{
				$GLOBALS['SCORE']['errors'][] = "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
				return false;
			}

			if (  $_FILES['score_file']['error'] == 1 )
			{
				$GLOBALS['SCORE']['errors'][] = "No file uploaded.";
				return false;
			}
			
			if (  $_FILES['score_file']['error'] == 3 )
			{
				$GLOBALS['SCORE']['errors'][] = "The uploaded file was only partially uploaded.";
				return false;
			}
			
			if (  $_FILES['score_file']['error'] == 6 )
			{
				$GLOBALS['SCORE']['errors'][] = "Missing a temporary folder.";
				return false;
			}
			
			if (  $_FILES['score_file']['error'] == 4 || $_FILES['score_file']['size'] < 1 )
			{
				$GLOBALS['SCORE']['errors'][] = "No file uploaded.";
				return false;
			}

			// ensure no monkeybusiness with specifying filename with ../../../blah.sc4
			$_FILES['score_file']['name'] = basename($_FILES['score_file']['name']);

			// didn't seem to be able to read the file in it's temp location - so here is a new temp location
			move_uploaded_file( $_FILES['score_file']['tmp_name'], "temp/{$_FILES['score_file']['name']}" );

			// handle if its a zip file and zip is turned on
			if ( substr($_FILES['score_file']['name'],-4)  == '.zip')
			{
				if ( strtolower($GLOBALS['SCORE']['USE_ZIP']) != 'on')
				{
					$GLOBALS['SCORE']['errors'][] = "File \"{$_FILES['score_file']['name']}\" is zip format but currently Zip support is off - please upload the city file unzipped.";
					unlink("temp/{$_FILES['score_file']['name']}");
					return false;
				}
				
				decho("temp/{$_FILES['score_file']['name']}");
				$zip = zip_open("temp/{$_FILES['score_file']['name']}");
				if( ! $zip )
				{
					$GLOBALS['SCORE']['errors'][] = "Could not open Zip file.";
					//unlink("temp/{$_FILES['score_file']['name']}");
					return false;
				}

				while($e = zip_read($zip))
					$zip_entries[] = $e;

				print_array($zip_entries);

				zip_close($zip);
				unlink("temp/{$_FILES['score_file']['name']}");
				exit;
			}
					


			// process data from file
			$scFile = new scSaveGameFile();
			$scFile->Load("temp/{$_FILES['score_file']['name']}");

			// check for proper Region dir
			if( ! $scFile->status)
			{
				$GLOBALS['SCORE']['errors'][] = "Could not load file \"{$_FILES['score_file']['name']}\" into scFile Object.";
				unlink("temp/{$_FILES['score_file']['name']}");
				return false;
			}

			$scFile->fullParse($_FILES['score_file']['name']);
			if( ! $scFile->status)
			{
				$GLOBALS['SCORE']['errors'][] = "Problem parsing file \"{$_FILES['score_file']['name']}\".";
				unlink("temp/{$_FILES['score_file']['name']}");
				return false;
			}

			//	check that this new file's location and size match the existing one - to ensure they're a match
			if(  
					$scFile->contents['sizeX'] != $scRegion->data['Cities'][$_REQUEST['cityid']]['sizeX']
					||
					$scFile->contents['sizeY'] != $scRegion->data['Cities'][$_REQUEST['cityid']]['sizeY']
			)
			{
				$GLOBALS['SCORE']['errors'][] = "The city \"{$_FILES['score_file']['name']}\" doesn't appear to match the City your attempting to update";
				unlink("temp/{$_FILES['score_file']['name']}");
				return false;
			}
			
			// checks city for valid last played location within a region
			if($scRegion->data['cityloc_check'] == 'on')
			{
				if(
						$scFile->contents['locX'] != $scRegion->data['Cities'][$_REQUEST['cityid']]['locX']
						||
						$scFile->contents['locY'] != $scRegion->data['Cities'][$_REQUEST['cityid']]['locY']
				)
				{
					$GLOBALS['SCORE']['errors'][] = "The city \"{$_FILES['score_file']['name']}\" doesn't appear to match the City your attempting to update";
					unlink("temp/{$_FILES['score_file']['name']}");
					return false;
				}
			}

			// process images
				$scImage= new scImg();
				$scImage->Load($scFile->contents['mainImg']);
				$scImage->Resize('66%', '66%');

				$scImage->setAlphaTrans($scImage->current, false, false, 0,0,0);
				$scImage->setAlphaTrans($scImage->original, false, true, 0,0,0);

				$scImage->writeImg("{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$GLOBALS['SCORE']['scRegion']->data['name']}/" . substr($_FILES['score_file']['name'],0,-4) . "_thumb.png");
				$scImage->writeImg("{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$GLOBALS['SCORE']['scRegion']->data['name']}/" . substr($_FILES['score_file']['name'],0,-4) . ".png", 'original');
			
			// check for proper Region dir
			if( ! is_dir("{$GLOBALS['SCORE']['SC4PATH']}/{$GLOBALS['SCORE']['scRegion']->data['name']}"))
			{
				$GLOBALS['SCORE']['errors'][] = "No directory to upload file \"{$_FILES['score_file']['name']}\" to region \"{$GLOBALS['SCORE']['scRegion']->data['name']}\".";
				unlink("temp/{$_FILES['score_file']['name']}");
				return false;
			}

			//	delete the existing city file
			if( ! @unlink("{$GLOBALS['SCORE']['SC4PATH']}/{$GLOBALS['SCORE']['scRegion']->data['name']}/{$scRegion->data['Cities'][$_REQUEST['cityid']]['name']}.sc4"))
			{
				$GLOBALS['SCORE']['errors'][] = "The city \"{$_FILES['score_file']['name']}\" in \"{$GLOBALS['SCORE']['scRegion']->data['name']}\" region wouldn't delete. Permissions problem?";
				unlink("temp/{$_FILES['score_file']['name']}");
				return false;
			}

			// copy the new one over
			rename( "temp/{$_FILES['score_file']['name']}", "{$GLOBALS['SCORE']['SC4PATH']}/{$GLOBALS['SCORE']['scRegion']->data['name']}/{$_FILES['score_file']['name']}" );

			// update the database
			$sql = "UPDATE `regions` SET modified = now() WHERE id='{$GLOBALS['SCORE']['scRegion']->data['id']}'";
			dbsql($sql);

			updateRegionTotals($GLOBALS['SCORE']['scRegion']->data['id']);

			$tpop = $scFile->contents['R'] + $scFile->contents['C'] + $scFile->contents['I'];
			$sql = "UPDATE `cities` SET modified = now(), R='{$scFile->contents['R']}',C='{$scFile->contents['C']}',I='{$scFile->contents['I']}', pop=R+C+I, money='{$scFile->contents['money']}', name='" . substr($_FILES['score_file']['name'],0,-4) . "' WHERE id='{$_REQUEST['cityid']}'";
			dbsql($sql);

			// regenerate the region image
			gen_region_images($GLOBALS['SCORE']['scRegion']->data);

			// give user some feedback
			$GLOBALS['SCORE']['errors'][] = "The city \"{$_FILES['score_file']['name']}\" was updated in \"{$GLOBALS['SCORE']['scRegion']->data['name']}\". Thank you for updating.";

			// update city history
			add_city_log($_REQUEST['cityid'], "City update was uploaded.");

	}


	function updateRegionTotals($rid)
		{
			$totals = dbq("SELECT	SUM(R) R,
											SUM(C) C,
											SUM(I) I,
											SUM(pop) pop,
											SUM(money) money 
								FROM cities WHERE region_id=$rid");

			dbq("UPDATE regions SET total_R={$totals[0]['R']},
											total_C={$totals[0]['C']},
											total_I={$totals[0]['I']},
											total_pop={$totals[0]['pop']},
											total_money={$totals[0]['money']}
										WHERE id=$rid
			");
		}


	function checkoutCity()
		{
			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];

			if( ! isset($scRegion->data['Cities']) )
				$scRegion->getCityList();

			if ( ! isset($_REQUEST['cityid']) || ! is_numeric( $_REQUEST['cityid']) )
				return false;

			// ensure not already checked out
			$city = &$scRegion->data['Cities'][$_REQUEST['cityid']];

			if ( is_int($city['mayor_id']) )
				return false;

			// was this player the previous mayor of this city
			if ( $_SESSION['score_user']->data['id'] == $city['last_mayor_id'] && $scRegion->data['checkout_timelimit'] > 0)
			{
				$GLOBALS['SCORE']['errors'][] = 'You can not take control of this city because you just played it.';
				return false;
			}

			// check if city points are turned on for this city, and if the user has enough available.
			if ( $scRegion->data['checkout_user_limit'] != '0' )
			{
				$uCP = $scRegion->usedCityPoints();
				if ( ($uCP+$city['sizeX']) > $scRegion->data['checkout_user_limit'])
				{
					$GLOBALS['SCORE']['errors'][] = 'Becoming Mayor of this city would exceed your City-Points quota.';
					return false;
				}
			}
		
			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];
			$sql = "UPDATE cities SET mayor_id = {$_SESSION['score_user']->data['id']}, checkout=now() WHERE id='{$_REQUEST['cityid']}'";
			dbsql($sql);

			// update scRegion Cities list to reflect our new mayor
			$scRegion->data['Cities'][$_REQUEST['cityid']]['mayor_id'] = $_SESSION['score_user']->data['id'];

			// nice feedback
			$GLOBALS['SCORE']['errors'][] = "You are now mayor for this city. Thank you for playing";

			add_city_log($_REQUEST['cityid'], "Mayor {$_SESSION['score_user']->data['login']} began a term.");
		}



	function resignCity()
		{
			if( ! isset($_REQUEST['cityid']) || ! is_numeric($_REQUEST['cityid']) )
				return false;

			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];

			if( ! isset($scRegion->data['Cities']) )
				$scRegion->getCityList();

			//	confirm this user is in fact the mayor of this city
			if($scRegion->data['Cities'][$_REQUEST['cityid']]['mayor_id'] !== $_SESSION['score_user']->data['id'])
				return false;

			$sql = "UPDATE cities SET mayor_id=NULL, last_mayor_id='{$_SESSION['score_user']->data['id']}', checkout=NULL WHERE id='{$_REQUEST['cityid']}'";
			dbsql($sql);

			// update the Cities array so html_ functions display current info
			$scRegion->data['Cities'][$_REQUEST['cityid']]['mayor_id'] = '';

			$GLOBALS['SCORE']['errors'][] = "You are no longer mayor for this city. Thank you for playing.";

			add_city_log($_REQUEST['cityid'], "Mayor {$_SESSION['score_user']->data['login']} resigned.");
		}



	function downloadCityZip()
		{
			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];
			if( ! isset($scRegion) )
				return false;

			if( ! strtolower($GLOBALS['SCORE']['USE_ZIP']) == 'on' )
				return false;

			if( ! isset($_REQUEST['id']) || ! is_numeric($_REQUEST['id']) )
				return false;

			$city = dbq("SELECT name, id FROM cities WHERE id = '{$_REQUEST['id']}'");
			if ( ! sizeof($city) === 1)
				return false;
			$city = $city[0];

			$file = "{$GLOBALS['SCORE']['SC4PATH']}/{$scRegion->data['name']}/{$city['name']}.sc4";
			if(! is_file($file) || ! is_readable ( $file ) )
			{
				$GLOBALS['SCORE']['errors'][] = 'Could not read/locate city file - contact the admin.';
			}

			$Cdat = file_get_contents($file);
			if ( ! strlen($Cdat) )
			{
				$GLOBALS['SCORE']['errors'][] = 'Zero length file. Could not read-in city file - contact the admin.?';
			}

			require('php_inc/zip_inc.php');
			$cityZip = new zipfile();
			$cityZip->addFile($Cdat, "{$city['name']}.sc4" );

			ob_clean();
			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=".str_replace(' ', '', $city['name']).".zip");
			header("Content-Length: ".strlen($cityZip->file()));
			header("Accept-Ranges: bytes");
			echo($cityZip->file());
		}

	
	function requestCity()
		{
			if ( ! isset($GLOBALS['SCORE']['sc_REQUEST']->values['ID']['cityid']) )
				return false;

			$cityid = $GLOBALS['SCORE']['sc_REQUEST']->values['ID']['cityid'];

			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];
			if( ! isset($scRegion->data['Cities']) )
				$scRegion->getCityList();

			$city = &$scRegion->data['Cities'][$cityid];


			// check if city points are turned on for this city, and if the user has enough available.
			if ( $scRegion->data['checkout_user_limit'] != '0' )
			{
				$uCP = $scRegion->usedCityPoints();
				if ( ($uCP+$city['sizeX']) > $scRegion->data['checkout_user_limit'])
				{
					$GLOBALS['SCORE']['errors'][] = 'Becoming Mayor of this city would exceed your City-Points quota.';
					return false;
				}
			}


			// ensure this city isn't already requested
			if ( $city['requested_mayor_id'] || $city['mayor_id'] )
			{
				$GLOBALS['SCORE']['errors'][] = 'This city has already been requested by someone else.';
				return false;				
			}


			//	request this city
			dbq("UPDATE cities SET requested_mayor_id='{$_SESSION['score_user']->data['id']}' WHERE id='{$cityid}'");

			// update the array so html_ functions display current info
			$city['requested_mayor_id'] = $_SESSION['score_user']->data['id'];

			// nice user feedback
			$GLOBALS['SCORE']['errors'][] = 'You have now placed a request for a term as mayor of this city.';
		}


	function cancelCityRequest()
		{
			if ( ! isset($GLOBALS['SCORE']['sc_REQUEST']->values['ID']['cityid']) )
				return false;

			$cityid = &$GLOBALS['SCORE']['sc_REQUEST']->values['ID']['cityid'];

			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];
			if( ! isset($scRegion->data['Cities']) )
				$scRegion->getCityList();

			$city = &$scRegion->data['Cities'][$cityid];

			// ensure people aren't cancelling requests for other people
			if ( $city['requested_mayor_id'] != $_SESSION['score_user']->data['id'] )
				return false;

			//	request this city
			dbq("UPDATE cities SET requested_mayor_id=NULL WHERE id='{$cityid}'");

			// update the array so html_ functions display current info
			$city['requested_mayor_id'] = '';
		}


	function html_show_user_stats()
		{
			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];

			if( ! isset($scRegion->data['Cities']) )
				$scRegion->getCityList();

			$php_self = basename($_SERVER['PHP_SELF']);

			echo "
			<div class=\"user_statusbar\">
		 		 <div class=\"column_header background_color2\">Mayor Info</div>
				 <div class=\"column_content\">
				";

			// show player's cities
			echo "<b>Your Cities</b>:<br>";

			if ( sizeof($scRegion->data['Cities']) )
			{
				$list = '';
				foreach($scRegion->data['Cities'] as $city)
				{
					if($city['mayor_id'] == $_SESSION['score_user']->data['id'])
					{
						$list .= "
						&nbsp;<a href=\"javascript:toggle_node({$city['id']},'cib');\" title=\"View City\">{$city['name']}</a><br>";
					} elseif ($city['requested_mayor_id'] == $_SESSION['score_user']->data['id']) {
						$list .= "
						&nbsp;<a href=\"javascript:toggle_node({$city['id']},'cib');\" title=\"View City\">{$city['name']}</a> (requested)<br>";
					}
				}
				echo $list;
			} else {
				echo "&nbsp;None<br>";
			}
				
		
			// show city-points
			if(  $scRegion->data['checkout_user_limit'] != '0' )
			{
				$uCP = $scRegion->usedCityPoints();
				$uCP = $scRegion->data['checkout_user_limit'] - $uCP;
				echo "<b>City-Points</b>: {$uCP}/{$scRegion->data['checkout_user_limit']}";
			}

			echo "
				</div>
			</div>";
		}



	function html_show_city_data()
		{
			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];
			if( ! isset($scRegion->data['Cities']) )
				$scRegion->getCityList();

			echo "<div id=\"cib\">
					  <div class=\"city_info_box\" id=\"city_info_default\" style=\"display:\" align=\"center\">
						 <div class=\"column_header background_color2\">City Info</div>
	 						 <div class=\"column_content\">
								Click the image to the right to select a city.
							</div>
					  </div>
					";

			$citySizes = array(1 => 'Small', 2 => 'Medium', 4=>'Large');

			// for each city spit out the html we need
			foreach($scRegion->data['Cities'] as $city)
			{



				// show link to sign on as mayor or resign as mayor
				if($city['mayor_id'])
				{
					$scMayor = new scMayor($city['mayor_id']);
					$mayor_html = "
										<a href=\"mailto:{$scMayor->data['email']}\">{$scMayor->data['login']}</a>";
					if($city['mayor_id'] == $_SESSION['score_user']->data['id'])
					$mayor_html .= "
										(<a href=\"".basename($_SERVER['PHP_SELF'])."?score_call=resignCity&cityid={$city['id']}\">Resign</a>)";
				}
					elseif ($city['requested_mayor_id'])
				{
					$request_mayor = dbq("SELECT * FROM users WHERE id='{$city['requested_mayor_id']}'");
					$mayor_html = "
										Requested by {$request_mayor[0]['login']}";
					if($city['requested_mayor_id'] == $_SESSION['score_user']->data['id'])
					$mayor_html .= "
										(<a href=\"".basename($_SERVER['PHP_SELF'])."?score_call=cancelCityRequest&sf[ID][cityid]={$city['id']}\" title=\"Cencel your request for this city\">Cancel</a>)";
				} 
					else
				{
					// not currently owned by a user if user has enough
					// city-points or city-points limit isn't in effect
					// determine if checkout is by authorization or not

					// tally up city-points for this person mayor
					$uCP = $scRegion->usedCityPoints();
					

					if ( $scRegion->data['checkout_user_limit'] != '0' &&  ($uCP+$city['sizeX']) > $scRegion->data['checkout_user_limit'] )
					{
						$mayor_html = "None";
						
					} else {
						if ( $scRegion->data['checkout_req_auth'] ==  'on')
						{
							$mayor_html = "
											<a href=\"".basename($_SERVER['PHP_SELF'])."?score_call=requestCity&sf[ID][cityid]={$city['id']}\" title=\"Request to begin your term as mayor of this city\"><i>Request Term</i></a>";
						} else {
							$mayor_html = "
											<a href=\"".basename($_SERVER['PHP_SELF'])."?score_call=checkoutCity&cityid={$city['id']}\" title=\"Begin your term as mayor of this city\">Begin Term</a>";
						}
					}
				}



				// are we making available zip download?

				
				if (strtolower($GLOBALS['SCORE']['USE_ZIP']) == 'on' )
					$zipahref = "
							(<a href=\"".$_SERVER['PHP_SELF']."?score_call=downloadCityZip&id={$city['id']}&noheader=true\" title=\"Download this city zipped\">Zipped</a>)";
				else
					$zipahref = '';

				$ahref= "
							<a href=\"{$GLOBALS['SCORE']['SC4PATH']}/{$scRegion->data['name']}/{$city['name']}.sc4\" title=\"Download this city\"><img src=\"download.png\" align=\"left\"></a><span class=\"title_text\">{$city['name']}</span>";


				// need an upload form?
				if($city['mayor_id'] == $_SESSION['score_user']->data['id'])
				{
					$form = "
							<p>
							<b>City Upload</b>:
							(.sc4 files only please)

							<form enctype=\"multipart/form-data\" action=\"".basename($_SERVER['PHP_SELF'])."\" method=\"post\">
							<input type=\"hidden\" name=\"cityid\" value=\"{$city['id']}\">
							<input type=\"file\" name=\"score_file\">
							<input type=\"submit\" name=\"score_call\" value=\"Upload\">
							</form>
					";
				} else {
					$form ='';
				}


				if ($scRegion->data['checkout_user_limit'] != 0)
					$city_point_cost="(City-Point Cost: {$city['sizeX']})";
				else
					$city_point_cost='';

				// output the stats
				echo "
						<div id=\"city_info_{$city['id']}\" class=\"city_info_box\" style=\"display:none\">
						 <div class=\"column_header background_color2\">City Info</div>
						 <div class=\"column_content\">
								$ahref
								(<a href=\"javascript:FullSize=window.open('".basename($_SERVER['PHP_SELF'])."?score_lib=popup&score_call=viewCityHistory&sf[ID][cityid]={$city['id']}&noheader=true', 'CityHistory', 'menubar=no,toolbar=no,resizable,height=500,width=300'), FullSize.focus()\" title=\"View City History\">City History</a>)
								<br>
								{$zipahref}
								<br>
								Current Mayor</b>: {$mayor_html}
								<p>

								<a href=\"javascript:FullSize=window.open('{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$scRegion->data['name']}/{$city['name']}.png', 'FullSize', 'menubar=no,toolbar=no,resizable,height=300,width=530'), FullSize.focus()\" title=\"View Full Size\">
									<img src=\"empty.gif\" loaded=\"false\" url=\"{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$scRegion->data['name']}/{$city['name']}_thumb.png\" border=\"none\" id=\"city_image_{$city['id']}\">
								</a>


								<p>
								- <b>Size</b>: {$citySizes[$city['sizeX']]} $city_point_cost
								<br>
								- <b>Created</b>: {$city['created']}
								<br>
								- <b>Updated</b>: {$city['modified']}
								<br>
								- <b>TotalPop</b>: ".number_format($city['pop'])."
								<br>
								&nbsp;&nbsp;
								<b>R</b>: ".number_format($city['R'])."
								<br>
								&nbsp;&nbsp;
								<b>C</b>: ".number_format($city['C'])."
								<br>
								&nbsp;&nbsp;
								<b>I</b>: ".number_format($city['I'])."
								<br>
								- <b>Total Funds</b>: $".number_format($city['money'])."
							$form
						</div>
					</div>
				";

			} //end for loop
		echo "</div>"; //end id=cib div
		} // end function



	function html_show_whois_playing_which_cities()
		{
			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];
			$inPlay = dbq("SELECT u.login, c.id, c.name FROM cities c, users u WHERE c.region_id={$scRegion->data['id']} AND c.mayor_id=u.id AND mayor_id IS NOT NULL ORDER BY c.mayor_id");
			
			if(sizeof($inPlay))
			{
				echo "
					<div class=\"whos_playing\">
		 				<div class=\"column_header background_color2\">Who's Playing?</div>
						<div class=\"column_content\" style=\"height:80px; overflow: auto\">
					";
				$i=0; $output = '';
				foreach($inPlay as $city)
				{
					// if mayor has more than one city?
					if(!isset($inPlay[$i-1]['login']) || $city['login'] != $inPlay[$i-1]['login'])
					{
						$output .= "
						{$city['login']} is playing: 
							
						
							<a href=\"javascript:toggle_node('{$city['id']}','cib')\">{$city['name']}</a>";

						// append break?
						if( ! isset($inPlay[$i+1]['login']) || $city['login'] != $inPlay[$i+1]['login'])
								$output .= "<br>";

					} elseif ($city['login'] == $inPlay[$i-1]['login']) {
						$output .= ", <a href=\"javascript:toggle_node('{$city['id']}','cib')\">{$city['name']}</a>";
						
						// append break?
						if( !isset($inPlay[$i+1]['login']) || $city['login'] != $inPlay[$i+1]['login'])
								$output .= "<br>";
					}

					$i++;
				}

				echo $output;

				echo	"
						</div>
					</div>
					";
			}
			//print_array($dbq);
		}


		function html_show_updated_cities()
		{
			if(! $_SESSION['score_user']->data['lastlastlogin'] )
				return false;

			$scRegion =& 	$GLOBALS['SCORE']['scRegion'];
			if( ! isset($scRegion->data['Cities']) )
				$scRegion->getCityList();
			
			$output = '';
			$count = 0;

			$output .= "
				<div id=\"updated_cities\">
					<div class=\"column_header background_color2\">Cities Updated Since Your Last Login</div>
					<div class=\"column_content\" style=\"height:40px; overflow: auto\">
				";
			foreach($scRegion->data['Cities'] as $id => $city)
			{
				// if the city isn't owned by the current user
				// 
				if( $city['mayor_id'] != $_SESSION['score_user']->data['id'] && strtotime($city['modified']) > strtotime($_SESSION['score_user']->data['lastlastlogin']) )
				{	
					$count++;
					$output .= "
						<a href=\"javascript:toggle_node('{$city['id']}','cib')\">{$city['name']}</a>, ";
				}
			}

			
			if($count)
			{ 
				$output = substr($output, 0, -2);
				$output .= "
					</div>
				</div>";
				echo $output;
			}
			
		}
?>