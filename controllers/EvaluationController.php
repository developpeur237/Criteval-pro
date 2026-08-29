<?php
declare(strict_types=1);

class EvaluationController
{
    public function ranking(): void
    {
        render_view('modules/Admin/views/evaluations/ranking.php');
    }
}
