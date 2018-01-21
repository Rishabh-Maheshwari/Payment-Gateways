<!DOCTYPE html>
<html lang="en">
<head>
  <title>Ingenico</title>
  </head>
  <body>
<FORM METHOD="post" ACTION="https://secure.ogone.com/ncol/test/orderstandard.asp" id=form1 name=form1>
	<INPUT type="hidden" NAME="PSPID" value="<?php echo $PSPID; ?>">
	<INPUT type="hidden" NAME="AMOUNT" value="<?php echo $AMOUNT; ?>"> 
	<INPUT type="hidden" NAME="CURRENCY" value="<?php echo $CURRENCY; ?>">
	<INPUT type="hidden" NAME="LANGUAGE" value="<?php echo $LANGUAGE; ?>">
	<INPUT type="hidden" NAME="SUBSCRIPTION_ID" value="<?php echo $SUBSCRIPTION_ID; ?>">
	<INPUT type="hidden" NAME="SUB_AMOUNT" value="<?php echo $SUB_AMOUNT; ?>"> 
	<INPUT type="hidden" NAME="SUB_COM" value="<?php echo $SUB_COM; ?>">
	<INPUT type="hidden" NAME="SUB_ORDERID" value="<?php echo $SUB_ORDERID; ?>">
	<INPUT type="hidden" NAME="ORDERID" value="<?php echo $ORDERID; ?>" >
	<INPUT type="hidden" NAME="SUB_PERIOD_UNIT" value="<?php echo $SUB_PERIOD_UNIT; ?>">
	<INPUT type="hidden" NAME="SUB_PERIOD_NUMBER" value="<?php echo $SUB_PERIOD_NUMBER; ?>">
	<INPUT type="hidden" NAME="SUB_PERIOD_MOMENT" value="<?php echo $SUB_PERIOD_MOMENT; ?>">
	<INPUT type="hidden" NAME="SUB_STARTDATE" value="<?php echo $SUB_STARTDATE; ?>">
	<INPUT type="hidden" NAME="SUB_ENDDATE" value="<?php echo $SUB_ENDDATE; ?>">
	<INPUT type="hidden" NAME="SUB_STATUS" value="<?php echo $SUB_STATUS; ?>">
	<INPUT type="hidden" NAME="SUB_COMMENT" value="<?php echo $SUB_COMMENT; ?>">
	<INPUT type="hidden" NAME="SHASIGN" value="<?php echo $SHASIGN; ?>" >
	<INPUT type="hidden" NAME="ACCEPTURL" VALUE="<?php echo $ACCEPTURL; ?>">
	<INPUT type="hidden" NAME="DECLINEURL" VALUE="<?php echo $DECLINEURL; ?>">
	<INPUT type="hidden" NAME="EXCEPTIONURL" VALUE="<?php echo $EXCEPTIONURL; ?>">
	<INPUT type="hidden" NAME="CANCELURL" VALUE="<?php echo $CANCELURL; ?>">
	<INPUT type="hidden" NAME="BACKURL" VALUE="<?php echo $BACKURL; ?>">
	<!-- miscellanous -->
	<INPUT type="hidden" NAME="HOMEURL" VALUE="<?php echo $HOMEURL; ?>">
	<INPUT type="hidden" NAME="CATALOGURL" VALUE="<?php echo $CATALOGURL; ?>">
	<INPUT type="hidden" NAME="CN" value="<?php echo $CN; ?>">
	<INPUT type="hidden" name="EMAIL" value="<?php echo $EMAIL; ?>">
	
	<input type="submit" value="Submit" id=submit name=submit>
	</form>
	</body>
	</html>