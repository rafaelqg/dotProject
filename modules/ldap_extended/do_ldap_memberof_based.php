<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
$AppUI->savePlace();
require_once DP_BASE_DIR ."/modules/ldap_extended/ldap_extended.class.php";

$ldapExt= new CLDAPExtended();
//$ldapExt->printLDAPParameters();
$dpRoles=$ldapExt->getDotProjectRoles();
$users=$ldapExt->getDotProjectUsers();
?>
Processing LDAP using "memberof" attribute:<br /><br />
<?php
echo "<br />Dotproject users:<br /><pre>";
print_r($users);
echo "</pre><br /><hr /><br />";
	foreach($users as $user){
		//missing step to clear all user roles that are LDAP binded.
		echo "<br />Processing user: ". $user."<br/>";
		$groups=$ldapExt->get_groups($user);
		if(sizeof($groups)==0 || !$groups){
			echo "<br />No group found on LDAP<br/>";
		}else{
			echo "Groups: ". print_r($groups);
			
			foreach($groups as $group){
				$ldapExt->addRoleToUser($user, $group);
			}
		}	
	}
die();

 ?>