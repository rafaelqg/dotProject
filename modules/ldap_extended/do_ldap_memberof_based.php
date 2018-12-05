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
?>
Processing LDAP using "member_of" attribute:<br /><br />
<?php
echo "<br />Dotproject users:<br /><pre>";
print_r($users);
echo "</pre><br /><hr /><br />";
	foreach($users as $user){
		//missing step to clear all user roles that are LDAP binded.
		echo "<br />Processing user: ". $user."<br/>";
		
		$ldap_search_output=$ldapExt->get_groups($user); 
		//$TEST_ldap_search_output=array ( 0 => array ("memberof" => array ( 0 => "cn=palo_it_admin,ou=Groups,dc=debortoli,dc=private",1 => "cn=DP_it,ou=Groups,dc=debortoli,dc=private",2 => "cn=risk_management,ou=Groups,dc=debortoli,dc=private", 3 => "cn=DP_quality,ou=Groups,dc=debortoli,dc=private" ))); 
		//print_r($TEST_ldap_search_output);
		$prefix=$ldapExt->ldap_dp_role_prefix;//"DP_";
		//$groups=getUserDPRoles($prefix,$TEST_ldap_search_output);
		$groups=getUserDPRoles($prefix,$ldap_search_output);
	
		//clean groups before add new ones
		$ldapExt->deleteRolesNotOnLDAPAnymore($user,$prefix);
		
		if(sizeof($groups)==0 || !$groups){
			echo "<br />No group found on LDAP<br/>";
		}else{
			echo "<br/>Groups<br />";
			print_r($groups);
			foreach($groups as $group){
				$ldapExt->addRoleToUser($user, $group);
			}
		}	
	}
die();


function getUserDPRoles($prefix,$ldap_search_output){								
	//foreach($ldap_search_output as $i){
	//	echo "<br/>foo: ".$i;
	//}
	//echo "$ldap_search_output: ".$ldap_search_output;
	//$member_of_list=$ldap_search_output;
	$roles=array();
	if( isset($ldap_search_output)){
		foreach($ldap_search_output as $ldap_member_of_string){
			//echo $ldap_member_of_string;
			$posCN=strpos($ldap_member_of_string,"cn=");
			//echo "POS CN:".$posCN;
			if($posCN!==false){
				$posComma=strpos($ldap_member_of_string,",",$posCN);
				//echo "POS Comma:".$posComma;
				if($posComma!==false){
					$cn=substr ( $ldap_member_of_string, $posCN+3,$posComma-3);// $posComma
					//echo "CN:". $cn . "::"; 
					$posPrefix=strpos($cn,$prefix);
					if($posPrefix!==false){
						$dpRoleName=substr ( $cn, $posPrefix, strlen($cn));
						echo "<br/>CN: " . $dpRoleName;
						array_push($roles,$dpRoleName);
					}
				}
			}
		}
	}
	return $roles;
	
}

 ?>
