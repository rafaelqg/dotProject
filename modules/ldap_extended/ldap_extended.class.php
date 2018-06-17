<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI;
require_once $AppUI->getSystemClass('dp');


class CLDAPExtended extends CDpObject {
/*
  var $ldap_extended_id = NULL;
  var $user_id = NULL;
	


	
	function __construct() {
		parent::__construct('ldap_extended', 'ldap_extended_id');
	}
	
 
	function check() {
	// ensure the integrity of some variables
		$this->ldap_extended_id = intval($this->ldap_extended_id);

		return NULL; // object is ok
	}

	function delete($oid = NULL, $history_desc = '', $history_proj = 0) {
		global $dPconfig;
	
	}
	*/	
	//SELECT * FROM   information_schema.tables WHERE  TABLE_SCHEMA = 'dotproject_ldap' order by UPDATE_TIME desc LIMIT 0, 1000
	
	public static function deleteRolesFromUser($user_name){
		$userIdPermissions=-1;
		$q = new DBQuery();
		$q->addQuery("id");
		$q->addTable("gacl_aro");
		$q->addWhere("name = '"  . stripslashes($user_name) . "'");
		$sql = $q->prepare();
		$records= db_loadList($sql);
		foreach($records as $record){
			$userIdPermissions= $record[0];
		 }
		 
		 if($userIdPermissions != -1){
			  $q = new DBQuery();
				$q->setDelete("gacl_groups_aro_map");
				$q->addWhere("aro_id=" . $userIdPermissions);
				$q->exec();
				$q->clear();
			echo "Roles deleted for user(" . $user_name . ")". $userIdPermissions;
		}else{
			echo "Roles NOT deleted for user(" . $user_name . ")". $userIdPermissions;
		}	
	}
	
	public static function addRoleToUser($user_name, $role_name){
	

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
		$q = new DBQuery();
		$q->addQuery("id");
		$q->addTable("gacl_aro");
		$q->addWhere("name = '"  . stripslashes($user_name) . "'");
		$sql = $q->prepare();
		$records= db_loadList($sql);
		foreach($records as $record){
			$userIdPermissions= $record[0];
		 }
		
		if($userIdPermissions != -1 && $groupdId != -1){
			$q = new DBQuery();
			$q->addTable('gacl_groups_aro_map');
			$q->addInsert('group_id', $groupdId);
			$q->addInsert('aro_id', $userIdPermissions);
			$q->exec();
			$q->clear();
			echo "Role ". $groupdId. "(" .$role_name. ") added to user (" . $user_name . ")". $userIdPermissions;
		}else{
			echo "Role ". $groupdId. "(" .$role_name. ") NOT added to user (" . $user_name . ")". $userIdPermissions;
		}		 
	}
}