<?php

	// if this library is being called then there ought to be a region id available to load a region object
	if ( isset($_SESSION['score_region_id']) )
		$GLOBALS['SCORE']['scRegion'] = new scRegion($_SESSION['score_region_id']);

	function html_regionIframe()
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
			$rand = rand();
			echo "
				<img src=\"{$GLOBALS['SCORE']['SC4IMG_PATH']}/{$GLOBALS['SCORE']['scRegion']->data['name']}/{$GLOBALS['SCORE']['scRegion']->data['name']}.png?nocache={$rand}\" usemap=\"#citymap\" border=\"none\">";
			

			$_SESSION['score_lib'] = 'region';
		}

	function html_generate_Imap($city,$Imap)
		{
			$html_Imap = "
			<area shape=\"poly\" coords=\"";
			
			$html_Imap .= $Imap['x1'] . ',';
			$html_Imap .= $Imap['y1'] . ',';

			$html_Imap .= $Imap['x2'] . ',';
			$html_Imap .= $Imap['y2'] . ',';

			$html_Imap .= $Imap['x3'] . ',';
			$html_Imap .= $Imap['y3'] . ',';

			$html_Imap .= $Imap['x4'] . ',';
			$html_Imap .= $Imap['y4'] . ',';

			$html_Imap .= $Imap['x5'] . ',';
			$html_Imap .= $Imap['y5'] ;

			$html_Imap .= "\" href=\"javascript:parent.toggle_node('{$city['id']}','cib')\" title=\"{$city['name']} | Pop:{$city['pop']} | R:{$city['R']} | C:{$city['C']} | I:{$city['I']}\">";

			return $html_Imap;
		}

?>