<?php
declare(strict_types=1);

class FormController
{
    public function gallery(): void
    {
        render_view('modules/Admin/views/forms/gallery.php');
    }
}
