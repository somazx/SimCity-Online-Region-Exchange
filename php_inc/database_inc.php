<?php

	/*
		SimCity Online Region Exchange(SCORE)

		database_inc.php - contains basic functions for database functionality
	*/


	// *temporary* specify these values here:
	define('DBHOST', '127.0.0.1');
	define('DBUSER','score_sys');
	define('DBPASSWORD','score_sys');
	define('DBNAME', 'score_sys');

	// function to open connection to database
	function dbopen()
	{
		$GLOBALS['score_dbconn'] = mysql_connect(DBHOST, DBUSER, DBPASSWORD);
		mysql_select_db(DBNAME);
		return;
	}


	function dbsql($sql)
	{
		// check for existing connection
		if ( ! isset ($GLOBALS['score_dbconn']) )
			dbopen();

		$result = mysql_query($sql);

		if($result == false) {
			decho (mysql_error ( $GLOBALS['score_dbconn'] ));
			decho ('SQL: ' . $sql);
		}

		return $result;
	}


	// processes a SQL query, collects results into an array, and returns that array
	function dbq($sql)
	{
		$result = false;

		// execute SQL statement and get result resource
		$result = dbsql($sql);
	
		// get query result data
		// decho( "numrows: " . mysql_num_rows($result) );
		if ( is_resource($result)  )
		{
			if ( mysql_num_rows($result) > 0 ) {
				while( $row = mysql_fetch_array($result, MYSQL_ASSOC) )
					$array_result[] = $row;

				mysql_free_result($result);
				return $array_result;
			} else {
	 			return array();
			}
		} 

		return $result;
	}

?>