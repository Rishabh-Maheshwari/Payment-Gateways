<!DOCTYPE html>
<html lang="en">
<head>

  <title>Ingenico</title>
  </head>
  <body>
<FORM METHOD="post" ACTION="https://ogone.test.v-psp.com/ncol/test/querydirect.asp" id=form1 name=form1>
	<INPUT type="hidden" NAME="PSPID" value=<?php echo $PSPID; ?>>
	<INPUT type="hidden" NAME="ORDERID" value=<?php echo $ORDERID; ?> >
	<INPUT type="hidden" NAME="PAYID" value=<?php echo $PAYID; ?>	>
	<INPUT type="hidden" NAME="PSWD" value=<?php echo $PSWD; ?>>
	<INPUT type="hidden" NAME="USERID" value=<?php echo $USERID; ?>>
	
	<input type="submit" value="Submit" id=submit2 name=submit2>
	</form>
	</body>
	</html>
	