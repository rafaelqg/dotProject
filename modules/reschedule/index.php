<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
$AppUI->savePlace();

GLOBAL $AppUI;
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


$q = new DBQuery;
$q->addTable('projects','p');
$q->addJoin('contacts', 'con', 'p.project_owner=con.contact_id');
$q->addJoin('companies', 'com', 'com.company_id= p.project_company');
$q->addQuery('p.project_id');
$q->addQuery('p.project_name');
$q->addQuery('project_start_date');
$q->addQuery('project_end_date');
$q->addQuery('com.company_name','company_name');
$q->addQuery('CONCAT_WS(", ", con.contact_last_name,con.contact_first_name) as owner'); 
$q->addOrder('p.project_start_date');
//$q->addOrder('com.company_name');
//$q->addWhere('');
$sql = $q->prepare();
//echo $sql;
$projects = db_loadList($sql);//$q->loadHashList();
?>
Choose the project to be rescheduled:
<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Project Name');?>		
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Company');?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Owner');?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Start');?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('Due Date');?>
	</th>
	<th nowrap="nowrap">
		<?php echo $AppUI->_('New start date');?>
	</th>
</tr>

<?php
foreach ($projects as $row) {
	if (! getPermission('projects', 'view', $row['project_id'])) {
		continue;
	}
	$start_date = ((intval(@$row['project_start_date'])) ? new CDate($row['project_start_date']) : null);
	$end_date = ((intval(@$row['project_end_date'])) ? new CDate($row['project_end_date']) : null);
	?>
	<tr>
		<td>
			<a href="?m=projects&a=view&project_id=<?php echo $row["project_id"] ?>">
				<?php echo $row["project_name"]; ?>
			</a>
		</td>
		<td>
			<?php echo $row["company_name"]; ?>
		</td>
		<td>
			<?php echo $row["owner"]; ?>
		</td>
		<td align="center">
			<?php echo (htmlspecialchars($start_date ? $start_date->format($df) : '-')); ?>
		</td>
		<td align="center"> 
			<?php echo (htmlspecialchars($end_date ? $end_date->format($df) : '-')); ?>
		</td>
		<td nowrap="nowrap">
			<form action="?m=reschedule" method="POST"> 
				<input type="hidden" name="dosql" value="do_reschedule_project" />
				<input type="hidden" name="project_id" value="<?php echo $row['project_id']; ?>" />
				<input type="text" name="new_start_date_<?php echo $row['project_id'] ?>" id="new_start_date_<?php echo $row['project_id'] ?>" />
				<script>
					var startDate=document.getElementById("new_start_date_<?php echo $row['project_id']?>");
					$(startDate).datepicker({dateFormat: "<?php echo $_SESSION["dateFormat"] ?>",showButtonPanel: true, firstDay: 1, changeYear:true, changeMonth:true} );
				</script> 
				<input type="submit" class="button" value="Reschedule" />
			</form>
		</td>
	</tr>
<?php }  ?>
</table>