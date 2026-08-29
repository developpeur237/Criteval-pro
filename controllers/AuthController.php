<?php
declare(strict_types=1);

class AuthController
{
    public function login(): void
    {
        render_view('modules/Auth/views/login.php');
    }
}
