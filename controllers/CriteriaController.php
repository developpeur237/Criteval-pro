<?php
declare(strict_types=1);

class CriteriaController
{
    public function index(): void
    {
        render_view('modules/Admin/views/criteria/index.php');
    }
}
