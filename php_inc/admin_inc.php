<?php

function zipLibStatus()
{
 if( function_exists ('gzcompress') )
	echo "Available";
 else
	echo "Not Installed";
}


function UpdateRegionSettings()
{
	if(! isset($_REQUEST['region_options']))
		return false;

	// element array
	/*
	$ea[] = 'complete_region_dl';
	$ea[] = 'checkout_req_auth';
	$ea[] = 'checkout_user_limit';
	$ea[] = 'checkout_timelimit';
	$ea[] = 'vrestrict';
	*/

	foreach ($_REQUEST['region_options'] as $region_id => $region)
	{
		$update = '';
		foreach($region as $field => $value)
			$update .= "$field='".addslashes($value)."',";
		$update = substr($update,0,-1);
		$sql = "UPDATE regions SET $update WHERE id='$region_id'";
		dbsql ($sql);
	}
}


function UpdateSCORESettings()
{
	if(! isset($_REQUEST['score_option']))
		return false;

	$element_array[] = 'PUBLIC_REG';
	$element_array[] = 'USE_ZIP';
	$element_array[] = 'SC4IMG_PATH';
	$element_array[] = 'SC4PATH';
	$element_array[] = 'SC4REGION_IMG_WIDTH';
	$element_array[] = 'ADMIN_NAME';
	$element_array[] = 'ADMIN_EMAIL';
	$element_array[] = 'idle_account_limit_warn';
	$element_array[] = 'idle_account_limit';
	
	$element_array[] = 'SMTP_HOST';
	$element_array[] = 'SMTP_USER';
	$element_array[] = 'SMTP_PASS';

	$update = '';
	foreach ($element_array as $el)
	{
		$update .= "$el='{$_REQUEST['score_option'][$el]}',";
	}
	
	$update = substr($update,0,-1);
	$sql = "UPDATE score_sys SET $update WHERE name_id='score_sys'";
	dbsql ($sql);

	set_score_prefs();
}


function html_get_admin_region_list()
{
	$sql="SELECT id,name,complete_region_dl,checkout_req_auth,checkout_user_limit,checkout_timelimit,vrestrict,description,cityloc_check FROM regions";
	$r = dbq($sql);

	if(! sizeof($r))
		return false;

	echo "
			<form action=\"".basename($_SERVER['PHP_SELF'])."\" method=\"post\">";

	$basename = $_SERVER['PHP_SELF'];

	foreach($r as $region)
	{
		// setup variables

		$vrestrict_options = '';
		foreach($GLOBALS['SCORE']['SC4VERSIONS']  as $string => $val)
		{
			if ($region['vrestrict'] == $val)
				$selected = 'SELECTED'; else $selected = '';
			$vrestrict_options .= "<option value=\"$val\" $selected>$string</option>\n";
		}

		if($region['complete_region_dl'] == 'on')
			$checked['complete_region_dl'] = ' CHECKED'; else $checked['complete_region_dl'] ='';

		if($region['checkout_req_auth'] == 'on')
			$checked['checkout_req_auth'] = ' CHECKED'; else $checked['checkout_req_auth'] ='';

		if($region['cityloc_check'] == 'on')
			$checked['cityloc_check'] = ' CHECKED'; else $checked['cityloc_check'] ='';

		echo "
		<br>
		<span class=\"title_text\">{$region['name']}</span> - 
		(<a href=\"$basename?score_call=deleteRegion&id={$region['id']}\" onclick=\"return js_confirm('This will remove this region from SCORE - are you sure?')\">Delete</a>)

		<div style=\"margin-left: 8px\">
			<div class=\"ldiv\">
				Full Region Downloads
			</div>
			<div class=\"rdiv\">
				<input type=\"hidden\" name=\"region_options[{$region['id']}][complete_region_dl]\" value=\"off\">
				<input type=\"checkbox\" name=\"region_options[{$region['id']}][complete_region_dl]\"{$checked['complete_region_dl']}>
			</div>

			<div class=\"ldiv\">
				City Checkout Authorization
			</div>
			<div class=\"rdiv\">
				<input type=\"hidden\" name=\"region_options[{$region['id']}][checkout_req_auth]\" value=\"off\">
				<input type=\"checkbox\" name=\"region_options[{$region['id']}][checkout_req_auth]\"{$checked['checkout_req_auth']}>
			</div>

			<div class=\"ldiv\">
				City Location Validation on Updates<br>
			</div>
			<div class=\"rdiv\">
				<input type=\"hidden\" name=\"region_options[{$region['id']}][cityloc_check]\" value=\"off\">
				<input type=\"checkbox\" name=\"region_options[{$region['id']}][cityloc_check]\"{$checked['cityloc_check']}>
			</div>

			<div class=\"ldiv\">
				User Checkout City Limit (Zero Unlimited)
			</div>
			<div class=\"rdiv\">
				<input type=\"text\" name=\"region_options[{$region['id']}][checkout_user_limit]\" value=\"{$region['checkout_user_limit']}\" size=\"3\">
			</div>

			<div class=\"ldiv\">
				Time Limited Checkouts (in Days; Zero Unlimited)
			</div>
			<div class=\"rdiv\">
				<input type=\"text\" name=\"region_options[{$region['id']}][checkout_timelimit]\" value=\"{$region['checkout_timelimit']}\" size=\"3\">
			</div>

			<div>
				General Description
			</div>
			<div>
				<textarea name=\"region_options[{$region['id']}][description]\" style=\"width:95%; height:100px\">{$region['description']}</textarea>
			</div>

			 
			<!--
			<div class=\"ldiv\">
				Version Limits
			</div>
			<div class=\"rdiv\">
				<select name=\"region_options[{$region['id']}][vrestrict]\">
					{$vrestrict_options}
				</select>
			</div>
			-->
		</div>
		";
	}

	echo "	<br>
				<div style=\"text-align: right\">
					<input type=\"submit\" name=\"score_call\" value=\"Update Region Settings\">
				</div>
			</form>
				";
}


function deleteRegion ()
	{
		if(! isset($_REQUEST['id']) || ! is_numeric($_REQUEST['id']) )
			return false;

		$region = new scRegion($_REQUEST['id']);
		if( ! $region->status )
		{
			decho("status is bad:".$region->status);
			return false;
		}

		// delete region folder from SC4IMG_PATH
		remove_dir("{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$region->data['name']}");

		// confirm the delete worked
		if ( is_dir("{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$region->data['name']}"))
		{
			$GLOBALS['SCORE']['errors'][] = "{$region->data['name']} directory failed to delete. Permissions problem?";
			return false;
		}

		// delete cities from database
		$sql = "DELETE FROM cities WHERE region_id='{$region->data['id']}'";
		dbq($sql);

		// delete region from database
		$sql = "DELETE FROM regions WHERE id='{$region->data['id']}'";
		dbq($sql);

		unset($region);
	}

function html_show_perms_state()
	{
		$gword = 'Yes';
		$bword = 'No';
		
		$gcolor = "<font color=\"green\">{$gword}</font>";
		$bcolor = "<font color=\"red\">{$bword}</font>";
		
		$sc4path['r'] = (is_readable($GLOBALS['SCORE']['SC4PATH'])) ? $gcolor : $bcolor;
		$sc4path['w'] = (is_writable($GLOBALS['SCORE']['SC4PATH'])) ? $gcolor : $bcolor;
	
		$sc4img_path['r'] = (is_readable($GLOBALS['SCORE']['SC4IMG_PATH'])) ? $gcolor : $bcolor;
		$sc4img_path['w'] = (is_writable($GLOBALS['SCORE']['SC4IMG_PATH'])) ? $gcolor : $bcolor;
		
		$gd_lib = (extension_loaded('gd')) ? $gcolor : $bcolor;
		$gdpng  = (function_exists('imagecreatefrompng')) ? $gcolor : $bcolor;
		
		$pearMail = (@include('Mail.php')) ? $gcolor : $bcolor;
		$pearMailAuth = (@include('Auth/SASL.php')) ? $gcolor : $bcolor;
		
		echo "
		<div>
		 <span class=\"title_text\">Permissions Status</span>
		 <br>
		 <i>{$GLOBALS['SCORE']['SC4PATH']}</i> is Readable {$sc4path['r']} / Writable {$sc4path['w']}
		 <br>
		 <i>{$GLOBALS['SCORE']['SC4IMG_PATH']}</i> is Readable {$sc4img_path['r']} / Writable {$sc4img_path['w']}
		 <p>
		 
		 <span class=\"title_text\">Requirements</span>
		 <br>
		 <i>GD Extensions</i>  available? {$gd_lib}
		 <br>
		 <i>GD-PNG Extensions</i>  available? {$gdpng}
		 <br>
		 <i>PEAR Mail Class</i>  available? {$pearMail}
		 <p>
		 
		 <span class=\"title_text\">Optional</span>
		 <br>
		 <i>PEAR SMTP Authentication</i>  available? {$pearMailAuth}		 
		</div>
		";
	}


function html_import_regions()
	{
		// is specified folders for regions/images useable by score (read/write)
		if ( ! is_writable($GLOBALS['SCORE']['SC4PATH']) || ! is_writable($GLOBALS['SCORE']['SC4IMG_PATH']) )
		{
			echo "<span class=\"warn_text\">Region and Images dir do not have correct permission or do not exist - please ensure that php can read and write to theses directories and their contents</span>";
			return false;
		}
		
		echo "
				<form action=\"" . basename($_SERVER['PHP_SELF']) ."\" method=\"post\" enctype=\"multipart/form-data\">
				<p>
					Import New Region
					<select name=\"importRegion\">
						";
		html_options_importableRegions();
		echo 				
			"
					</select>
					<input type=\"submit\" name=\"score_call\" value=\"Import Region\">
				</form>
		";
	}


function html_options_importableRegions ()
	{
		$dir = new scDir();

		// get region folder names from SC4PATH
		$dir->Load($GLOBALS['SCORE']['SC4PATH']);
		$all_regions = $dir->getDirContents();

		// get list of already imported regions
		$imported_regions = dbq("SELECT name FROM regions");
		
		foreach($imported_regions as $region)
			$ir[] = $region['name'];

		echo "
					<option></option>";

		foreach($all_regions as $region)
		{
			if ( is_readable("{$GLOBALS['SCORE']['SC4PATH']}/{$region}") && ! in_array($region, $ir) )
			{
				echo "
					<option value=\"{$region}\">{$region}</option>";
			} elseif ( ! is_readable("{$GLOBALS['SCORE']['SC4PATH']}/{$region}") && ! in_array($region, $ir) ) {
				echo "
					<option value=\"NOREAD\">{$region} (No Read Permissions)</option>";
			}	
		}

		unset($dir);
	}



function ImportRegion()
	{
		if ( ! isset($_REQUEST['importRegion']) )
			return false;
			
		if ( $_REQUEST['importRegion'] == 'NOREAD' )
		{
			$GLOBALS['SCORE']['errors'][] = "You can not import this region until you fix the read permissions. See the 'setup' documentation for more information.";
			return false;
		}
		
		if ( ! extension_loaded('gd') || ! function_exists('imagecreatefrompng') )
		{
			$GLOBALS['SCORE']['errors'][] = "The version of PHP you are using doesn't have GD Extensions with PNG support. See the 'setup' documentation for more information.";
			return false;		
		}

		$import_region = basename($_REQUEST['importRegion']);

		if ( ! is_dir("{$GLOBALS['SCORE']['SC4PATH']}/$import_region") )
			return false;

		if ( is_dir("{$GLOBALS['SCORE']['SC4IMG_PATH']}/$import_region") )
			return false;

		importSCRegion($import_region);
	}


function importSCRegion($importRegion)
	{	
		// class handles rolling back any changes on any error
		//$sc_importErrorHandler = new sc_importErrorHandler($importRegion);

		// set up variables
		$total_region['R'] = $total_region['C'] = $total_region['I'] = $total_region['money'] = 0;
		$path = $GLOBALS['SCORE']['SC4PATH'] . '/' . $importRegion . '/';
		$img_path = $GLOBALS['SCORE']['SC4IMG_PATH']. '/' . $importRegion . '/';

		// set up objects
		$Cfiles	=	new scDir();
		$scFile	=	new scSaveGameFile(); 
		$scImg	=	new scImg();

		//make the region dir in Images dir
		mkdir($GLOBALS['SCORE']['SC4IMG_PATH']. '/' . $importRegion);

		// process the city files for this region
		$Cfiles->Load($GLOBALS['SCORE']['SC4PATH'] . '/' . $importRegion);

		// add the region the table
		$sql = "INSERT INTO `regions` ( `id` , `name` , `total_pop` , `total_R` , `total_C` , `total_I` , `vrestrict`, `created`, `modified`) VALUES ('', '{$importRegion}', NULL , NULL , NULL , NULL , '0', now(), now())";
		dbsql($sql);
		$Rid = mysql_insert_id($GLOBALS['score_dbconn']);
		
		// this ensures the error handler can clean up our database tables		
		//$sc_importErrorHandler->setRegionID($Rid);

		if( ! sizeof($Cfiles->getFileContents()))
			return false;

		foreach($Cfiles->getFileContents() as $key=>$file)
		{
			if( substr($file, -4) === '.sc4' && is_readable($path.$file) )
			{

				// load and parse the city file (if readable)
				$scFile->Load($path . $file);
				$scFile->fullParse();

				// load fullsize and process to new size(s)
				$scImg->Load($scFile->contents['mainImg']);
				$scImg->Resize('66%','66%');

				$scImg->setAlphaTrans($scImg->current, false, false, 0,0,0);
				$scImg->setAlphaTrans($scImg->original, false, true, 0,0,0);

				$scImg->writeImg($img_path . substr($file, 0, -4) . '.png', 'original');
				$scImg->writeImg($img_path . substr($file, 0, -4) . '_thumb.png');

				// add city info to table
				$sql = "INSERT INTO `cities`	(	`id`,
															`region_id`,
															`R`,
															`C`,
															`I`,
															`pop`,
															`name`,
															`locX`,
															`locY`,
															`sizeX`,
															`sizeY`,
															`created`,
															`modified`,
															`money`
														)
														VALUES 
														(
															'',
															'{$Rid}',
															'{$scFile->contents['R']}',
															'{$scFile->contents['C']}',
															'{$scFile->contents['I']}',
															'".($scFile->contents['R']+$scFile->contents['C']+$scFile->contents['I'])."',
															'" . addslashes(substr($file, 0, -4)) . "',
															'{$scFile->contents['locX']}',
															'{$scFile->contents['locY']}',
															'{$scFile->contents['sizeX']}',
															'{$scFile->contents['sizeY']}',
															now(),
															now(),
															'{$scFile->contents['money']}'

														)";
				dbsql($sql);
				$total_region['R'] += $scFile->contents['R'];
				$total_region['C'] += $scFile->contents['C'];
				$total_region['I'] += $scFile->contents['I'];
				$total_region['money'] += $scFile->contents['money'];
	
				}
			}

			// update the region info to include RCI & pop
			$sql = " UPDATE `regions` SET
							
							`total_pop` = '".($total_region['R']+$total_region['C']+$total_region['I'])."', 
							`total_R` = '{$total_region['R']}',
							`total_C` = '{$total_region['C']}',
							`total_I` = '{$total_region['I']}',
							`total_money` = '{$total_region['money']}'
						WHERE `id` = '{$Rid}'
					";
			dbsql($sql);
			



			// generate the region image
			$region['name'] = $importRegion;
			$region['id'] = $Rid;
			gen_region_images($region, true);
	}

?>