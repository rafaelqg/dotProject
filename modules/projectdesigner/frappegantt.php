<?php


class Gantt {
    public const ListProjects = 1;
    public const ListProjectTasks = 2;

    public static function Projects() {
        return new Gantt(Gantt::ListProjects);
    }
    public static function ProjectTasks($projectid) {
        return new Gantt(Gantt::ListProjectTasks, ["projectid"=>$projectid]);
    }

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

    public function render() {
        //render html+json for frappe
        var_dump($this);
    }

    private function getProjects() {
        //get list of projects
    }

    private function getProjectTasks($projectID) {
        //get tasks of a project
    }
}

class GanttTask {

}
//TEST
Gantt::Projects()->render();
Gantt::ProjectTasks(123)->render();