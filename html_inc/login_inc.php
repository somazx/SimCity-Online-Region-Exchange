
		<!-- outer table -->
		<table style="width:100%; height:100%;">
		 <tr>
		  <td>
				
				<!-- real table -->
				<center>
				<form name="score_login_form" action="<?php echo basename($_SERVER['PHP_SELF']) ?>" method="post">
				
				

				<table class="login_form">
				 <tr>
				  <td>
					<span style="color: navy; font-size: 12px;">SimCity Online Region Exchange (v.RC1)</span>
					<p>
					Login
				  </td>
				 </tr>

				 <tr>
				  <td>
					<input tabindex="1" type="text" name="sf[TEXT][score_login]" value="<?php if(isset($_POST['score_login'])) echo $_POST['score_login']?>">
					<?php show_public_register() ?>
				  </td>
				 </tr>

				 <tr>
				  <td>
					Password
				  </td>
				 </tr>

				 <tr>
				  <td>
					<input tabindex="2" type="password" name="sf[TEXT][score_pass]" value="">
				  </td>
				 </tr>

				 <tr>
				  <td style="text-align: right">
					<input type="hidden" name="noheader" value="true">
					<input tabindex="3" type="submit" name="score_call" value="Login">
				  </td>
				 </tr>
				</table>
				
				<span class="warn_text">
				<b>Note!</b> IE does not fully support PNG transparency.
				</span>
				<br>			
				 <a href="http://www.mozilla.org/products/firefox/" title="Get Firefox - The Browser, Reloaded">
 					<img src="http://www.mozilla.org/products/firefox/buttons/getfirefox_large2.png" width="178" height="60" border="0" alt="Get Firefox">
 				</a>
				</form>
				</center>
				
		  </td>
		 </tr>
		</table>