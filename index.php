<?php

	// setup general environment
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('log_errors', 0);
	ini_set('session.cookie_lifetime', 3600);
	
	//	suggested that session.name be made something
	//	unique to your setup to prevent people having
	// problems going from one score system to another.
	ini_set('session.name', 'scoreMonkeyzPHun');
	ini_set('max_execution_time', '120');

	/*
		SimCity Online Region Exchange(SCORE)

		score.htm is the central script for the entire SCORE system.
		It handles including the appropriate function libraries,
		session management, setting up the php environment for
		SCORE, and inclusion of the header/footer html.
	*/

	// get various class definitions -- has to be done before session starts
	require('php_inc/classes_inc.php');

	// has to occur before including score_inc.php since score_inc.php
	// deffines the callable functions based on if your a score_user or
	//	score_guest
	session_start();

	// no existing session?
	if( ! isset($_SESSION['score_user']) )
	{
		
		// set the guest flag
		$_SESSION['score_guest'] = true;

		// if no lib is specified via reguest - such as register,
		//	nor any specified as part of the session - then set the default
		if(!isset($_REQUEST['score_lib']) && !isset($_SESSION['score_lib']))
			$_SESSION['score_lib'] = 'login';
	} 

	// get db config and functions -- dbname, user and pass are here
	require('php_inc/database_inc.php');

	// required libs
	require('php_inc/score_inc.php');

	// get and set general score prefs/config
	set_score_prefs();

	/*
	**	call maintenance function - 
	**	looks for
	**		- resigns cities exceeding their time limit
	**		- removes idle members/registrants
	**		- expires idled cities
	**	according to set score_prefs
	*/
	score_maintenance();


	// prep errors array
	$GLOBALS['SCORE']['errors'] = array();

	// call our browser input validation class
	// it looks for $_REQUEST['sf']['TEXT/ID/FILE']
	//	values and validates them as needed.
	$GLOBALS['SCORE']['sc_REQUEST'] = new sc_REQUEST();

	/*
	**	So long as a valid session exits, we'll check for which library 
	**	file to include and if any end-user functions were called.
	*/
	if ( @is_numeric($_SESSION['score_user']->data['id']) || isset($_SESSION['score_guest']) )
	{	

		// sometimes (like for the region view iFrame or for headers)
		//	we want to load score with out spewing content
		if (! isset($_REQUEST['noheader']))
			include('html_inc/header.php');
		/*
		**	Lib & Call handle what section of the site we're at and what functions to call
		*/

		lib_request();	func_request();

		// includes this library's html file
		if (@is_numeric($_SESSION['score_user']->data['id']) || isset($_SESSION['score_guest'])) // still an existing session - checking again because func_request could have logged the user out
			include("html_inc/{$_SESSION['score_lib']}_inc.php");

		// sometimes (like for the region view iFrame or for headers)
		//	we want to load score with out spewing content
		if (! isset($_REQUEST['noheader']))
			include('html_inc/footer.php');
	}


/*
decho ("<div style=\"text-align:left;\">
<div style=\"height:100px\">&nbsp;</div>SESSION");
if (isset($_SESSION))
	print_array($_SESSION);

decho ("GET");
if (isset($_GET))
	print_array($_GET);

decho ("POST");
if (isset($_POST))
	print_array($_POST);

decho ("GLOBAL");
if (isset($GLOBALS['SCORE']))
	print_array($GLOBALS['SCORE']);
decho("</div>")
*/
?>