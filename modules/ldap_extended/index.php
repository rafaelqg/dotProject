<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
$AppUI->savePlace();


$user_groups=CLDAPExtended::get_groups("gauss");
print_r($user_groups);

/*
 require_once DP_BASE_DIR ."/modules/ldap_extended/ldap_extended.class.php";
 CLDAPExtended::deleteRolesFromUser("rafael");
 CLDAPExtended::addRoleToUser("rafael", "Just projects");
 */
 ?>