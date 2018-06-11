<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI;
require_once $AppUI->getSystemClass('dp');
/**
 * Initiating Class
 */
class CInitiating extends CDpObject {

  var $initiating_id = NULL;
  var $initiating_title = NULL;
  var $initiating_manager = NULL;
  var $initiating_create_by = NULL;
  var $initiating_date_create = NULL;
  var $initiating_justification = NULL;
  var $initiating_objective = NULL;
  var $initiating_expected_result = NULL;
  var $initiating_premise = NULL;
  var $initiating_restrictions = NULL;
  var $initiating_budget = NULL;
  var $initiating_start_date = NULL;
  var $initiating_end_date = NULL;
  var $initiating_milestone = NULL;
  var $initiating_success = NULL;
  var $initiating_approved = NULL;
  var $initiating_authorized = NULL;
  var $initiating_completed = NULL;
  var $initiating_approved_comments = NULL;
  var $initiating_authorized_comments = NULL;
  var $project_id = NULL;
	
  function getStatus() {
        if ($this->initiating_authorized == "1") {
            $status = "Authorized";
        } else if ($this->initiating_approved == "1") {
            $status = "Approved";
        } else if ($this->initiating_completed == "1") {
            $status = "Completed";
        } else {
            $status = "Draft";
        }
        return $status;
    }

	
	function __construct() {
		parent::__construct('initiating', 'initiating_id');
	}
	
 
	function check() {
	// ensure the integrity of some variables
		$this->initiating_id = intval($this->initiating_id);

		return NULL; // object is ok
	}

	function delete($oid = NULL, $history_desc = '', $history_proj = 0) {
		global $dPconfig;
		$this->_message = "deleted";

	// delete the main table reference
		$q = new DBQuery();
		$q->setDelete('initiating');
		$q->addWhere('initiating_id = ' . $this->initiating_id);
		if (!$q->exec()) {
			return db_error();
		}
		return NULL;
	}
        
        static public function  findByProjectId($projectId){
            $initiating=null; 
            $q = new DBQuery();
            $q->addQuery("initiating_id, project_id");
            $q->addTable("initiating");
            $q->addWhere("project_id = "  . $projectId);
            $sql = $q->prepare();
            $project_charters= db_loadList($sql);
	    foreach($project_charters as $project_charter){
                $initiating=new CInitiating();
                $initiating->load($project_charter[0]);
             }
            return $initiating;
        }
		
	public function loadMillestones(){
        $q = new DBQuery();
        $q->addQuery("task_id");
        $q->addTable("tasks");
        $q->addWhere("task_project=". $this->project_id ."  and task_milestone=1 order by task_start_date asc");
        $results = db_loadHashList($q->prepare(true), "task_id");
        $list= array();
        $i=0;
        foreach ($results as $data) {
		   $obj = new CTask();
		   $obj->load($data[0]);
           $list[$i]=$obj;
           $i++;
        }
        return $list;
    }	
		
}