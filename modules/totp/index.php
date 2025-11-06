<?php
if (!defined('DP_BASE_DIR')) {
    die('You should not access this file directly.');
}

$AppUI->savePlace();

// Check permissions
if (!$canEdit) {
    $AppUI->redirect("m=public&a=access_denied");
}

// Include the TOTP class
require_once DP_BASE_DIR . '/classes/totp.class.php';

// Load user's current TOTP settings
$q = new DBQuery;
$q->addTable('users');
$q->addQuery('user_totp_enabled, user_totp_secret');
$q->addWhere('user_id = ' . (int)$AppUI->user_id);
$totpSettings = $q->loadHash();

// Handle form submission
if (isset($_POST['enable_totp'])) {
    $totp = new TOTP();
    $secret = $totp->generateSecret();
    
    $q = new DBQuery;
    $q->addTable('users');
    $q->addUpdate('user_totp_secret', $secret);
    $q->addUpdate('user_totp_enabled', 1);
    $q->addWhere('user_id = ' . (int)$AppUI->user_id);
    if (!$q->exec()) {
        $AppUI->setMsg('Failed to enable 2FA', UI_MSG_ERROR);
    } else {
        $totpSettings['user_totp_enabled'] = 1;
        $totpSettings['user_totp_secret'] = $secret;
        $AppUI->setMsg('Two-Factor Authentication Enabled', UI_MSG_OK);
    }
} else if (isset($_POST['disable_totp'])) {
    $q = new DBQuery;
    $q->addTable('users');
    $q->addUpdate('user_totp_secret', 'NULL', false, true);
    $q->addUpdate('user_totp_enabled', 0);
    $q->addWhere('user_id = ' . (int)$AppUI->user_id);
    if (!$q->exec()) {
        $AppUI->setMsg('Failed to disable 2FA', UI_MSG_ERROR);
    } else {
        $totpSettings['user_totp_enabled'] = 0;
        $totpSettings['user_totp_secret'] = null;
        $AppUI->setMsg('Two-Factor Authentication Disabled', UI_MSG_OK);
    }
}
?>

<script type="text/javascript">
function validateTOTP() {
    var code = document.getElementById('verify_code').value;
    if (code.length !== 6 || !/^\d+$/.test(code)) {
        alert('Please enter a valid 6-digit code');
        return false;
    }
    return true;
}
</script>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
    <tr>
        <td width="100%" valign="top">
            <table border="0" cellpadding="2" cellspacing="1" width="100%" class="std">
                <tr>
                    <th colspan="2"><?php echo $AppUI->_('Two-Factor Authentication Settings');?></th>
                </tr>
                <tr>
                    <td colspan="2">
                        Two-Factor Authentication adds an extra layer of security to your account by requiring a verification code from your phone in addition to your password when logging in.
                    </td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td>
                        <?php 
                        if ($totpSettings['user_totp_enabled']) {
                            echo '<strong style="color: green;">Enabled</strong>';
                        } else {
                            echo '<strong style="color: red;">Disabled</strong>';
                        }
                        ?>
                    </td>
                </tr>
                <?php if (!$totpSettings['user_totp_enabled']) { ?>
                <tr>
                    <td colspan="2">
                        <form name="totp" action="?m=totp" method="post">
                            <input type="submit" class="button" name="enable_totp" value="Enable Two-Factor Authentication" />
                        </form>
                    </td>
                </tr>
                <?php } else { ?>
                <tr>
                    <td colspan="2">
                        <form name="totp" action="?m=totp" method="post" onsubmit="return confirm('Are you sure you want to disable Two-Factor Authentication? This will make your account less secure.');">
                            <input type="submit" class="button" name="disable_totp" value="Disable Two-Factor Authentication" />
                        </form>
                    </td>
                </tr>
                <?php
                    // If 2FA was just enabled, show QR code
                    if (isset($_POST['enable_totp'])) {
                        $totp = new TOTP($totpSettings['user_totp_secret']);
                        $qrCodeUrl = $totp->getProvisioningUri($AppUI->user_username);
                        ?>
                        <tr>
                            <td colspan="2" align="center">
                                <p><strong>Setup Instructions:</strong></p>
                                <p>1. Install Google Authenticator or any other TOTP app on your phone</p>
                                <p>2. Scan this QR code with your authenticator app:</p>
                                <img src="https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=<?php echo urlencode($qrCodeUrl); ?>" />
                                <p>Or manually enter this secret key: <code><?php echo chunk_split($totpSettings['user_totp_secret'], 4, ' '); ?></code></p>
                                <p>3. Enter the 6-digit code shown in your app to verify setup:</p>
                                <form name="verify" action="?m=totp" method="post" onsubmit="return validateTOTP();">
                                    <input type="text" id="verify_code" name="verify_code" size="6" maxlength="6" pattern="\d{6}" required />
                                    <input type="submit" class="button" name="verify_totp" value="Verify" />
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                } ?>
            </table>
        </td>
    </tr>
</table>