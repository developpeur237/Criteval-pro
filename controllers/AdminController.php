<?php
declare(strict_types=1);

class AdminController
{
    public function dashboard(): void
    {
        render_view('modules/Admin/views/dashboard.php');
    }
}
