<?php /* TASKS $Id$ */
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}

$AppUI->savePlace();
GLOBAL $AppUI, $dPconfig;
?>

<script src="modules/reschedule/js/jquery-3.2.1.min.js"></script>
<script src="modules/reschedule/js/jquery-ui.js"></script>
<script src="modules/reschedule/js/jquery-datepicker-customizations.js"></script>
<link rel="stylesheet" href="modules/reschedule/css/jquery-ui.css">
<!-- include libraries for lightweight messages -->
<link type="text/css" rel="stylesheet" href="modules/reschedule/js/alertify/alertify.css" media="screen"></link>
<script type="text/javascript" src="modules/reschedule/js/alertify/alertify.js"></script>
 
<?php
// collect all the users for the company owner list
$df = $AppUI->getPref('SHDATEFORMAT');
$userDateFormat=$AppUI->user_prefs["SHDATEFORMAT"]; 
$_SESSION["dateFormatPHP"]=$userDateFormat;
$userDateFormat=str_replace("%d", "dd", $userDateFormat);
$userDateFormat=str_replace("%m", "mm", $userDateFormat); 
$userDateFormat=str_replace("%Y", "YY", $userDateFormat);
$userDateFormat=strtolower($userDateFormat); 
$_SESSION["dateFormat"]=$userDateFormat;
$task_id=$_GET["task_id"];

$q = new DBQuery();
$q->addTable('tasks','t');
$q->addQuery('t.task_id');
$q->addQuery('t.task_name');
$q->addQuery('t.task_start_date');
$q->addQuery('t.task_end_date');
$q->addOrder('t.task_start_date');
$q->addWhere('t.task_id='.$task_id);
$sql = $q->prepare();
$tasks = db_loadList($sql);//$q->loadHashList();
?>
<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Task Name');?>		
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Start Date');?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('End Date');?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('New start date');?>
	</th>
</tr>

<?php
foreach ($tasks as $row) {
	$start_date = ((intval(@$row['task_start_date'])) ? new CDate($row['task_start_date']) : null);
	$end_date = ((intval(@$row['task_end_date'])) ? new CDate($row['task_end_date']) : null);
	?>
	<tr>
		<td align="center">
			<?php echo (htmlspecialchars($start_date ? $start_date->format($df) : '-')); ?>
		</td>
		<td align="center"> 
			<?php echo (htmlspecialchars($end_date ? $end_date->format($df) : '-')); ?>
		</td>
		<td nowrap="nowrap">
			<form action="?m=reschedule" method="POST"> 
				<input type="hidden" name="dosql" value="do_reschedule_task" /> 
				<input type="hidden" name="task_id" value="<?php echo $row['task_id']; ?>" />
				<input type="text" name="new_start_date_<?php echo $row['task_id'] ?>" id="new_start_date_<?php echo $row['task_id'] ?>" autocomplete="off" />
				<script>
					var startDate=document.getElementById("new_start_date_<?php echo $row['task_id']?>");
					$(startDate).datepicker({dateFormat: "<?php echo $_SESSION["dateFormat"] ?>",showButtonPanel: true, firstDay: 1, changeYear:true, changeMonth:true} );
				</script> 
				<input type="submit" class="button" value="Reschedule" />
			</form>
		</td>
	</tr>
<?php }  ?>
</table>