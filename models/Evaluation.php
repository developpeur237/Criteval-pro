<?php
declare(strict_types=1);

class Evaluation
{
    public static function count(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM evaluations')->fetchColumn();
    }

    public static function averageScore(): float
    {
        $value = db()->query('SELECT COALESCE(AVG(score), 0) FROM evaluations')->fetchColumn();
        return (float) $value;
    }
}
