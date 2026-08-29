<?php
declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        render_view('modules/Public/views/home.php');
    }
}
