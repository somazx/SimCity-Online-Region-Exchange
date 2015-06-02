	<div id="function_header">
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_lib=main">Main</a>

		<?php 
			if ( $_SESSION['score_user']->data['privileges'] > 50 )
			{
				echo "
				> <a href=\"".basename($_SERVER['PHP_SELF'])."?score_lib=admin\">Admin</a>";
			}
		?>

		<div style="background-color: black; width: 4px; height: 4px; display: inline; font-size:4px; font-family: fixed; vertical-align: middle; margin-left:4px; margin-right:4px">O</div>
		<font style="color: black">Functions: </font>
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_lib=settings">Settings</a>
		&nbsp;|&nbsp;		
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_call=logout">Logout <?php echo $_SESSION['score_user']->data['login'] ?></a>
	</div>

	<?php scoreErrorsOut(); ?>

	<table class="main_table">
		<td class="main_left_column background_image">

			<div class="column_header background_color1">Region Settings</div>

			<div class="column_content">

				
				<?php html_import_regions() ?>
				
				
				<?php html_get_admin_region_list(); ?>
			</div>



		</td>
		<td class="main_right_column">


			<div class="column_header background_color2">General SCORE Options/Settings:</div>
			<form action="<?php echo basename($_SERVER['PHP_SELF']); ?>" method="post">

			 <div class="column_content">
				
				<?php html_show_perms_state() ?>
				
				<hr>
				<span class="title_text">SCORE Variables</span>
				<p>
				<div class="ldiv">
					Public Registration
				</div>
				<div class="rdiv">
					<input type="hidden" name="score_option[PUBLIC_REG]" value="Off">
					<input type="checkbox" name="score_option[PUBLIC_REG]"<?php isChecked($GLOBALS['SCORE']['PUBLIC_REG']) ?>>
				</div>


				<div class="ldiv">
					Admin Name
				</div>
				<div class="rdiv">
					<input type="text" name="score_option[ADMIN_NAME]" value="<?php echo $GLOBALS['SCORE']['ADMIN_NAME'] ?>" size="32">
				</div>


				<div class="ldiv">
					Admin Email
				</div>
				<div class="rdiv">
					<input type="text" name="score_option[ADMIN_EMAIL]" value="<?php echo $GLOBALS['SCORE']['ADMIN_EMAIL'] ?>" size="32">
				</div>
				
				
				<hr>
				<span class="title_text">SMTP Settings</span> (Optional)
				<p>
				
				SCORE will use php's built in mail() 
				function which uses sendmail on linux, 
				and an external, non authenticating SMTP
			 	server on windows (specified in php.ini).
			 	<p>
			 	If you need authenitcating SMTP or just wish
			 	to use an external SMTP, specify the server and
			 	optionally any login/pass needed to authenticate.
			 	Leave blank if no authentication is not needed.
			 	

				<div class="ldiv">
					External SMTP Host
				</div>
				<div class="rdiv">
					<input type="text" name="score_option[SMTP_HOST]" value="<?php echo $GLOBALS['SCORE']['SMTP_HOST'] ?>" size="32">
				</div>
				

				<div class="ldiv">
					SMTP Login
				</div>
				<div class="rdiv">
					<input type="text" name="score_option[SMTP_USER]" value="<?php echo $GLOBALS['SCORE']['SMTP_USER'] ?>" size="32">
				</div>
				
				<div class="ldiv">
					SMTP Password
				</div>
				<div class="rdiv">
					<input type="text" name="score_option[SMTP_PASS]" value="<?php echo $GLOBALS['SCORE']['SMTP_PASS'] ?>" size="32">
				</div>							
				
				<hr>
				
				<span class="title_text">Expiration of Stale Accounts</span>
				<p>

				<div class="ldiv">
					Warn on accounts Idle for X days old
				</div>
				<div class="rdiv">
					<input type="text" name="score_option[idle_account_limit_warn]" value="<?php echo $GLOBALS['SCORE']['idle_account_limit_warn'] ?>" size="4">
				</div>

				<div class="ldiv">
					Remove account X days later after warn
				</div>
				<div class="rdiv">
					<input type="text" name="score_option[idle_account_limit]" value="<?php echo $GLOBALS['SCORE']['idle_account_limit'] ?>" size="4">
				</div>


				<hr>

				<span class="title_text">Path Settings</span>
				<p>
				
				<font style="color: navy">Note: Changing these values once regions are in progress is a very bad idea. Existing regions should be deleted and added back.</font>
				<p>
				<div style="text-align: right">

				<div class="ldiv">
					Use Zip (<?php zipLibStatus() ?>)
				</div>
				<div class="rdiv">
					<input type="hidden" name="score_option[USE_ZIP]" value="Off">
					<input type="checkbox" name="score_option[USE_ZIP]"<?php isChecked($GLOBALS['SCORE']['USE_ZIP']) ?>>
				</div>

				<div class="ldiv">
					Images Path
				</div>
				<div class="rdiv">
					<input type="text" name="score_option[SC4IMG_PATH]" value="<?php echo $GLOBALS['SCORE']['SC4IMG_PATH'] ?>" size="32">
				</div>

				<div class="ldiv">
					Region Path
				</div>
				<div class="rdiv">
					<input type="text" name="score_option[SC4PATH]" value="<?php echo $GLOBALS['SCORE']['SC4PATH'] ?>" size="32">
				</div>

				<div class="ldiv">
					Region Image Width (pixels or percent)
				</div>
				<div class="rdiv">
					<input type="text" name="score_option[SC4REGION_IMG_WIDTH]" value="<?php echo $GLOBALS['SCORE']['SC4REGION_IMG_WIDTH'] ?>" size="6">
				</div>

				<hr style="clear:both;">

				
				
				
				<span class="title_text" style="text-align:left;">Server Environment (php.ini)</span>
				<p>
		
				<div class="ldiv">
					max_execution_time (in seconds)
				</div>
				<div class="rdiv">
					<font style="color: green;"><?php echo ini_get('max_execution_time') ?>&nbsp;</font>
				</div>
				
				<div class="ldiv">
					post_max_size  (10 - 20MB recommended)
				</div>
				<div class="rdiv">
					<font style="color: green;"><?php echo ini_get('post_max_size') ?>&nbsp;</font>
				</div>

				<div class="ldiv">
					upload_max_filesize (match to post_max_size)
				</div>
				<div class="rdiv">
					<font style="color: green;"><?php echo ini_get('upload_max_filesize') ?>&nbsp;</font>
				</div>

				<div class="ldiv">
					memory_limit (post_max_size + 10%)
				</div>
				<div class="rdiv">
					<font style="color: green;"><?php echo ini_get('memory_limit') ?>&nbsp;</font>
				</div>

				<br>
				<hr style="clear:both;">
				
				<input type="submit" name="score_call" value="Update SCORE Settings">

			 </div>
			</form>

		</td>
	</table>