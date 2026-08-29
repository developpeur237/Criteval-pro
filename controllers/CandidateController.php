<?php
declare(strict_types=1);

class CandidateController
{
    public function access(): void
    {
        render_view('modules/Candidate/views/form_access.php');
    }

    public function fill(): void
    {
        render_view('modules/Candidate/views/form_fill.php');
    }
}
