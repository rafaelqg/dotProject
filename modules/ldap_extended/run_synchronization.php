<?php
$syncType=$dPconfig['ldap_variable_for_retrieve_roles_list'];
if(strtolower($syncType)=="memberof"){ 
	require_once DP_BASE_DIR ."/modules/ldap_extended/do_ldap_memberof_based.php";
}else{
	require_once DP_BASE_DIR ."/modules/ldap_extended/do_ldap_group_membership_based.php";
}
?>