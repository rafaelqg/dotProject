<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI;

$project_id= $_POST["project_id"];
$start_date= $_POST["new_start_date_".$project_id];
$df = $AppUI->getPref('SHDATEFORMAT');
if($start_date != ""){
	//echo $project_id, $start_date, $df;
	$userDateFormat=strtolower($df); 
	$df=str_replace("%","",$df);//prepare for printing correctly
	$dateSeparator= substr($userDateFormat,2,1);
	//echo "separator: ". $dateSeparator ."<br />";
	//echo "date format: ". $userDateFormat."<br />";
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
		//echo $interval->days;
		//echo $interval->format('%R%a days');
		$newProjectEndDate->modify("+". $interval->days. " day");
		?>
		<table width="100%" class="tbl">
			<tr style="font-weight:bold">
				<td></td>
				<td>Project start date</td>				
				<td>Project end date</td>
			</tr>
			<tr>
				<td style="font-weight:bold">Current</td>
				<td><?php echo $currentProjectStartDate->format($df); ?></td>
				<td><?php echo $currentProjectEndDate->format($df); ?></td>
			</tr>
			<tr>
				<td style="font-weight:bold">New</td>
				<td><?php echo $newProjectStartDate->format($df); ?></td>
				<td><?php echo $newProjectEndDate->format($df); ?></td>
			</tr>
		
		</table>
		<br /><br />
		<?php
		//update project new dates in database
		$q = new DBQuery();
		$q->addTable('projects');
		$q->addUpdate('project_start_date', $newProjectStartDate->format("Y-m-d"));
		$q->addUpdate('project_end_date', $newProjectEndDate->format("Y-m-d"));
		$q->addWhere('project_id='.$project_id);
		$q->exec();
		$q->clear();		
	}

	?>

	<?php

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
	?>
	<table width="100%" class="tbl">
		<tr style="font-weight:bold">
			<td>
				Task id
			</td>
			<td>
				Task description
			</td>
			<td>
				Start date
			</td>
			<td>
				End date
			</td>
			<td>
				Task duration (days)
			</td>
			<td> 
				New Task Start Date
			</td>
			<td>
				New Task End Date
			</td>
		</tr>
	<?php
	foreach($tasks as $task){
		$currentTaskStartDate = new DateTime($task["task_start_date"]);	
		$currentTaskEndDate = new DateTime($task["task_end_date"]);
		//echo "Current task start date: ".$task["task_start_date"]."<br />";
		//echo "Current task end date: ".$task["task_end_date"]."<br />";
		$intervalTaskDuration= $currentTaskStartDate->diff($currentTaskEndDate);
		
		//echo "Task duration:". $intervalTaskDuration->days ."<br />";
		$intervalProjectBegin= $currentProjectStartDate->diff($currentTaskStartDate);
		$newTaskStartDate = new DateTime($newProjectStartDateString);
		$newTaskStartDate->modify("+". $intervalProjectBegin->days. " day");
		$newTaskStartDateString=date_format($newTaskStartDate,"Y-m-d");
		//echo "New task start date: ".$newTaskStartDateString ."<br />";
		$newTaskEndDate = new DateTime($newTaskStartDateString);
		$newTaskEndDate->modify("+". $intervalTaskDuration->days. " day");
		$newTaskEndDateString=date_format($newTaskEndDate,"Y-m-d");
		//echo "New task end date: ".$newTaskEndDateString ."<br />";
		
		//update task new dates in database
		$q = new DBQuery();
		$q->addTable('tasks');
		$q->addUpdate('task_start_date', $newTaskStartDate->format("Y-m-d"));
		$q->addUpdate('task_end_date', $newTaskEndDate->format("Y-m-d")); 
		$q->addWhere('task_id='.$task["task_id"]);
		$q->exec();
		$q->clear();
		
	?>
		<tr>
			<td> <?php echo $task["task_id"]; ?></td>
			<td> <?php echo $task["task_name"]; ?></td>
			<td> <?php echo $currentTaskStartDate->format($df); ?></td>
			<td> <?php echo $currentTaskEndDate->format($df); ?></td>
			<td> <?php echo $intervalTaskDuration->days; ?></td>
			<td> <?php echo $newTaskStartDate->format($df); ?></td>
			<td> <?php echo $newTaskEndDate->format($df); ?></td>
		</tr>

	<?php	
	}
	?>
	</table>
	<br />
	<a href="?m=projects&a=view&project_id=<?php echo $project_id ?>" align="center">Open Project</a>
	<?php
}else{
	echo "Date not informed.";
}

die();
?>