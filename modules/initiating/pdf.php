<?php
require_once (DP_BASE_DIR ."/base.php");
require_once DP_BASE_DIR . ("/includes/config.php");
require_once (DP_BASE_DIR . "/classes/csscolor.class.php"); // Required before main_functions
require_once (DP_BASE_DIR . "/includes/main_functions.php");
require_once (DP_BASE_DIR . "/includes/db_adodb.php");
require_once (DP_BASE_DIR . "/includes/db_connect.php");
require_once (DP_BASE_DIR . "/classes/ui.class.php");
require_once (DP_BASE_DIR . "/modules/projects/projects.class.php");
require_once (DP_BASE_DIR . "/classes/permissions.class.php");
require_once (DP_BASE_DIR . "/includes/session.php");
require_once (DP_BASE_DIR . "/modules/initiating/libraries/dompdf-master-v2/dompdf_config.inc.php");

function formatListField($text){
    $text=str_ireplace("\n", "<br />", $text);
    return $text;
}

set_time_limit (300);
$df = $AppUI->getPref('SHDATEFORMAT');
//$htmlCode = file_get_contents($baseUrl . "/teste.php");

//fix specific characters that aren't threated by html_entity_decode
?>
<?php

$id=intval(dPgetParam($_GET, 'id', 0));
$q = new DBQuery();
$q->addQuery('*');
$q->addTable('initiating');
$q->addWhere('initiating_id = ' . $id);
$initiatingObj= new CInitiating();  
$initiatingObj->load($id);
// load the record data
$obj = new CInitiating(); 
$obj = null;
if (!db_loadObject($q->prepare(), $obj) && $id > 0) {
	$AppUI->setMsg('Initiating');
	$AppUI->setMsg("invalidID", UI_MSG_ERROR, true);
	$AppUI->redirect();
}
$managerName="";
if($obj->initiating_manager>0){
	$q = new DBQuery();
	$q->addQuery("*");
	$q->addTable("contacts","con");
	$q->addJoin("users", "u", "u.user_contact=con.contact_id");
	$q->addWhere("user_id = " . $obj->initiating_manager);
	$contact = $q->loadHash();
	$managerName = $contact['contact_first_name'] . " " .  $contact['contact_last_name'];
}

//get company info
$projectId = $obj->project_id;
$projectObj = new CProject();
$projectObj->load($projectId);
$companyId = $projectObj->project_company;
$companyObj = new CCompany();
$companyObj->load($companyId);
$companyName = $companyObj->company_name;
//  <meta charset='UTF-8' content='text/html' http-equiv='Content-Type' />
$htmlCode = "";          
$htmlCode.="<!DOCTYPE html><html lang='en'><head>
			<title>".$AppUI->_("Project Charter")."</title>
            <style>
            
                @page {
                    size: A4;
                    margin: 2cm;
                }
                body{
                    margin-top: 1.0cm;
                    margin-left:75px;
                    margin-right:75px;
                }
                
                body, div{
                    font-size:11px;
                    color:#000000;
                    font-family:  arial, Helvetica;
                    line-height: 140%;
                }
            </style>
        </head><body>".chr(13).chr(10);
$htmlCode.=("<div style='font-size:18px;text-align:center; font-weight: bold'>".$AppUI->_("Project Charter")."</div>"); 
$htmlCode.="<br /> <br />";
$htmlCode.="<div style='text-align:right;line-height:115%; color:silver;font-size:11px'>";
//this line below is useless, but necessary to the next line be displayed (bugfix)
$htmlCode.="<span style='color:white;font-size:1px'>". $AppUI->_("Company",UI_OUTPUT_HTML )."</span>";
$htmlCode.="<span>". $AppUI->_("Company",UI_OUTPUT_HTML ). "</span>: ". $companyName ."<br />";
$htmlCode.="<span>". $AppUI->_("Date",UI_OUTPUT_HTML). "</span>: ". date('d/m/Y', time())."<br />";
$htmlCode.="</div>".chr(13).chr(10);
$htmlCode.=("<br /><br /> <div style='text-align: justify;'>").chr(13).chr(10);
$htmlCode.=(("<br /><b>".($AppUI->_("Project Title",UI_OUTPUT_HTML)).": </b><br />" . $obj->initiating_title));
$htmlCode.=('<br />');
$htmlCode.="<br /><b>". $AppUI->_("Project Manager") . ":</b><br />". $managerName; 

$htmlCode.=('<br />');
$htmlCode.=(("<br /><b>".$AppUI->_("Justification").": </b><br />" . formatListField($obj->initiating_justification)));
$htmlCode.=('<br />');
$htmlCode.=(("<br /><b>".$AppUI->_("Objectives").":</b><br />" .  formatListField($obj->initiating_objective)));
$htmlCode.=('<br />');
$htmlCode.=(("<br /><b>".$AppUI->_("Expected Results").": </b><br />" .  formatListField($obj->initiating_expected_result)));
$htmlCode.=('<br />');
$htmlCode.=(("<br /><b>".$AppUI->_("Premises").": </b><br />" .  formatListField($obj->initiating_premise)));
$htmlCode.=('<br />');
$htmlCode.=(("<br /><b>".($AppUI->_("Restrictions",UI_OUTPUT_HTML)). ":</b><br />" .  formatListField($obj->initiating_restrictions)));
$htmlCode.=('<br />');
$htmlCode.=(("<br /><b>".($AppUI->_("Budget",UI_OUTPUT_HTML)). " (R$): </b><br />" .  number_format($obj->initiating_budget, 2, ',', '.')));
$htmlCode.=('<br />');

$dateStart = new CDate($obj->initiating_start_date);
$dateEnd = new CDate($obj->initiating_end_date);


$htmlCode.=(("<br /><b>".$AppUI->_("Start Date",UI_OUTPUT_HTML).": </b><br />" .  $dateStart->format($df)));
$htmlCode.=('<br />');
$htmlCode.=(("<br /><b>".$AppUI->_("End Date",UI_OUTPUT_HTML).": </b><br />" . $dateEnd->format($df) ));
$htmlCode.=('<br />');

$milestones_text="";
$milestones =$initiatingObj->loadMillestones();
foreach($milestones as $milestone){
		$milestone_date = new CDate($milestone->task_start_date);
		$milestones_text .= $milestone->task_name ." (". $milestone_date->format($df).")<br />"; 
}


$htmlCode.=(("<br /><b>".$AppUI->_("Milestones",UI_OUTPUT_HTML).": </b><br />" .  formatListField($milestones_text)));
$htmlCode.=('<br />');
$htmlCode.=(("<br /><b>".$AppUI->_("Criteria for success",UI_OUTPUT_HTML).": </b><br />" .  formatListField($obj->initiating_success)));
$htmlCode.=('</div>').chr(13).chr(10);
$htmlCode.=('<br />');
$htmlCode.=('<br />');
$htmlCode.=("<div style='text-align:center'><b>".$AppUI->_("Signatures",UI_OUTPUT_HTML)."</b></div>").chr(13).chr(10);; 
$htmlCode.= "<br /><br /><br /><br />".chr(13).chr(10);;
$htmlCode.= "<div style='text-align:center'>";
$htmlCode.= "<span style='border-top: 1px solid #000000'>" . $AppUI->_("Project Sponsor",UI_OUTPUT_HTML) . "</span>".chr(13).chr(10);;
$htmlCode.= "<br /><br /><br /><br />".chr(13).chr(10);;
$htmlCode.= "<span style='border-top: 1px solid #000000'>" . $AppUI->_("Project Manager",UI_OUTPUT_HTML) . "</span>".chr(13).chr(10);;
$htmlCode.= "</div>".chr(13).chr(10);
$htmlCode.= "</body></html>".chr(13).chr(10);

$htmlCode=utf8_decode($htmlCode); //keep this line, make the special chars on labels keeps well formated
//$htmlCode=str_ireplace("\n", "<br />", $htmlCode);
$htmlCode=str_ireplace("&nbsp;", " ", $htmlCode);
$htmlCode=str_ireplace("&aacute;", "á", $htmlCode);
//tmlCode=str_ireplace("&agrave;", "à", $htmlCode);
//$htmlCode=html_entity_decode($htmlCode,0);//convert HTML chars (e.g. &nbsp;) to the real characters
//$htmlCode=str_ireplace("&Atilde;&copy;", "é", $htmlCode);
echo $htmlCode;
$dompdf = new DOMPDF();
$dompdf->load_html($htmlCode);
$dompdf->render();
$dompdf->output();
$dompdf->stream("project_charter_". $id.".pdf");
?>