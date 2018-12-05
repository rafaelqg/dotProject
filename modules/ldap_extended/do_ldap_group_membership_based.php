<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
//$AppUI->savePlace();
require_once DP_BASE_DIR ."/modules/ldap_extended/ldap_extended.class.php";

$ldapExt= new CLDAPExtended();
//$ldapExt->printLDAPParameters();
$dpRoles=$ldapExt->getDotProjectRoles();
$users=$ldapExt->getDotProjectUsers();
$prefix=$ldapExt->ldap_dp_role_prefix;//"DP_";
?>
Processing LDAP membership based:<br /><br />
<?php
echo "<br />Dotproject users:<br /><pre>";
print_r($users);
echo "</pre><br /><hr /><br />";


foreach($dpRoles as $role){
	echo "<br />";
	echo "Searching for group: ".$role . "<br />";
	$usersInGroup=$ldapExt->getUsersByGroup("ou=$role,dc=example,dc=com");//line has to be adapted to get group identification from some configuration data
	if(count($usersInGroup)>0){ 
		//remove LDAP roles from users, ensuring users excluded from group will not remain with it.
		$ldapExt->deleteRolesNotOnLDAPAnymore($user,$prefix);
		//add the role for all users in group
		echo "Group members: ". print_r ($usersInGroup) . "<br />";
		foreach($usersInGroup as $user){
			echo "Checking user: $user";
			if(in_array($user, $users)){//check if group member is a dotProject user
				echo "(on group)";	
				$ldapExt->addRoleToUser($user, $role);
			}else{
				echo "(not on group)";
			}
			echo "<br />";
		}
	}else{
		echo "No members found on this group.";
	}
	echo "<br /><hr /><br />";
}
die();

 ?>