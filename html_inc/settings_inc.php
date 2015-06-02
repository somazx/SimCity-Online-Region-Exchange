	<div id="function_header">
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_lib=main">Main</a>

		<?php 
			if ( $_SESSION['score_user']->data['privileges'] > 50 )
			{
				echo "
				> <a href=\"".basename($_SERVER['PHP_SELF'])."?score_lib=settings\">Settings</a>";
			}
		?>

		<div style="background-color: black; width: 4px; height: 4px; display: inline; font-size:4px; font-family: fixed; vertical-align: middle; margin-left:4px; margin-right:4px">O</div>
		<font style="color: black">Functions: </font>	
		<a href="<?php echo basename($_SERVER['PHP_SELF']) ?>?score_call=logout">Logout <?php echo $_SESSION['score_user']->data['login'] ?></a>
	</div>

	<?php scoreErrorsOut(); ?>
	
	<table class="main_table">
	  <tr>
		<td class="main_left_column background_image">
			<form action="<?php echo basename($_SERVER['PHP_SELF']) ?>" method="post">
			<div class="column_header background_color1">Player Settings</div>
			
			<div class="column_content">
			
			  <span class="title_text">Personal Info</span>
			  <p>
			  <div class="ldiv">
			  	Email Address
			  </div>
			  <div class="rdiv">
			  	<input type="text" name="sf[TEXT][email_address]" value="<?php echo $_SESSION['score_user']->data['email']?>" size="32">
			  </div>
			  <br>
			  At this time - please contact the <a href="mailto: <?php echo $GLOBALS['SCORE']['ADMIN_EMAIL'] ?>">Admin</a> to change your email address.
			  
			  <hr>
			  <span class="title_text">Change Password</span>
			  <p>

			  <div class="ldiv">
			  	Current Password
			  </div>
			  <div class="rdiv">
			  	<input type="password" name="sf[TEXT][current_password]" value="" size="32">
			  </div>			  
			  
			  <div class="ldiv">
			  	New Password
			  </div>
			  <div class="rdiv">
			  	<input type="password" name="sf[TEXT][new_password1]" value="" size="32">
			  </div>
			  
			  <div class="ldiv">
			  	New Password x2
			  </div>
			  <div class="rdiv">
			  	<input type="password" name="sf[TEXT][new_password2]" value="" size="32">
			  </div>
			  
			  <hr>

  			  <span class="title_text">Game Options</span>
			  <p>
			  <div class="ldiv">
			  	Email game messages?
				<br>(still not really implemented yet - sorry, just ignore)
			  </div>
			  <div class="rdiv">
			  	<input type="hidden" name="sf[TEXT][email_messages]" value="off">
			  	<input type="checkbox" name="sf[TEXT][email_messages]" value="on"<?php isChecked($_SESSION['score_user']->data['email_messages']) ?>>
			  </div>
			  <br>
			  With this option ON you will be sent an email with each new SCORE message.
			  
			  <hr>
			  
			  <div style="text-align: right">
			  	<input type="submit" name="score_call" value="Update User Info">
			  </div>
			  
			</div>
			</form>
		</td>
		
		<td class="main_right_column">
		  <div class="column_header background_color2">Let us know what should go here</div>
		  
		  <div class="column_content">
			 
		  </div>
		</td>
	  </tr>
	</table>