<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}

// MODULE CONFIGURATION DEFINITION
$config = array(
	'mod_name' => 'Two-Factor Authentication',
	'mod_version' => '1.0.0',
	'mod_directory' => 'totp',
	'mod_setup_class' => 'CSetup2FA',
	'mod_type' => 'user',
	'mod_ui_name' => 'Two-Factor Auth',
	'mod_ui_icon' => 'lock.png',
	'mod_description' => 'A module to enable Two-Factor Authentication using TOTP',
	'mod_config' => true,
	'permissions_item_table' => '',
	'permissions_item_field' => '',
	'permissions_item_label' => ''
);

if (@$a == 'setup') {
	echo dPshowModuleConfig($config);
}

class CSetup2FA {
	function install() {
		$sql = "ALTER TABLE `users` 
               ADD COLUMN `user_totp_secret` VARCHAR(32) NULL DEFAULT NULL COMMENT 'TOTP secret key',
               ADD COLUMN `user_totp_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether TOTP 2FA is enabled'";
		
		$q = new DBQuery;
		if (!$q->exec($sql)) {
			return false;
		}

		return null;
	}

	function remove() {
		$sql = "ALTER TABLE `users` 
               DROP COLUMN `user_totp_secret`,
               DROP COLUMN `user_totp_enabled`";
		
		$q = new DBQuery;
		if (!$q->exec($sql)) {
			return false;
		}

		return null;
	}

	function upgrade($old_version) {
		return null;
	}
}