<?php
	
	/*
	**	Score Classes
	**
	**	Requires the regionFileDecode classes
	*/
	

	class scUser 
	{
		var $data;

		function scUser($data)
			{
				$this->data = $data;
			}
	}
	

	class scMayor extends scUser
	{
		var $data;

		function scMayor($id)
		{
			$sql = "SELECT * FROM users WHERE id = '{$id}'";
			$r = dbq($sql);

			if( sizeof($r) != 1)
				return false;

			$this->data = $r[0];
		}
	}


	class scRegion
	{
		var $data;
		var $status;

		function scRegion($id)
			{
				$sql = "SELECT * FROM regions WHERE id = '{$id}'";
				$tmp = dbq($sql);

				if( ! sizeof($tmp) == 1 )
				{
					$this->status = false;
					$GLOBALS['SCORE']['errors'][] = "Error Loading Region Object";
					return $this->status;
				}

				$this->data = $tmp[0];
				$this->data['imagemap'] = unserialize($this->data['imagemap']);

				$this->status = true;
				return $this->status;

			}


		function getCityList()
		{
			$sql = "SELECT * FROM cities WHERE region_id = '{$this->data['id']}'";
			$tmp = dbq($sql);

			foreach($tmp as $city)
				$this->data['Cities'][$city['id']] = $city;
		}

		// gets the number of city-points used in this region
		function usedCityPoints ($force=false)
			{
				if( ! isset($this->data['Cities']) )
					$this->getCityList();

				$total=0;

				foreach($this->data['Cities'] as $id => $city)
					if ( $_SESSION['score_user']->data['id'] ==  $city['mayor_id'])
						$total += $city['sizeX'];

				return $total;
			}

	}


	class scImg extends scBaseClass
	{
			var $original;						// img resource
			var $currrent;						// current image
		

			function Load($data)
				{
					$this->original = imagecreatefromstring ( $data );
				}


			//	handles converting percents to pix and calculating proportionate X or Y
			function normalizeSize(&$toX, $cX, &$toY, $cY)
				{
					// pixelize $toX if percent
					if (	substr($toX,-1) == '%'	) // if number provided in percent
					{
						$toX = $cX * (substr($toX,0,-1) / (	100 ) );
					}

					// pixelize $toY if percent
					if (	substr($toY,-1) == '%'	) // if number provided in percent
					{
						$toY = $cY * (substr($toY,0,-1) / (	100 ) );
					}

					// determine if we need to calculate aspect ratio resize
					// based on if only one dimension size was provided
					if ($toX == '' && $toY > 0)
						$toX = $cX * ($toY/$cY);

					if ($toY == '' && $toX > 0)
						$toY = $cY * ($toX/$cX);
						
				}

			function setAlphaTrans(&$Image, $ImgAlphaBlend, $ImgSaveAlpha, $R,$G,$B)
				{
					imagealphablending ( $Image, $ImgAlphaBlend);
					imagesavealpha ( $Image, $ImgSaveAlpha );
					$trans = imagecolortransparent ( $Image, imagecolorallocate ( $Image, $R, $G, $B ) );
				}


			function Resize($toX, $toY)
				{
					$cX =		imagesx($this->original);
					$cY =		imagesy($this->original);

					$this->normalizeSize($toX, $cX, $toY, $cY);
					
					// skip it if there would be no size change
					if ($toX == $cX && $toY == $cY)
					{
						$this->current = $this->original;
						return true;
					}

					$this->current = imagecreatetruecolor (	$toX, $toY	);
					$return = imagecopyresampled (	$this->current, 
																$this->original,
																0,
																0,
																0,
																0,
																$toX,
																$toY,
																$cX,
																$cY);
				}


			function writeImg($path, $which='current')
				{
					if ($which == 'current')
					{
						imagepng($this->current, $path);
					}
					else
					{
						imagepng($this->original, $path);
					}
				}


			function writeImgToBrowser()
				{
					header ("Content-type: image/png"); 
					imagepng ($this->current);
				}

			
			function loadCurrent()
				{
					$this->original = $this->current;
					$this->current = false;
				}
	}



	class scRegionImg extends scImg
	{
		var $tilearray;	// string containing html Image Map coords
		var $Imap;			// holds image map coordinates if needed

		function drawEntireRegion($region, $doImap=false)
			{
				$this->status = true;
				$this->tilearray = array();

				$sql = "SELECT id,name,sizeX,sizeY,locX,locY FROM cities WHERE region_id = {$region['id']}";
				$array = dbq($sql);

				$configBMP = configBMP($array);

				while(sizeof($array))
				{
					foreach($array as $key => $item)
					{
						if ( tileCheck($item['locX'], $item['locY'], $item['sizeX'], $item['sizeX'], $configBMP) )
						{
							$this->tilearray[] = $item;
							unset($array[$key]);
							$f=true;
						}
					}

					// check to make sure the loop placed at least 1 tile
					// otherwise "we may have a problem Houston".
					if ($f !== true)
						return false;
					else
						$f = false;
				}
				
				if( ! sizeof($this->tilearray) )
				{
					$GLOBALS['SCORE']['errors'][] = "It appears there are no cities in this region. This is likely due to permissions - unreadable city files.";
					$this->status = false;
					return $this->status;
				}

				unset($array);

				$maxX = 0;
				$maxY = 0;
				foreach($this->tilearray as $A)
				{
					( ($A['locX'] + $A['sizeX']) > $maxX) ? ($maxX = ($A['locX'] + $A['sizeX']  ) ) : false ; 
					( ($A['locY'] + $A['sizeY']) > $maxY) ? ($maxY = ($A['locY'] + $A['sizeY']  ) ) : false ;

				}

				$so[1]['X']=0;
				$so[1]['Y']=0;

				$so[2]['X']=-35;
				$so[2]['Y']=20;

				$so[4]['X']= -111;
				$so[4]['Y']= 58;

				$this->current = imagecreatetruecolor( ($maxX*127)+(($maxX*127)*.01),($maxY*68)+(($maxY*68)*.01) );

				foreach($this->tilearray as $key => $tile)
				{
					$this->Load(file_get_contents("{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$region['name']}/{$tile['name']}.png"));
					$Isize['X'] = imagesx($this->original);
					$Isize['Y'] = imagesy($this->original);

					$YOffset = ($Isize['Y']) - (45 * $tile['sizeY']);
					$ImageX = (-37 * $tile['locY'] + 1) + ($tile['locX'] * 90) + $so[$tile['sizeX']]['X'] + $maxX*127*0.27;
					$ImageY = (45 * $tile['locY'] + 1) + ($tile['locX'] * 18) - $YOffset + $so[$tile['sizeY']]['Y']+50;
				
					/*
					**	now that we've determined where this tile goes - copy it to our scratch (current) image
					*/
					if($doImap)
						{
							$this->Imap[$tile['id']] = array(
												'x1' => $ImageX + (0.29*$Isize['X'])+5,			'y1' => $ImageY,										// top left
												'x2' => $ImageX +5,										'y2' => $ImageY+(0.744*($Isize['Y']-40)),		// bottom left
												'x3' => $ImageX + (0.71*$Isize['X'])-5,			'y3' => ($ImageY+$Isize['Y']-30),				// bottom right
												'x4' => ($ImageX + $Isize['X'])-5,					'y4' => $ImageY+(0.248*$Isize['Y']),			// top right
												'x5' => $ImageX+ (0.29*$Isize['X']),				'y5' => $ImageY										// top left (again)
											);
						}

					
					imagecopy ( $this->current, $this->original, $ImageX, $ImageY, 0, 0, $Isize['X'], $Isize['Y'] );
				}
			}
	}


	function &configBMP ($array)
		{
			$maxX = $maxY = 0;
			foreach($array as $A)
			{
				( ($A['locX'] + $A['sizeX']) > $maxX) ? ($maxX = ($A['locX'] + $A['sizeX'] - 1 ) ) : false ; 
				( ($A['locY'] + $A['sizeY']) > $maxY) ? ($maxY = ($A['locY'] + $A['sizeY'] - 1 ) ) : false ;

			}

			$img = imagecreatetruecolor($maxX+1, $maxY+1);
			$white = imagecolorallocate($img, 255,255,255);
			$c[1] = imagecolorallocate($img, 255,0,0);
			$c[2] = imagecolorallocate($img, 0,255,0);
			$c[4] = imagecolorallocate($img, 0,0,255);

			foreach($array as $tile)
			{
			foreach($array as $tile)
				for($x=$tile['locX']; $x <= (($tile['locX']) + ($tile['sizeX']-1)); $x++ )
					for($y=$tile['locY']; $y <= (($tile['locY']) + ($tile['sizeY']-1)); $y++ )
						imagesetpixel ( $img, $x, $y, $c[$tile['sizeY']] );
			}


			return $img;
		}


	function tileCheck($x,$y,$xSize,$ySize,&$configBMP)
		{	
			$imageX = imagesx($configBMP);
			$imageY = imagesy($configBMP);

			$blk=  imagecolorallocate ( $configBMP, 0, 0, 0 );
			
			if ($y > 0)
				for($n=0; $n < $xSize; $n++)
					if ( imagecolorat($configBMP, $x+$n, $y-1 ) != $blk )
						return false;


			if ($x > 0)
				for($n=0; $n < $ySize; $n++)
					if ( imagecolorat($configBMP, $x-1, $y+$n) != $blk )
							return false;

	
			for ($imgX=$x; $imgX < ($x + $xSize); $imgX++)
				for ($imgY=$y; $imgY < ($y + $ySize); $imgY++)
					imagesetpixel($configBMP, $imgX, $imgY, $blk);

			return true;
		}



	class scFile extends scBaseClass
	{


			var $data;						// file resource
			var $length	= 0;				// file_data length

			function Load($file) 
				{
					if (file_exists($file) && is_readable($file) ){
						$this->data = file_get_contents($file);
						$this->length = strlen($this->data);
						$this->status = true;
					} else {
						$this->status = false;
						return $this->status;
					}
				}


			function getContents()
				{
					//print_Array($this->dir_content);
					return $this->file_data;
				}


			function unLoad()
				{
					$this->data = '';
				}
	}


	class scSaveGameFile extends scFile
	{

			var $indexes = array();		// stores the parsed indexes from file
			var $contents = array();	// anything extracted frm file goes in this array

			function scSaveGameFile ()
				{
					require('php_inc/regionFileDecode_inc.php');
				}


			function isSane()
				{
					//	file big enough?
					if(! strlen($this->data) > 11)
					{
						$this->status=false;
						return false;
					}

					$this->contents['major_version'] = unpack1(substr($this->data,4,4),'V');
					$this->contents['minor_version'] = unpack1(substr($this->data,8,4),'V');

					// return valid version #?
					if ($this->contents['major_version'] == '1' && $this->contents['minor_version'] == '0')
					{
						return '1.0';
					} else {
						false;	
					}
				}

			function locateIndex($TGI)
				{

					if(! is_array($this->indexes) )
						return false;

					foreach($this->indexes as $key=>$index)
					{
						$dat = unpack('V*',substr($index,0,20));
						//decho( strtolower(dechex($dat[1])) .'=='. strtolower($TGI[0]) .'&&'. strtolower(dechex($dat[2])) .'=='. strtolower($TGI[1])  .'&&'.  strtolower(dechex($dat[3])) .'=='. strtolower($TGI[2]) );
						if( strtolower(dechex($dat[1])) ==  strtolower($TGI[0]) && strtolower(dechex($dat[2])) == strtolower($TGI[1])  &&  strtolower(dechex($dat[3])) == strtolower($TGI[2]))
							return $dat;
					}
					
					return false;
				}


			function parseRegionFileData($Cname='unknown')
				{

					$TGI = array('ca027edb', 'ca027ee1', '00');
					$index = $this->locateIndex($TGI);

					if(!$index)
						return false;

					//decho ("grabbing region file at {$dat[4]} thru {$dat[5]} bytes");
					$RF = substr($this->data, $index[4], $index[5]);
					unset($dat);
					
					$RF = fileDecode($RF, $Cname);

					$RFstring = implode($RF,'');
					unset($RF);
					
					$this->contents['rvmj'] = unpack1(substr($RFstring,0,2),'v');
					$this->contents['rvmi'] =unpack1(substr($RFstring,2,4),'v');

					$this->contents['locX'] = unpack1(substr($RFstring,4,4));
					$this->contents['locY'] = unpack1(substr($RFstring,8,4));

					$this->contents['sizeX'] = unpack1(substr($RFstring,12,4));
					$this->contents['sizeY'] = unpack1(substr($RFstring,16,4));

					$this->contents['R'] = unpack1(substr($RFstring,20,4));
					$this->contents['C'] = unpack1(substr($RFstring,24,4));
					$this->contents['I'] = unpack1(substr($RFstring,28,4));

					return true;
				}


			function parseDates()
				{
					if(! strlen($this->data) > 31)
					{
						$this->status=false;
						return false;
					}

					$this->contents['dateC'] = date('Y-m-d',unpack1(substr($this->data,24,4),'V'));
					$this->contents['dateM'] = date('Y-m-d',unpack1(substr($this->data,28,4),'V'));

					return true;
				}


			function parseIndexes() {
				if(! strlen($this->data) > 43)
				{
					$this->status = false;
					return false;
				}

				$num_indexes = unpack1(substr($this->data, 36, 4));
				$loc_indexes =	unpack1(substr($this->data, 40, 4));

				//decho("num_indexes: $num_indexes | loc_indexes: $loc_indexes");
				$tloc_indexes=$loc_indexes;

				for($i=0; $i < $num_indexes; $i++)
				{
					$this->indexes[$i] = substr($this->data, $tloc_indexes, 20);
					$tloc_indexes+=20;
				}
			}


			function parseMainImg()
				{
						$index = $this->locateIndex(array('8a2482b9', '4a2482bb', '00'));
						if(!$index)
							return false;
						$this->contents['mainImg'] = substr( $this->data, $index[4] , $index[5] );
				}


			function fullParse($Cname='unknown')
				{ // to handle doing all parse* methods in one go
					if(! ($this->isSane()  &&	$this->parseDates()))
					{
						$GLOBALS['SCORE']['errors'][] = 'File was found to be unsane.';
						return false;
					}
					

					$this->parseIndexes();
					$this->parseRegionFileData($Cname);
					$this->parsemainImg();
					$this->parseMoney();
				}

			function parseMoney()
				{
					$TGI = array('e990BE01','299B2D1B','00000');
					$index = $this->locateIndex($TGI);
					if ( ! $index )
						return false;

					$file = substr($this->data, $index[4], $index[5]);
					unset($dat);

					$file = fileDecode($file, 'moneyFile');
					
					$file = implode($file,'');

					// the money value is in the file at:
					//	long long long short quad-money-value
					//	4	+ 4 +	4 + 2 = 14
					$this->contents['money'] = unpack1(substr($file,14,4),'V');
				}
	}



	class scDir extends scBaseClass
	{
			var $dir_content = array();					// contents of dir


			// get $dir's contents
			function Load($dir)
				{
					$this->dir_content = array();
					if (is_dir($dir)) {
						if ($dh = opendir($dir)) { 
							 while (($file = readdir($dh)) !== false) {
								  if($file != '.' && $file != '..')
									  $this->dir_content[@is_dir($dir .'/'. $file)][] =  $file; // silence errors due to file not being readable
							 } 
							 closedir($dh);
							 $this->status = true;
							 return;
						} else {
							$this->status = false;
							return $this->status;
						}
					} else {
						$this->status = false;
						return $this->status;
					}
				}


			function getDirContents()
				{
					if(isset($this->dir_content[1]))
						return $this->dir_content[1];
					else
						return false;
				}

			function getFileContents()
				{
					if(isset($this->dir_content[0]))
						return $this->dir_content[0];
					else
						return false;
				}

			function getAllContents()
				{
					if ( is_array($this->dir_content[0]) && is_array($this->dir_content[1]) )
						return array_merge($this->dir_content[0], $this->dir_content[1]);
					elseif ( is_array($this->dir_content[0]) && !is_array($this->dir_content[1]) )
						return $this->dir_content[0];
					elseif ( !is_array($this->dir_content[0]) && is_array($this->dir_content[1]) )
						$this->dir_content[1];
					else
						return false;
				}
	}


	class scBaseClass
	{
			var $status;						// last action's return

			function getStatus()
				{
					return $this->status;
				}

	}


	// class will be loaded with any values in $_REQUEST['sf']
	// at each load of score.php - these values have general validation
	// performed on them such as confirming integers and basenaming file
	// names, escaping text strings
	class sc_REQUEST extends scBaseClass
	{
		var $values;		// contains $_REQUEST values
		var $types;			// contains the data types we validate

		function sc_REQUEST()
			{
				// set the default state of class vars
				$this->values = array();
				$this->types = array('TEXT','ID','FILE');

	
				// check if we have anything for us
				if ( ! isset($_REQUEST['sf']) || ! is_array($_REQUEST['sf']) )
					return false;

				$this->values = $_REQUEST['sf'];
				
				// now call out validator functions
				foreach($this->types as $type)
					$this->validate($type);

			}


		//	these functions have to handle
		//	if instead of a single value
		//	its any number of sub arrays
		//  - so recursive behaviour.
		function validate($type)
			{
				if ( ! isset($this->values[$type]) || ! is_array($this->values[$type]) )
					return false;

				// dynamic method call --- oooh beholds it's wonder and slendor
				$this->{'validate'.$type}($this->values[$type]);
			}


		function validateTEXT(&$array)
			{
				foreach($array as $key => $val)
					if ( is_array($val) )
						$this->validateTEXT($array[$key]);
					else
						$array[$key] = addslashes($val);
			}

		function validateID(&$array)
			{
				// check for valid integer, unset if not
				foreach( $array as $key => $val )
					if ( is_array($val) )
						$this->validateID($array[$key]);
					else
						if ( ! $this->isInt($val) )
							unset($array[$key]);
			}

		function validateFILE(&$array)
			{
				// basename these values
				foreach($array as $key => $val)
					if ( is_array($val) )
						$this->validateFILE($array[$key]);
					else
						$array = basename($val);

			}

		function isInt($var)
			{
				// Determines if a variable's value is an integer regardless of type
				// meant to be an analogy to PHP's is_numeric()
				if (is_int($var)) return TRUE;
				if (is_string($var) && $var === (string)(int) $var) return TRUE;
				//if (is_float($var) and $var === (float)(int) $var) return TRUE;
				else return FALSE;
			}
		
	}
	
	
	class sc_importErrorHandler
	{
		var $region_id;
		var $region_name;
	
		function sc_importErrorHandler($rname, $rid=false)
			{
				$this->region_id = $rid;
				$this->region_name = $rname;
				set_error_handler(array(&$this, 'errorHandler'));
			}
			
		function setRegionID($rid)
			{
				$this->region_id = $rid;
				set_error_handler(array(&$this, 'errorHandler'));
			}
	
		function errorHandler($errno, $errmsg, $filename, $linenum, $var)
			{
				$errortypes = array(2 => 'Warning', 8 => 'Notice');
				$GLOBALS['SCORE']['errors'][] = "There has been an error during the import: <b>{$errortypes[$errno]}</b> $errmsg in $filename on line $linenum.";
				
				// clean up regions and cities database
				if(is_numeric($this->region_id))
				{
					decho ("cleaning tables");
					dbq("DELETE FROM cities WHERE region_id='{$this->region_id}'");
					dbq("DELETE FROM regions WHERE id='{$this->region_id}'");
				}
				
				// clean up images directory
				remove_dir("{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$this->region_name}");
			}
	}
?>