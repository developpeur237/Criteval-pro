<?php
declare(strict_types=1);

class ProjectController
{
    public function index(): void
    {
        render_view('modules/Admin/views/projects/index.php');
    }
}
