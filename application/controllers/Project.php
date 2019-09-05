<?php
class Project_Action extends ActionPDO {

    /**
     * 项目选择
     */
    public function index ()
    {
        $projects = (new ProjectModel())->getProjects();
        $this->render('projectSelect.html', [
            'projects' => $projects
        ]);
    }

}
