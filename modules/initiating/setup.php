<?php
if (!defined('DP_BASE_DIR')) {
  die('You should not access this file directly.');
}

$config = array();
$config['mod_name'] = 'initiating';
$config['mod_version'] = '1.1';
$config['mod_directory'] = 'initiating';
$config['mod_setup_class'] = 'CSetupInitiating'; 
$config['mod_type'] = 'user';
$config['mod_config'] = false;
$config['mod_ui_name'] = 'Initiating';
$config['mod_ui_icon'] = 'applet3-48.png';
$config['mod_description'] = "Initiating process group implementation";

if (@$a == 'setup') {
	echo dPshowModuleConfig($config);
}

class CSetupInitiating {

     function install() {
		$q = new DBQuery();
		$q->createTable('initiating');
		$q->createDefinition("(
	  initiating_id int(11) NOT NULL AUTO_INCREMENT ,
	  initiating_title varchar(255) NOT NULL,
	  initiating_manager int(11) NOT NULL,
	  initiating_create_by int(11) NOT NULL,
	  initiating_date_create datetime NOT NULL,
	  initiating_justification varchar(2000) DEFAULT NULL,
	  initiating_objective varchar(2000) DEFAULT NULL,
	  initiating_expected_result varchar(2000) DEFAULT NULL,
	  initiating_premise varchar(2000) DEFAULT NULL,
	  initiating_restrictions varchar(2000) DEFAULT NULL,
	  initiating_budget float DEFAULT 0,
	  initiating_start_date date DEFAULT NULL,
	  initiating_end_date date DEFAULT NULL,
	  initiating_milestone varchar(2000) DEFAULT NULL,
	  initiating_success varchar(2000) DEFAULT NULL,
	  initiating_approved int(1) DEFAULT '0',
	  initiating_authorized int(1) DEFAULT '0',
	  initiating_completed int(1) NOT NULL DEFAULT '0',
	  initiating_approved_comments varchar(2000) DEFAULT NULL,
	  initiating_authorized_comments varchar(2000) DEFAULT NULL,
	  project_id int(11) default null,
	PRIMARY KEY (initiating_id) 
	) ");

		$q->exec($sql);
		
		$q->clear();
		$q = new DBQuery();
		$q->createTable('initiating_stakeholder');
		$q->createDefinition("(
	  initiating_stakeholder_id int(11) NOT NULL AUTO_INCREMENT,
	  initiating_id int(11) NOT NULL,
	  contact_id int(11) NOT NULL,
	  stakeholder_responsibility varchar(100) DEFAULT NULL,
	  stakeholder_interest varchar(100) DEFAULT NULL,
	  stakeholder_power varchar(100) DEFAULT NULL,
	  stakeholder_strategy varchar(100) DEFAULT NULL,
	  PRIMARY KEY (initiating_stakeholder_id) 
	) ");

		$q->exec($sql);
		
			
			
			$q = new DBQuery();
		$q->createTable('authorization_workflow');
		$q->createDefinition("(
	  initiating_id int(11) NOT NULL,
	  draft_byn int(11) DEFAULT NULL,
	  draft_when DATETIME DEFAULT NULL,
	  completed_by int(11) DEFAULT NULL,
	  completed_when DATETIME DEFAULT NULL,
	  approved_by int(11) DEFAULT NULL,
	  approved_when DATETIME DEFAULT NULL,
	  authorized_by int(11) DEFAULT NULL,
	  authorized_when DATETIME DEFAULT NULL,
	  PRIMARY KEY (initiating_id) 
	) ");

		$q->exec($sql);
 }


   function remove() { 
  	
	$q = new DBQuery();
  	$q->dropTable('initiating');
  	$q->exec();
	
	$q = new DBQuery();
  	$q->dropTable('initiating_stakeholder');
  	$q->exec();
	
 }
 
  function upgrade($version = 'all') {
	return true;
  }
  
  function configure() {
	return true;
  }  

}
