<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI;

function updateTaskDates($taskId,$interval,$direction){
		//Get current start and end date of the task
		$q = new DBQuery();
		$q->addTable('tasks','t');
		$q->addQuery('t.task_start_date');
		$q->addQuery('t.task_name');
		$q->addQuery('t.task_end_date');
		$q->addQuery('t.task_project');
		$q->addWhere('t.task_id='.$taskId);
		$sql = $q->prepare();
		$tasks = db_loadList($sql);
		$currentTaskStartDate=null;
		$currentTaskEndDate=null;
		$newTaskStartDate=null;
		$newTaskEndDate=null;
		$project_id=null;
		$taskDescription=null;
		foreach($tasks as $task){
				$currentTaskStartDate = new DateTime($task["task_start_date"]);
				$currentTaskEndDate = new DateTime($task["task_end_date"]);
				$newTaskStartDate = new DateTime($task["task_start_date"]);
				$newTaskEndDate = new DateTime($task["task_end_date"]);
				$taskDescription=$task["task_name"];
				$project_id= $task["task_project"];
		}
		
		//calculate new task end date
		if($currentTaskStartDate != null && $currentTaskEndDate!=null){
				$newTaskStartDate->modify($direction. $interval->days. " day");
				$newTaskEndDate->modify($direction. $interval->days. " day");
				
				global $currentProjectStartDate, $currentProjectEndDate, $originalProjectEndDate, $originalProjectStartDate;
				if($newTaskStartDate< $currentProjectStartDate){
					$currentProjectStartDate=$newTaskStartDate;
				}
				if($currentProjectEndDate<$newTaskEndDate){
					$currentProjectEndDate=$newTaskEndDate;
				}
				
		}
			
			//update task new dates in database
			$q = new DBQuery();
			$q->addTable('tasks');
			$q->addUpdate('task_start_date', $newTaskStartDate->format("Y-m-d"));
			$q->addUpdate('task_end_date', $newTaskEndDate->format("Y-m-d"));
			$q->addWhere('task_id='.$taskId);
			$q->exec();
			$q->clear();		
			
			global $AppUI, $outputString;
			$df = $AppUI->getPref('SHDATEFORMAT');
			$userDateFormat=strtolower($df); 
			$df=str_replace("%","",$df);//prepare for printing correctly
			$outputString.= "\n\nTask:  ".$taskDescription;//. "($taskId)";
			$outputString.= "\nStart date:  ". $currentTaskStartDate->format($df);
			$outputString.= " => ". $newTaskStartDate->format($df);
			$outputString.= "\nEnd date:  ". $currentTaskEndDate->format($df);
			$outputString.= " => ". $newTaskEndDate->format($df);
			?>
			<table width="100%" class="tbl">
				<tr>
					<td colspan="3">
						Task id: <?php echo $taskId; ?> <br />
						Task description:<?php echo $taskDescription; ?>
					</td>
				</tr>
				<tr style="font-weight:bold">
					<td></td>
					<td>Task start date</td>				
					<td>Task end date</td>
				</tr>
				<tr>
					<td style="font-weight:bold">Current</td>
					<td><?php echo $currentTaskStartDate->format($df); ?></td>
					<td><?php echo $currentTaskEndDate->format($df); ?></td>
				</tr>
				<tr>
					<td style="font-weight:bold">New</td>
					<td><?php echo $newTaskStartDate->format($df); ?></td>
					<td><?php echo $newTaskEndDate->format($df); ?></td>
				</tr>
			
			</table>	
			<br /><br /> 
			<?php
}

function updateDependencies($task_id, &$arrayDependency,$interval,$direction){
	
	updateTaskDates($task_id,$interval,$direction);
	
	$q = new DBQuery();
	$q->addTable('task_dependencies');
	$q->addQuery('dependencies_task_id');
	$q->addWhere('dependencies_req_task_id='.$task_id);
	$sql = $q->prepare();
	//echo $sql;
	$tasks = db_loadList($sql);//$q->loadHashList();
	foreach($tasks as $task){
			$dep_id= $task["dependencies_task_id"];
			if(!isset($arrayDependency[$dep_id])){ //avoid circular dependencies
				$arrayDependency[$dep_id]=$dep_id;			
				updateDependencies($dep_id, $arrayDependency,$interval,$direction);
			}
	}			
}

$task_id= $_POST["task_id"];
$start_date= $_POST["new_start_date_".$task_id];
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
	$newTaskStartDateString=$startDateParts[$yearIndex]."-".$startDateParts[$monthIndex]."-".$startDateParts[$dayIndex];
	$newTaskStartDate = new DateTime($newTaskStartDateString);

	$currentProjectStartDate=null;
	$currentProjectEndDate=null;

	$arrayDependency = array();
	$arrayDependency[$task_id]=$task_id;
		
	$q = new DBQuery();
	$q->addTable('tasks','t');
	$q->addQuery('t.task_start_date');
	$q->addQuery('t.task_end_date');
	$q->addQuery('t.task_project');
	$q->addWhere('t.task_id='.$task_id);
	$sql = $q->prepare();
	//echo $sql;
	$tasks = db_loadList($sql);//$q->loadHashList();
	$currentTaskStartDate=null;
	$currentTaskEndDate=null;
	$project_id=null;
	
	foreach($tasks as $task){
		$project_id= $task["task_project"];
		break;
	}
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
	$originalProjectStartDate=null;
	$originalProjectEndDate =null;
	foreach($projects as $project){
			$currentProjectStartDate = new DateTime($project["project_start_date"]);
			$currentProjectEndDate = new DateTime($project["project_end_date"]);
			$originalProjectStartDate = new DateTime($project["project_start_date"]);
			$originalProjectEndDate = new DateTime($project["project_end_date"]);
	}
	
	
	$outputString="";
	$outputString.="Task rescheduled to " . $newTaskStartDate->format($df);
	$outputString.= "\n\nUpdated tasks:\n";
	
	foreach($tasks as $task){
			$project_id= $task["task_project"];
			$currentTaskStartDate = new DateTime($task["task_start_date"]);
			$interval= $currentTaskStartDate->diff($newTaskStartDate);//interval created by new date
			$direction=$currentTaskStartDate>$newTaskStartDate?"-":"+";
			//echo $task_id;
			updateDependencies($task_id, $arrayDependency,$interval,$direction);
	}
	
	//update project new dates in database 
	if($currentProjectStartDate != null && $currentProjectEndDate!=null && $originalProjectStartDate != null &&  ($currentProjectStartDate->format("Y-m-d") != $originalProjectStartDate->format($df)) ){
		$q = new DBQuery();
		$q->addTable('projects');
		$q->addUpdate('project_start_date', $currentProjectStartDate->format("Y-m-d"));
		$q->addUpdate('project_end_date', $currentProjectEndDate->format("Y-m-d"));
		$q->addWhere('project_id='.$project_id);
		$q->exec();
		$q->clear();	
		/*
		$outputString.= "\n\nProject dates updated:";
		$outputString.= "\nStart date:  ". $originalProjectStartDate->format($df);
		$outputString.= " => ". $currentProjectStartDate->format($df);
		$outputString.= "\nEnd date:  ". $originalProjectEndDate->format($df);
		$outputString.= " => ". $currentProjectEndDate->format($df);
		*/
	}
	
	$AppUI->setMsg( $outputString );
	?>
	<br />
	<a href="?m=projects&a=view&project_id=<?php echo $project_id ?>" align="center">Open Project</a>
	
	<?php
}else{
	$AppUI->setMsg( "Date not informed!" , UI_MSG_WARNING, true);
}
$AppUI->redirect();
?>