<?php
require_once DP_BASE_DIR . '/classes/query.class.php';

/**
 * Frappe Gantt renderer for PHP
 * 
 * @author Matt
 * @
 */
class Gantt {
    public const ListProjects = 1;
    public const ListProjectTasks = 2;
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
     * Get the projects from the db and format them for a gantt chart
     */
    private function getProjects() {
        $q = new DBQuery;
        $q->addTable('projects', 'p');
        $q->addQuery('project_id, project_color_identifier, project_name, project_start_date, project_end_date');
        $q->addJoin('tasks', 't1', 'p.project_id = t1.task_project');
        $q->addWhere('project_status != 7');
        $q->addGroup('project_id');
        $q->addOrder('project_name');
        $projects = $q->loadHashList('project_id');
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
        //get tasks of a project
    }
}

