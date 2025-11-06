<?php /* $Id$ */ 
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo $dPconfig['page_title'];?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset($locale_char_set) ? $locale_char_set : 'UTF-8';?>" />
	<title><?php echo $dPconfig['company_name'];?> :: dotProject Login</title>
	<meta http-equiv="Pragma" content="no-cache" />
	<meta name="Version" content="<?php echo @$AppUI->getVersion();?>" />
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css" media="all" />
	<style type="text/css" media="all">@import "./style/<?php echo $uistyle;?>/main.css";</style>
	<link rel="shortcut icon" href="./style/<?php echo $uistyle;?>/images/favicon.ico" type="image/ico" />
</head>

<body style="background-color: #f0f0f0" onload="document.loginform.totp_code.focus();">
<br /><br /><br /><br />
<?php //please leave action argument empty ?>
<form method="post" action="" name="loginform">
<table align="center" border="0" width="250" cellpadding="6" cellspacing="0" class="std">
<input type="hidden" name="login" value="<?php echo time();?>" />
<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
<input type="hidden" name="userdata" value="<?php echo htmlspecialchars($_REQUEST['userdata'] ?? '');?>" />
<input type="hidden" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? '');?>" />
<input type="hidden" name="password" value="<?php echo htmlspecialchars($_POST['password'] ?? '');?>" />

<tr>
	<th colspan="2"><em><?php echo $dPconfig['company_name'];?></em></th>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_('Two-Factor Authentication');?>:</td>
	<td align="left" nowrap><input type="text" size="6" name="totp_code" class="text" maxlength="6" pattern="\d{6}" required /></td>
</tr>
<tr>
	<td align="left" nowrap><a href="javascript: void(0);" onclick="javascript:window.location='./index.php?logout=-1'"><?php echo $AppUI->_('cancel');?></a></td>
	<td align="right" valign="bottom" nowrap><input type="submit" name="submit" value="<?php echo $AppUI->_('verify');?>" class="button" /></td>
</tr>
<tr>
	<td colspan="2" align="center">Please enter the 6-digit code from your authenticator app</td>
</tr>
</table>
</form>
<div align="center">
<?php
	if (function_exists('styleCopyright')) {
		echo styleCopyright();
	}
?>
</div>
</body>
</html>