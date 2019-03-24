<?php
require_once DP_BASE_DIR . '/classes/query.class.php';


/**
 * Frappe Gantt renderer for dotProject
 * 
 * @author Matt Bell (2Pi Software) 2019
 */
class Gantt {
    const ListProjects = 1;
    const ListProjectTasks = 2;
    private static $headerWritten = false;

    /**
     * Create a gantt for projects
     * 
     * @return Gantt
     */
    public static function Projects() {
        return new Gantt(Gantt::ListProjects);
    }

    /**
     * Create a gantt for tasks within the specified project
     * 
     * @param int $projectid ID of the project
     * @return Gantt
     */
    public static function ProjectTasks($projectid) {
        return new Gantt(Gantt::ListProjectTasks, ["projectid"=>$projectid]);
    }

    /**
     * Write the header required to display the gantt chart
     */
    public static function WriteHeader() {
        if (!Gantt::$headerWritten) {
            echo '<link rel="stylesheet" href="lib/frappe-gantt/frappe-gantt.css">
            <script src="lib/frappe-gantt/frappe-gantt.min.js"></script>';
            Gantt::$headerWritten = true;
        }
    }

    /**
     * Create a Gantt Chart
     */
    private function __construct($type, $params = []) {
        $this->getFilters();
        switch ($type) {
            case Gantt::ListProjects:
                $this->getProjects();
            break;
            case Gantt::ListProjectTasks:
                $this->getProjectTasks($params["projectid"]);
            break;
        }
    }

    /**
     * List of tasks to render
     */
    private $tasks = [];

    /**
     * List of filters
     */
    private $filters = [];

    /**
     * Render the gantt chart
     */
    public function render() {
        if (!Gantt::$headerWritten) {
            Gantt::WriteHeader();
        }
        $json = json_encode($this->tasks);
        //render html+json for frappe
        echo "<svg id=\"gantt\"></svg>
        <script>
        var tasks = $json;
        var gantt = new Gantt(\"#gantt\", tasks);
        </script>";
    }

    /**
     * Get the options chosen from the project filter pane
     */
    private function getFilters() {
        $this->filters["user_id"] = intval(dPgetParam($_REQUEST, 'user_id', $AppUI->user_id));
        $this->filters["proFilter"] = (int)dPgetParam($_REQUEST, 'proFilter', '-1');
        $this->filters["company_id"] = intval(dPgetParam($_REQUEST, 'company_id', 0));
        $this->filters["department"] = intval(dPgetParam($_REQUEST, 'department', 0));
        $this->filters["showLabels"] = (int)dPgetParam($_REQUEST, 'showLabels', 0);
        $this->filters["showInactive"] = (int)dPgetParam($_REQUEST, 'showInactive', 0);
        $this->filters["sortTasksByName"] = (int)dPgetParam($_REQUEST, 'sortTasksByName', 0);
        $this->filters["addPwOiD"] = (int)dPgetParam($_REQUEST, 'addPwOiD', 0);
        $this->filters["m_orig"] = dPgetCleanParam($_REQUEST, 'm_orig', $m);
        $this->filters["a_orig"] = dPgetCleanParam($_REQUEST, 'a_orig', $a);    }

    /**
     * Get the projects from the db and format them for a gantt chart
     */
    private function getProjects() {
        $q = new DBQuery;
        $pjobj = new CProject;
        global $dPconfig;
        $working_hours = $dPconfig['daily_working_hours'];
        $owner_ids = array();
        if ($this->filters['addPwOiD'] && $this->filters["department"] > 0) {
            $q->addTable('users');
            $q->addQuery('user_id');
            $q->addJoin('contacts', 'c', 'c.contact_id = user_contact');
            $q->addWhere('c.contact_department = '.$this->filters["department"]);
            $owner_ids = $q->loadColumn();	
            $q->clear();
        }
        $q->addTable('projects', 'p');
        $q->addQuery('DISTINCT p.project_id, project_color_identifier, project_name, project_start_date' 
                     . ', project_end_date, max(t1.task_end_date) AS project_actual_end_date' 
                     . ', SUM(task_duration * task_percent_complete * IF(task_duration_type = 24, ' 
                     . $working_hours . ', task_duration_type))' 
                     . ' / SUM(task_duration * IF(task_duration_type = 24, ' 
                     . $working_hours . ', task_duration_type)) AS project_percent_complete' 
                     . ', project_status');
        $q->addJoin('tasks', 't1', 'p.project_id = t1.task_project');
        $q->addJoin('companies', 'c1', 'p.project_company = c1.company_id');
        if ($this->filters["department"] > 0) {
            $q->addJoin('project_departments', 'pd', 'pd.project_id = p.project_id');
            
            if (!$this->filters["addPwOiD"]) {
                $q->addWhere('pd.department_id = ' . $this->filters["department"]);
            } else {
                // Show Projects where the Project Owner is in the given department
                $q->addWhere('p.project_owner IN (' 
                             . ((!empty($owner_ids)) ? implode(',', $owner_ids) : 0) . ')');
            }
        } else if ($this->filters["company_id"] != 0 && !$this->filters["addPwOiD"]) {
            $q->addWhere('project_company = ' . $this->filters["company_id"]);
        }
        
        if ($this->filters["proFilter"] == '-4') {
            $q->addWhere('project_status != 7');
        } else if ($this->filters["proFilter"] == '-3') {
            $q->addWhere('project_owner = ' . $this->filters["user_id"]);
        } else if ($this->filters["proFilter"] == '-2') {
            $q->addWhere('project_status != 3');
        } else if ($this->filters["proFilter"] != '-1') {
            $q->addWhere('project_status = ' . $this->filters["proFilter"]);
        }
        
        if ($this->filters["user_id"] && $this->filters["m_orig"] == 'admin' && $this->filters["a_orig"] == 'viewuser') {
            $q->addWhere('project_owner = ' . $this->filters["user_id"]);
        }
        
        if ($this->filters["showInactive"] != '1') {
            $q->addWhere('project_status != 7');
        }
        //$pjobj->setAllowedSQL($AppUI->user_id, $q, null, 'p');
        $q->addGroup('p.project_id');
        $q->addOrder('project_name, task_end_date DESC');
        
        $projects = $q->loadList();
        $q->clear();
        foreach ($projects as $project) {
            array_push($this->tasks, [
                "id" => $project["project_id"],
                "name" => $project["project_name"],
                "start" => $project["project_start_date"],
                "end" => $project["project_end_date"]
            ]);
        }
    }

    /**
     * Get the project tasks from the db and format them for a gantt chart
     */
    private function getProjectTasks($projectID) {
        global $AppUI;

        $q = new DBQuery;
        $q->addTable('tasks', 't');
        $q->addJoin('projects', 'p', 'p.project_id = t.task_project');
        
        $q->addQuery('t.task_id, task_parent, task_name, task_start_date, task_end_date' 
                     . ', task_duration, task_duration_type, task_priority, task_percent_complete' 
                     . ', task_order, task_project, task_milestone, project_name, task_dynamic');
        
        $q->addWhere('project_status != 7');
        if ($project_id) {
            $q->addWhere('task_project = ' . $project_id);
        }
        if ($f != 'myinact') {
            $q->addWhere('task_status > -1');
        }
        switch ($f) {
            case 'all':
                break;
            case 'myproj':
                $q->addWhere('project_owner = ' . $AppUI->user_id);
                break;
            case 'mycomp':
                $q->addWhere('project_company = ' . $AppUI->user_company);
                break;
            case 'myinact':
                $q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
                $q->addWhere('ut.user_id = '.$AppUI->user_id);
                break;
            default:
                $q->innerJoin('user_tasks', 'ut', 'ut.task_id = t.task_id');
                $q->addWhere('ut.user_id = '.$AppUI->user_id);
                break;
        }
        
        $q->addOrder('p.project_id, ' . (($sortByName) ? 't.task_name, ' : '') . 't.task_start_date');

        $tasks = $q->loadHashList('task_id');
        $q->clear();

        foreach ($tasks as $task) {
            array_push($this->tasks, [
                "id" => $task["task_id"],
                "name" => $task["task_name"],
                "start" => $task["task_start_date"],
                "end" => $task["task_end_date"],
                "progress" => $task["task_percent_complete"]
            ]);
        }
    }
}

