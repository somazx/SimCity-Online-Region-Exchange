
<table align="center" valign="middle" height="100%">
<td height="100%" valign="middle">
	<div style="width: 400px;">
		<div class="background_color1 column_header">
			SCORE Registration Policy & User Agreement
		</div>

		<div class="column_content">
			Here are the rules for using this SCORE system:<p>

			- Don't be a bad-ass.
			<p>
			If you agree to these terms and rules please continue with 
			the registration.

			<p>
			Thank You.
		</div>
	</div>

	<div class="background_image" style="width:400px;">
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" style="">

		<div class="background_color2 column_header">
			SimCity Online Region Exchange: New User Registration Form
		</div>

		<?php scoreErrorsOut(); ?>

		<br>
		<div class="column_content">

		<?php

			html_register_form()

		?>

		</div>

	</form>
	</div>
</td>
</table>