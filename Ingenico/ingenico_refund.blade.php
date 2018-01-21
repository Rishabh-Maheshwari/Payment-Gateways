<!DOCTYPE html>
<html lang="en">
<head>

  <title>Ingenico</title>
  </head>
  <body>
<FORM METHOD="post" ACTION="https://ogone.test.v-psp.com/ncol/test/maintenancedirect.asp" id="form" name="form">
	<INPUT type="hidden" NAME="PSPID" value="<?php echo $PSPID; ?>">
	<INPUT type="hidden" NAME="ORDERID" value="<?php echo $ORDERID; ?>" >
	<INPUT type="hidden" NAME="AMOUNT" value="<?php echo $AMOUNT; ?>" >
	<INPUT type="hidden" NAME="OPERATION" value="<?php echo $OPERATION; ?>" >
	<INPUT type="hidden" NAME="PAYID" value="<?php echo $PAYID; ?>"	>
	<INPUT type="hidden" NAME="PSWD" value="<?php echo $PSWD; ?>" >
	<INPUT type="hidden" NAME="USERID" value="<?php echo $USERID; ?>" >
	<INPUT type="hidden" NAME="SHASIGN" value="<?php echo $SHASIGN; ?>" >
	
	<input type="submit" value="Submit" id="submit" name="submit">
	</form>
	</body>
	</html>