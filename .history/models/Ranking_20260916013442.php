<?php
declare(strict_types=1);

class Ranking
{
	public static function all(): array
	{
		$rows = db()->query(
			'SELECT s.id AS submission_id, s.candidate_name, s.submitted_at,
					COALESCE(r.weighted_score, 0) AS score,
					COALESCE(p.organization, p.title) AS organization,
					p.country_code
			 FROM submissions s
			 JOIN forms f ON f.id = s.form_id
			 LEFT JOIN projects p ON p.id = f.project_id
			 LEFT JOIN results r ON r.submission_id = s.id
			 WHERE s.status IN ("evaluated", "published")
			 ORDER BY score DESC, s.submitted_at ASC, s.id ASC'
		)->fetchAll();
		foreach ($rows as $index => &$row) {
			$row['rank_position'] = $index + 1;
		}
		return $rows;
	}
}
