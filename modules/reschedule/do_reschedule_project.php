<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI;

$project_id= $_POST["project_id"];
$start_date= $_POST["new_start_date_".$project_id];
$df = $AppUI->getPref('SHDATEFORMAT');
echo $project_id, $start_date, $df;
$userDateFormat=strtolower($df); 
$dateSeparator= substr($userDateFormat,2,1);
echo "separator: ". $dateSeparator ."<br />";
echo "date format: ". $userDateFormat."<br />";
$dateParts=explode ($dateSeparator,$userDateFormat);
$yearIndex=array_search("%y",$dateParts);
$monthIndex=array_search("%m",$dateParts);
$dayIndex=array_search("%d",$dateParts);
$startDateParts=explode($dateSeparator,$start_date);
//echo  $startDateParts[$yearIndex],$startDateParts[$monthIndex],$startDateParts[$dayIndex]."<br />";
$newProjectStartDateString=$startDateParts[$yearIndex]."-".$startDateParts[$monthIndex]."-".$startDateParts[$dayIndex];
$newProjectStartDate = new DateTime($newProjectStartDateString);
$newProjectEndDate = new DateTime($newProjectStartDateString);
//Get current start and end date of the project
$q = new DBQuery();
$q->addTable('projects','p');
$q->addQuery('project_start_date');
$q->addQuery('project_end_date');
$q->addWhere('project_id='.$project_id);
$sql = $q->prepare();
//echo $sql;
$projects = db_loadList($sql);//$q->loadHashList();
$currentProjectStartDate=null;
$currentProjectEndDate=null;
foreach($projects as $project){
		$currentProjectStartDate = new DateTime($project["project_start_date"]);
		$currentProjectEndDate = new DateTime($project["project_end_date"]);
}
//calculate new project end date
if($currentProjectStartDate != null && $currentProjectEndDate!=null){
	$interval= $currentProjectStartDate->diff($currentProjectEndDate);
	echo $interval->days;
	//echo $interval->format('%R%a days');
	$newProjectEndDate->modify("+". $interval->days. " day");
	echo "New project start date:". $newProjectStartDate->format('Y-m-d')."<br />";	
	echo "New project end date:". $newProjectEndDate->format('Y-m-d')."<br />";	
}
//for each project task	
$q = new DBQuery();
$q->addTable('tasks');
$q->addQuery('task_id');
$q->addQuery('task_name');
$q->addQuery('task_start_date');
$q->addQuery('task_end_date');
$q->addWhere('task_project='.$project_id);
$sql = $q->prepare();

$tasks = db_loadList($sql);

foreach($tasks as $task){
	$currentTaskStartDate = new DateTime($task["task_start_date"]);	
	$currentTaskEndDate = new DateTime($task["task_end_date"]);
	echo "Current task start date: ".$task["task_start_date"]."<br />";
	echo "Current task end date: ".$task["task_end_date"]."<br />";
	$intervalTaskDuration= $currentTaskStartDate->diff($currentTaskEndDate);
	
	echo "Task duration:". $intervalTaskDuration->days ."<br />";
	$intervalProjectBegin= $currentProjectStartDate->diff($currentTaskStartDate);
	$newTaskStartDate = new DateTime($newProjectStartDateString);
	$newTaskStartDate->modify("+". $intervalProjectBegin->days. " day");
	$newTaskStartDateString=date_format($newTaskStartDate,"Y-m-d");
	echo "New task start date: ".$newTaskStartDateString ."<br />";
	$newTaskEndDate = new DateTime($newTaskStartDateString);
	$newTaskEndDate->modify("+". $intervalTaskDuration->days. " day");
	$newTaskEndDateString=date_format($newTaskEndDate,"Y-m-d");
	echo "New task end date: ".$newTaskEndDateString ."<br />"; 
}
die();
?>