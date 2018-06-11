<?php
if (!defined('DP_BASE_DIR')) {
    die('You should not access this file directly.');
}
require_once (DP_BASE_DIR . '/modules/tasks/tasks.class.php');
require_once (DP_BASE_DIR . "/modules/initiating/authoriziation_workflow.class.php");
$initiating_id = intval(dPgetParam($_POST, 'initiating_id', 0));
$del = intval(dPgetParam($_POST, 'del', 0));
$completed = intval(dPgetParam($_POST, 'initiating_completed', 0));
$approved = intval(dPgetParam($_POST, 'initiating_approved', 0));
$authorized = intval(dPgetParam($_POST, 'initiating_authorized', 0));
global $db, $AppUI;

$not = dPgetParam($_POST, 'notify', '0');
if ($not != '0')
    $not = '1';

$obj = new CInitiating();
if (!$obj->bind($_POST)) {
    $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
    $AppUI->redirect();
}


$df=str_replace("%","",$AppUI->getPref('SHDATEFORMAT')); 
$datetime = new DateTime();
// convert dates to SQL format first
if ($obj->initiating_start_date) {
	$date = $datetime->createFromFormat( $df, dPgetParam($_POST, 'start_date', 0));//'d/m/Y'
    $obj->initiating_start_date = $date->format("Y-m-d");
}

if ($obj->initiating_end_date) {
    $date = $datetime->createFromFormat( $df, dPgetParam($_POST, 'end_date', 0));//'d/m/Y'
    $obj->initiating_end_date = $date->format("Y-m-d");
}

//update_milestones
$total_milestones = intval(dPgetParam($_POST, 'total_milestones', 0));
for($i=0;$i<$total_milestones ;$i++){
	$idMS=dPgetParam($_POST, 'milestone_id_'.$i, 0);
	$ms = new CTask();
	$ms->load($idMS);
	$ms->task_name=dPgetParam($_POST, 'milestone_name_'.$i, 0);
	if(dPgetParam($_POST, 'milestone_date_'.$i, 0) !=""){
		$date = $datetime->createFromFormat( $df, dPgetParam($_POST, 'milestone_date_'.$i, 0));//'d/m/Y'
		$ms->task_start_date = $date->format("Y-m-d");	
	}else{
		$ms->task_start_date=NULL;
	}
	$ms->store();
}


if ($initiating_id) {
    $obj->_message = 'updated'; 
} else {
    $obj->initiating_date_create = str_replace("'", '', $db->DBTimeStamp(time()));
    $obj->initiating_create_by = $AppUI->user_id;
    if ($completed) {
        $obj->initiating_completed = 1;
    }
    if ($approved) {
        $obj->initiating_approved = 1;
    }
    if ($authorized) {
        $obj->initiating_authorized = 1;
    }
    $obj->_message = 'added';
}

//create new milestone
if(dPgetParam($_POST, 'new_milestone', 0) == "1"){
    $ms = new CTask();
	$ms->task_id=null;
	$ms->task_project = $obj->project_id;
	$ms->task_name = "";
	$ms->task_duration = 0;
	$ms->task_milestone=1;
	$ms->task_start_date = NULL;
	$ms->task_end_date = NULL;
	$ms->task_creator=$AppUI->user_id;
	db_insertObject('tasks', $ms, 'task_id');
	$task_id=$ms->task_id;
	$ms->load($task_id);
	$ms->task_parent=$task_id;
	$ms->store();
}

//delete milestone
if(dPgetParam($_POST, 'delete_milestone_id', 0) != "0"){
	$ms = new CTask();
	$ms->load(dPgetParam($_POST, 'delete_milestone_id', 0));
	$ms->delete();
}

// delete the item
if ($del) {
    $obj->load($initiating_id);
    if (($msg = $obj->delete())) {
        $AppUI->setMsg($msg, UI_MSG_ERROR);
        $AppUI->redirect();
    } else {
        if ($not == '1')
            $obj->notify();
        $AppUI->setMsg("deleted", UI_MSG_ALERT, true);
        $AppUI->redirect("m=initiating");
    }
}

//if autorized then copy values to project
if ($_POST["action_authorized_performed"] == "1") {
    require_once (DP_BASE_DIR . "/modules/projects/projects.class.php");
    $projectId=$_POST["project_id"];
    $projectObj = new CProject();
    $projectObj->load($projectId);
    $projectObj->project_start_date=$obj->initiating_start_date;
    $projectObj->project_end_date=$obj->initiating_end_date;
    $projectObj->project_owner=$obj->initiating_manager;
    $projectObj->project_target_budget=$obj->initiating_budget;
    $projectObj->project_status=2;// set in the planning phase
    $projectObj->store();
}

if($initiating_id>0){
//update values of authorization woerkflow
    $approvalWorkflow= new CAuthorizationWorkflow();
    $resultAWLoad=$approvalWorkflow->load($initiating_id);
	//new object - force its insertion, to the store method update that. (necessary because it is an weak entity, using a pre-defined ky from the strong entity)
	if($resultAWLoad!=1){
		$approvalWorkflow->insert();
	}	
    if( is_null($approvalWorkflow->draft_when)){    
        $approvalWorkflow->draft_when=date("Y-m-d H:i:s");
        $approvalWorkflow->draft_by=$AppUI->user_id;

    }
    
    if($obj->initiating_completed==1 && is_null($approvalWorkflow->completed_when) ){
		$approvalWorkflow->completed_when=date("Y-m-d H:i:s");
        $approvalWorkflow->completed_by=$AppUI->user_id;
    }else if ($obj->initiating_completed!=1){
        $approvalWorkflow->completed_when=null;
        $approvalWorkflow->completed_by= null;
    }
    
    if($obj->initiating_approved==1 && is_null($approvalWorkflow->approved_when) ){
        $approvalWorkflow->approved_when=date("Y-m-d H:i:s");
        $approvalWorkflow->approved_by=$AppUI->user_id;
    }else if($obj->initiating_approved!=1 ){
        $approvalWorkflow->approved_when=null;
        $approvalWorkflow->approved_by=null;
    }
    
    if($obj->initiating_authorized==1 && is_null($approvalWorkflow->authorized_when) ){
        $approvalWorkflow->authorized_when=date("Y-m-d H:i:s");
        $approvalWorkflow->authorized_by=$AppUI->user_id;
    }else if($obj->initiating_authorized!=1){
        $approvalWorkflow->authorized_when=null;
        $approvalWorkflow->authorized_by=null;
    }
    
    $approvalWorkflow->update();
    
}
    
     

if (($msg = $obj->store())) {
    $AppUI->setMsg($msg, UI_MSG_ERROR);
} else {
    $obj->load($obj->initiating_id);
    if ($not == '1')
        $obj->notify();
    $AppUI->setMsg($AppUI->_("Project charter included"), UI_MSG_OK, true);
}
$AppUI->redirect();