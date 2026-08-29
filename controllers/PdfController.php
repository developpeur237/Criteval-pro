<?php
declare(strict_types=1);

class PdfController
{
    public function export(): void
    {
        http_response_code(501);
        echo 'PDF export is not implemented yet.';
    }
}
