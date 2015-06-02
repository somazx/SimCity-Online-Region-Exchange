	<?
	
	function html_show_region_detail()
	{

		if (	! isset($GLOBALS['SCORE']['scRegion'])	)
			return;

		$sql =  "SELECT * FROM cities WHERE region_id = {$GLOBALS['SCORE']['scRegion']->data['id']}";
		$city_data = dbq($sql);
		
		echo "
		<div style=\"margin-bottom: 10px\">
			<b style=\"font-size: 12px\">{$GLOBALS['SCORE']['scRegion']->data['name']}</b>
			<br>
			- Created: NA
			&nbsp;|&nbsp;
			Last Updated: NA
			<p>
			- TotalPop: {$GLOBALS['SCORE']['scRegion']->data['total_pop']}
			<br>
			&nbsp;&nbsp;
			R: {$GLOBALS['SCORE']['scRegion']->data['total_R']}
			&nbsp;|&nbsp;
			C: {$GLOBALS['SCORE']['scRegion']->data['total_C']}
			&nbsp;|&nbsp;
			I: {$GLOBALS['SCORE']['scRegion']->data['total_I']}
		</div>

		<div>
			City Upload:
			<form enctype=\"multipart/form-data\" action=\"".basename($_SERVER['PHP_SELF'])."\" method=\"post\">
			<input type=\"file\" name=\"score_file\">
			<input type=\"submit\" name=\"score_call\" value=\"Upload\">
			</form>
		</div>
		<hr>
		";

		// output city listing
		if (is_array($city_data))
		{
			echo "
				<table style=\"border: none; padding: none; margin: none;\">
			";
			foreach($city_data as $key=>$city)
			//print_array ($city);
			{
				echo "<tr>
						 <td style=\"margin-bottom: 10px\">

								<a href=\"{$GLOBALS['SCORE']['SC4PATH']}/".rawurlencode($GLOBALS['SCORE']['scRegion']->data['name'])."/".rawurlencode($city['name']).".sc4\" title=\"Click to Download\">{$city['name']}</a>
								<br>
								- Created: NA<br>
								- Updated: NA
								<p>
								- TotalPop: {$city['pop']}<br>
								&nbsp;&nbsp;
								R: {$city['R']}
								<br>
								&nbsp;&nbsp;
								C: {$city['C']}
								<br>
								&nbsp;&nbsp;
								I: {$city['I']} 
								<p>
								</td>
							<td>
								<img src=\"{$GLOBALS['SCORE']['SC4IMG_PATH']}/".rawurlencode($GLOBALS['SCORE']['scRegion']->data['name'])."/".rawurlencode($city['name'])."_thumb.png\" align=\"right\">
							</td>
						  </tr>
				";
			}
			echo "</table>";
		}
	}



		function showEntireRegion()
	{

			$sql = "SELECT name,sizeX,sizeY,locX,locY FROM cities WHERE region_id = {$_SESSION['score_region_id']} ORDER BY locY,locX";
			$array = dbq($sql);

			drawEntireRegion($array, $GLOBALS['SCORE']['scRegion']->data);
	}




	function drawEntireRegion($array, $region)
		{

			$configBMP = configBMP($array);
			$f=0;
			while(sizeof($array))
			{
				foreach($array as $key => $item)
				{
					if ( tileCheck($item['locX'], $item['locY'], $item['sizeX'], $item['sizeX'], $configBMP) )
					{
						$drawlist[] = $item;
						unset($array[$key]);
					} else {
						$f++;
						if ($f > 1000)
							exit;
					}
				}
			}

			$so[1]['X']=0;
			$so[1]['Y']=0;

			$so[2]['X']=-35;
			$so[2]['Y']=20;

			$so[4]['X']= -110;
			$so[4]['Y']= 60;

			foreach($drawlist as $tile)
			{
				$Isize = getimagesize ( "{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$GLOBALS['SCORE']['scRegion']->data['name']}/{$tile['name']}.png" );

				$YOffset = ($Isize[1]) - (45 * $tile['sizeY']);
				$ImageX = (-37 * $tile['locY'] + 1) + ($tile['locX'] * 90) + $so[$tile['sizeX']]['X'] + 250;
				$ImageY = (45 * $tile['locY'] + 1) + ($tile['locX'] * 18) - $YOffset + $so[$tile['sizeY']]['Y'] + 75;
				echo
				"
					<img src=\"{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$GLOBALS['SCORE']['scRegion']->data['name']}/{$tile['name']}.png\" style=\"position:absolute; top: {$ImageY}; left: {$ImageX}\">
				";
			}
		}


function rebuildSCORE () {

		// setting some variables
		$t_start = array_sum(explode(' ', microtime()));	// to monitor processing time
		$ccount = 0;													//	city count
		$rcount = 0;													//	region count

		echo (".");
		// clear existing data
		$sql = "TRUNCATE TABLE regions";
		dbsql($sql);
		$sql = "TRUNCATE TABLE cities";
		dbsql($sql);

		echo (".");
		// delete image data
		remove_dir($GLOBALS['SCORE']['SC4IMG_PATH']);
		mkdir($GLOBALS['SCORE']['SC4IMG_PATH']);

		echo (".");
		// prepare our objects
		$Rdirs	=	new scDir();
		$Cfiles	=	new scDir();
		$scFile	=	new scSaveGameFile(); 
		$scImg	=	new scImg();

		echo (".");
		// Read in the Regions dir. Loop through each region gathering city names, and other data		
		$Rdirs->Load($GLOBALS['SCORE']['SC4PATH']);

		if( ! sizeof($Rdirs->getDirContents()))
			return false;

		foreach($Rdirs->getDirContents() as $key=>$dir)
		{
			echo (".");
			$rcount++;
			$total_region['R'] = $total_region['C'] = $total_region['I'] = 0;
			$path = $GLOBALS['SCORE']['SC4PATH'] . '/' . $dir . '/';
			$img_path = $GLOBALS['SCORE']['SC4IMG_PATH']. '/' . $dir . '/';

			echo (".");
			// add the region the table
			$sql = "INSERT INTO `regions` ( `id` , `name` , `total_pop` , `total_R` , `total_C` , `total_I` , `vrestrict`, `created`, `modified`) VALUES ('', '{$dir}', NULL , NULL , NULL , NULL , '0', now(), now())";
			dbsql($sql);

			echo (".");
			mkdir($GLOBALS['SCORE']['SC4IMG_PATH']. '/' . $dir);

			echo (".");
			$Rid = mysql_insert_id($GLOBALS['score_dbconn']);

			echo (".");
			// process the city files for this region
			$Cfiles->Load($GLOBALS['SCORE']['SC4PATH'] . '/' . $dir);

			if( ! sizeof($Cfiles->getFileContents()))
				return false;

			echo (".");
			foreach($Cfiles->getFileContents() as $key=>$file)
			{
				flush();
				if( substr($file, -4) === '.sc4' )
				{
					$ccount++;

					echo (".");
					// load and parse the city file
					$scFile->Load($path . $file);
					$scFile->fullParse();

					echo (".");
					// load fullsize and process to new size(s)
					$scImg->Load($scFile->contents['mainImg']);
					$scImg->Resize('66%','66%');
					$scImg->setAlphaTrans($scImg->current, false, true, 0,0,0);
					$scImg->setAlphaTrans($scImg->original, false, true, 0,0,0);

					echo (".");
					$scImg->writeImg($img_path . substr($file, 0, -4) . '.png', 'original');
					$scImg->writeImg($img_path . substr($file, 0, -4) . '_thumb.png');

					echo (".");
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
																`modified`
															)
															VALUES 
															(
																'',
																'{$Rid}',
																'{$scFile->contents['R']}',
																'{$scFile->contents['C']}',
																'{$scFile->contents['I']}',
																'".($scFile->contents['R']+$scFile->contents['C']+$scFile->contents['I'])."',
																'" . substr($file, 0, -4) . "',
																'{$scFile->contents['locX']}',
																'{$scFile->contents['locY']}',
																'{$scFile->contents['sizeX']}',
																'{$scFile->contents['sizeY']}',
																now(),
																now()

															)";
					dbsql($sql);
					$total_region['R'] += $scFile->contents['R'];
					$total_region['C'] += $scFile->contents['C'];
					$total_region['I'] += $scFile->contents['I'];
	

				}
			}

			// update the region info to include RCI & pop
			echo (".");
			$sql = " UPDATE `regions` SET
							
							`total_pop` = '".($total_region['R']+$total_region['C']+$total_region['I'])."', 
							`total_R` = '{$total_region['R']}',
							`total_C` = '{$total_region['C']}',
							`total_I` = '{$total_region['I']}'
						
						WHERE `id` = '$Rid'
					";
			dbsql($sql);

			// generate the region images
			$region['name'] = $dir;
			$region['id'] = $Rid;
			
			gen_region_images($region, true);
		}

		decho("Processed $ccount cities in $rcount regions in " . (array_sum(explode(' ', microtime())) - $t_start) . " seconds");
	}

	?>