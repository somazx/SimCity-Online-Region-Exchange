
	<div id="function_header">
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_lib=main">Main</a> > 
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_region_id=<?php echo $GLOBALS['SCORE']['scRegion']->data['id'] ?>"><?php echo $GLOBALS['SCORE']['scRegion']->data['name'] ?></a>
		<div style="background-color:black; width: 4px; height: 4px; display: inline; font-size:4px; font-family: fixed; vertical-align: middle; margin-left:4px; margin-right:4px">O</div>
		Functions: 
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_lib=settings">Settings</a>
		&nbsp;|&nbsp;		
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_call=logout">Logout <?php echo $_SESSION['score_user']->data['login']?></a>

		<?php html_adminLink() ?>
	</div>

	

	<div class="background_color1">
		Region Detail & City Listing
	</div>

	<!-- main area of page-->
	<table class="background_image city_main" height="100%" width="100%">
	<tr>
		<td valign="top" height="1%">

			<?php 
				html_show_user_stats();
			?>

			<?php
				html_show_whois_playing_which_cities();
			?>

			<?php
				html_show_updated_cities();
			?>

			<?php scoreErrorsOut(); ?>

			<?php	
				html_show_city_data(); 
			?>

			<div class="column_content">
			
			If you just uploaded your city and you don't see the changes on the full map or in the city view - its likely because of your browser cacheing the image.
			</div>
		</td>
		<td rowspan="2" width="90%" valign="top">


		  <IFRAME src="<?php basename($_SERVER['PHP_SELF'])?>?score_lib=iframe&noheader=true" width="97%" height="95%" scrolling="auto">
		  [Your user agent does not support frames]
		  </IFRAME>
		</td>
	</table>


	<!-- javascript: check for city id and show that city by default -->
	<script type="text/javascript">
		cityid = <?php if ( isset($_REQUEST['cityid']) ) echo $_REQUEST['cityid']; else echo '0'; ?>;
		if (cityid > 0)
			toggle_node(cityid,'cib');
	</script>