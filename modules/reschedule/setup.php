<?php
if (!defined('DP_BASE_DIR')) {
  die('You should not access this file directly.');
}

$config = array();
$config['mod_name'] = 'reschedule';
$config['mod_version'] = '1.0';
$config['mod_directory'] = 'reschedule';
$config['mod_setup_class'] = 'CSetupReschedule'; 
$config['mod_type'] = 'user';
$config['mod_config'] = false;
$config['mod_ui_name'] = 'Reschedule';
$config['mod_ui_icon'] = 'applet3-48.png';
$config['mod_description'] = "Reschedule project tasks and dates";

if (@$a == 'setup') {
	echo dPshowModuleConfig($config);
}

class CSetupReschedule {

     function install() {
		/*
		$q = new DBQuery();
		$q->createTable('ldap_extended');
		$q->createDefinition("");
		$q->exec($sql);
		*/
 }


   function remove() { 
  	/*
	$q = new DBQuery();
  	$q->dropTable('ldap_extended');
  	$q->exec();
	*/
	return true;
	
 }
 
  function upgrade($version = 'all') {
	return true;
  }
  
  function configure() {
	return true;
  }  

}
