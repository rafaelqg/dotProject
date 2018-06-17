<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
$AppUI->savePlace();
 require_once DP_BASE_DIR ."/modules/ldap_extended/ldap_extended.class.php";
 CLDAPExtended::deleteRolesFromUser("rafael");
 CLDAPExtended::addRoleToUser("rafael", "Just projects");
 
 ?>