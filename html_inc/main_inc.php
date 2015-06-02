
	<div id="function_header">
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_lib=main">Main</a>
		<div style="background-color: black; width: 4px; height: 4px; display: inline; font-size:4px; font-family: fixed; vertical-align: middle; margin-left:4px; margin-right:4px">O</div>
		<font style="color: black">Functions: </font>
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_lib=settings">Settings</a>
		&nbsp;|&nbsp;			
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_call=logout">Logout <?php echo $_SESSION['score_user']->data['login'] ?></a>

		<?php html_adminLink() ?>
	</div>
	

	<?php scoreErrorsOut(); ?>



	<table id="main">
		<td class="main_left_column background_image">
			<div class="column_header background_color1">
				All Regions / Games
			</div>
	
			<div class="column_content">
				<?php html_show_all_regions() ?>
			</div>
		</td>
		
		<td class="main_right_column">
		
			<div class="column_header background_color2">
				Your Cities in Play
			</div>
			<div class="column_content">
					<?php html_show_your_cities() ?>
			</div>
			
			
			<div class="column_header background_color2">
				Your Messages
			</div>
			<div class="column_content">
				<?php html_show_your_messages() ?>
			</div>

			<?php html_show_admins_cityrequests() ?>
						
		</div>
	</td>