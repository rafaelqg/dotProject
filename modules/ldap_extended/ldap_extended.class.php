<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI;
require_once $AppUI->getModuleClass('contacts');
require_once $AppUI->getModuleClass('admin');
require_once $AppUI->getSystemClass('dp');




class CLDAPExtended extends CDpObject {
		//Default values are test purpose only. It is overwrite on constructor.
		//replace such values from some configuration data
		var $ldap_host;
		var $ldap_port;
		var $ldap_version;
		//credatials for login
		var $ldap_dn;
		var $ldap_password;
		var $ldap_search_user;
		
		
		function __construct() {
			parent::__construct('ldap_extended', 'ldap_extended_id');
			global $dPconfig;			
			$this->ldap_host = $dPconfig['ldap_host'];//"ldap.forumsys.com";
			$this->ldap_port = $dPconfig['ldap_port'];//"389"
			$this->ldap_version= $dPconfig['ldap_version'];//"3"
			$this->ldap_dn =  $dPconfig['ldap_base_dn'];// "cn=read-only-admin,dc=example,dc=com"
			$this->ldap_search_user= $dPconfig['ldap_search_user'];//"admin"
			$this->ldap_password = $dPconfig['ldap_search_pass'];//"password"
		}
		
		
		public function printLDAPParameters(){
			echo "<br /><pre>";
			print_r($this);
			echo "</pre><br />";
		}
	
		
	public function getDotProjectRoles(){
		$q = new DBQuery();
		$q->addQuery("value");
		$q->addTable("gacl_aro_groups");
		$sql = $q->prepare();
		$records= db_loadList($sql);
		$roles=array();
		foreach($records as $record){
			array_push($roles,$record[0]);
		}
		return $roles;
	}
	
	public function getDotProjectUsers(){
		$q = new DBQuery();
		$q->addQuery("user_username");
		$q->addTable("users");
		$sql = $q->prepare();
		$records= db_loadList($sql);
		$users=array();
		foreach($records as $record){
			array_push($users,$record[0]);
		}
		return $users;
	}
	
	
	public function deleteRoleFromUser($user_name,$role_name){
		
		global $AppUI;
		$perms =& $AppUI->acl();
		
		$userIdPermissions=-1;
		$user_id=-1;
		$q = new DBQuery();
		$q->addQuery("id,value");
		$q->addTable("gacl_aro");
		$q->addWhere("name = '"  . stripslashes($user_name) . "'");
		$sql = $q->prepare();
		$records= db_loadList($sql);
		
		foreach($records as $record){
			$userIdPermissions= $record[0];
			$user_id=$record[1];
		}
		 
		 if($userIdPermissions != -1){
			
			$q = new DBQuery();
			$q->addQuery("id");
			$q->addTable("gacl_aro_groups");
			$q->addWhere("name = '"  . stripslashes($role_name) . "' or value='".stripslashes($role_name)."'");
			$sql = $q->prepare();
			//echo $sql; 
			$records= db_loadList($sql);
			foreach($records as $record){
				$role_id= $record[0];
				$perms->deleteUserRole($role_id, $user_id);	
				//echo "Role " . $role_id ." deleted for user(" . $user_name . ")". $userIdPermissions;
			 }	 
				 			
		}else{
			//echo "Roles NOT deleted for user(" . $user_name . ")". $userIdPermissions;
		}	
	}
	
	public function addRoleToUser($user_name, $role_name){
	
		global $AppUI;
		//SELECT group_id, aro_id FROM dotproject_ldap.dotp_gacl_groups_aro_map;
		//SELECT id, name FROM dotproject_ldap.dotp_gacl_aro;
		//SELECT id,name,value FROM dotproject_ldap.dotp_gacl_aro_groups;
		
		$groupdId=-1;
		$q = new DBQuery();
		$q->addQuery("id");
		$q->addTable("gacl_aro_groups");
		$q->addWhere("name = '"  . stripslashes($role_name) . "' or value='".stripslashes($role_name)."'");
		$sql = $q->prepare();
		$records= db_loadList($sql);
		foreach($records as $record){
			$groupdId= $record[0];
		 }
			
		$userIdPermissions=-1;
		$user_id=-1;
		$q = new DBQuery();
		$q->addQuery("id,value");
		$q->addTable("gacl_aro");
		$q->addWhere("name = '"  . stripslashes($user_name) . "'");
		$sql = $q->prepare();
		$records= db_loadList($sql);
		foreach($records as $record){
			$userIdPermissions= $record[0];
			$user_id=$record[1];
		 }
		
		if($userIdPermissions != -1 && $groupdId != -1){
			/*
			$q = new DBQuery();
			$q->addTable('gacl_groups_aro_map');
			$q->addInsert('group_id', $groupdId);
			$q->addInsert('aro_id', $userIdPermissions);
			$q->exec();
			$q->clear();
			*/
			
			$perms =& $AppUI->acl();
			$user_role=$groupdId;
			if ($perms->insertUserRole($user_role, $user_id)) {
				$AppUI->setMsg('added', UI_MSG_OK, true);
				$public_contact=true;
				if ($public_contact) {
					// Mark contact as public
					$obj = new CUser();
					$contact = new CContact();
					$obj->load($user_id);
					if ($contact->load($obj->user_contact)) {
						$contact->contact_private = 0;
						$contact->store();
					}
				}
			} else {
				$AppUI->setMsg('failed to add role', UI_MSG_ERROR);
			}
		}	 
	}
	
	//based on: https://samjlevy.com/php-ldap-membership/
	//http://www.forumsys.com/tutorials/integration-how-to/ldap/online-ldap-test-server/)
	//To be utilized in LDAP when ismemberof (posix) is available
	public  function get_groups($user) {
		// Active Directory server
		$ldap_host = $this->ldap_host;//replace for a dynamic value
		$ldap_port=$this->ldap_port;
		
	 
		// Active Directory user for querying
		$query_user = $user."@".$ldap_host;
		$password = $this->ldap_password;
		
		// Active Directory DN, base path for our querying user
		$ldap_dn =$this->ldap_dn;
		

		// Connect to AD
		//$ldap = ldap_connect($ldap_host,$ldap_port) or die("Could not connect to LDAP");
		$ldap = ldap_connect($ldap_host,$ldap_port) or die("Could not connect to LDAP");
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_version);
		ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
		
		if(ldap_bind($ldap,$ldap_dn,$password)){
			echo "Bind LDAP successfully.";
		}else{
			die("Could not bind to LDAP");
		} 
		
		// Search AD
		$results = ldap_search($ldap,$ldap_dn,"(samaccountname=$user)",array("memberof","primarygroupid"));
		$entries = ldap_get_entries($ldap, $results);
		
		// No information found, bad user
		print_r($entries);
		if($entries['count'] == 0){
			echo "<br />No group found querying for memberof attribute.<br />";
			return false;
		} 
		
		// Get groups and primary group token
		$output = $entries[0]['memberof'];
		$token = $entries[0]['primarygroupid'][0];
		
		// Remove extraneous first entry
		array_shift($output);
		
		// We need to look up the primary group, get list of all groups
		$results2 = ldap_search($ldap,$ldap_dn,"(objectcategory=group)",array("distinguishedname","primarygrouptoken"));
		$entries2 = ldap_get_entries($ldap, $results2);
		
		// Remove extraneous first entry
		array_shift($entries2);
		
		// Loop through and find group with a matching primary group token
		foreach($entries2 as $e) {
			if($e['primarygrouptoken'][0] == $token) {
				// Primary group found, add it to output array
				$output[] = $e['distinguishedname'][0];
				// Break loop
				break;
			}
		}
	 
		return $output;
	}
	
	/**
	parameter group: its identification on LDAP as : "ou=mathematicians,dc=example,dc=com"
	**/
	public function getUsersByGroup($group) {
		$users= array();//return variable. Here will be added all users found on this group
		// Connect to AD
		$ldap = ldap_connect($this->ldap_host,$this->ldap_port) or die("Could not connect to LDAP");
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_version);///must be version 3
		ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
		if(ldap_bind($ldap,$this->ldap_dn,$this->ldap_password)){
			echo "<br />Bind LDAP successfully.<br />";
		}else{
			die("Could not bind to LDAP");
		} 
		
		//search for queried group
		// Search AD
		echo "<br />Looking for LDAP group  \"" . $group . "\"<br />";
		$results = ldap_search($ldap,$group,"(cn=*)" );
		if($results!=""){
			$entries = ldap_get_entries($ldap, $results);
			
			// No information found, bad user
			if($entries['count'] >0){
			// Get groups and primary group token
				$output = $entries[0]['uniquemember'];		
				foreach ($output as $user){
					$commaPos=strpos($user,",");
					$userName=substr($user,4,$commaPos-4);
					array_push($users,$userName);
				}
			} 
		}else{
			echo "<br />Group \"".$group ."\" not found on LDAP<br />";
		}
		return $users;
	}
	
}