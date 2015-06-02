<?php

	function viewCityHistory()
		{
			$GLOBALS['SCORE']['popup_output'] = '<p>';


			if ( ! isset($GLOBALS['SCORE']['sc_REQUEST']->values['ID']['cityid']) )
				return false;

			$history = dbq("SELECT text,created FROM city_log WHERE city_id='{$GLOBALS['SCORE']['sc_REQUEST']->values['ID']['cityid']}' ORDER BY created");

			if ( sizeof($history) )
				foreach ($history as $entry)
					$GLOBALS['SCORE']['popup_output'] .= "
							<div>-<i>{$entry['created']}</i> -- {$entry['text']}</div>
							";
			else
				$GLOBALS['SCORE']['popup_output'] .= 'No history yet.';

			$GLOBALS['SCORE']['popup_output'] .= '<p>';

		}

?>