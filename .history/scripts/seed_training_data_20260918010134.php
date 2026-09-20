<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Criteria.php';
require_once __DIR__ . '/../models/TrainingSession.php';

initialize_database();
$pdo = db();

$organisations = [
    [
        'Réseau Sahel Vert',
        'Programme de résilience agroécologique communautaire',
        'Agriculture durable, climat',
        'Femmes rurales, jeunes agriculteurs',
        'ML',
        'Sikasso et Koulikoro',
        'contact@sahelvert.ml',
        '+223 70 11 22 33',
    ],
    [
        'Femmes Numériques du Cameroun',
        'Inclusion numérique et entrepreneuriat féminin',
        'Inclusion numérique, entrepreneuriat',
        'Femmes entrepreneures, jeunes',
        'CM',
        'Douala et Yaoundé',
        'bonjour@femmesnumeriques.cm',
        '+237 690 22 33 44',
    ],
    [
        'Alliance Eau & Santé Sénégal',
        'Accès à l’eau et prévention sanitaire locale',
        'Eau potable, santé communautaire',
        'Ménages ruraux, associations locales',
        'SN',
        'Thiès et Saint-Louis',
        'contact@eausantesn.org',
        '+221 77 44 55 66',
    ],
];

$createdOrganisations = [];
foreach ($organisations as $organisation) {
    $find = $pdo->prepare('SELECT id FROM projects WHERE organization = :organization LIMIT 1');
    $find->execute(['organization' => $organisation[0]]);
    $projectId = (int) ($find->fetchColumn() ?: 0);

    if ($projectId === 0) {
        $projectId = Project::create([
            'title' => $organisation[1],
            'organization' => $organisation[0],
            'domains' => $organisation[2],
            'target_audiences' => $organisation[3],
            'legal_status' => 'legal',
            'country_code' => $organisation[4],
            'project_count' => 3,
            'intervention_zone' => $organisation[5],
            'description' => 'Organisation partenaire engagée dans des actions mesurables au service des communautés.',
            'contact_name' => 'Équipe coordination',
            'contact_phone' => $organisation[7],
            'contact_email' => $organisation[6],
            'website' => '',
            'budget_requested' => 25000000,
            'duration_months' => 12,
            'logo_path' => null,
            'status' => 'active',
            'created_by' => 1,
        ]);
        Criteria::configureProject($projectId, array_column(Criteria::templates(), 'id'), []);
    }

    $createdOrganisations[] = [$projectId, $organisation];
}

$formations = [
    ['Formation des relais agroécologiques', 45, 'hybride', 10],
    ['Leadership numérique pour les femmes', 35, 'presentiel', 15],
    ['Eau, hygiène et santé communautaire', 40, 'en_ligne', 10],
];

foreach ($formations as $index => $formation) {
    $projectId = $createdOrganisations[$index][0];
    $find = $pdo->prepare('SELECT id FROM training_sessions WHERE name = :name LIMIT 1');
    $find->execute(['name' => $formation[0]]);
    $sessionId = (int) ($find->fetchColumn() ?: 0);

    if ($sessionId === 0) {
        $sessionId = TrainingSession::create([
            'name' => $formation[0],
            'objective' => 'Renforcer les compétences opérationnelles et le suivi des actions de l’organisation.',
            'session_date' => date('Y-m-d', strtotime('-' . mt_rand(180, 780) . ' days')),
            'project_id' => $projectId,
            'capacity' => $formation[1],
            'format' => $formation[2],
            'evaluation_weight' => $formation[3],
            'public_slug' => strtolower('formation-' . bin2hex(random_bytes(3))),
            'is_active' => 1,
            'created_by' => 1,
        ]);
    }

    $form = $pdo->prepare('SELECT id FROM forms WHERE project_id = :project_id LIMIT 1');
    $form->execute(['project_id' => $projectId]);
    $formId = (int) ($form->fetchColumn() ?: 0);

    if ($formId === 0) {
        $insertForm = $pdo->prepare(
            'INSERT INTO forms (title, description, project_id, layout_json, status, created_by)
             VALUES (:title, :description, :project_id, :layout_json, :status, :created_by)'
        );
        $insertForm->execute([
            'title' => 'Candidature - ' . $formation[0],
            'description' => 'Formulaire de candidature et de suivi pour la formation sélectionnée.',
            'project_id' => $projectId,
            'layout_json' => json_encode(['fields' => [
                ['type' => 'text', 'label' => 'Nom de l’organisation'],
                ['type' => 'textarea', 'label' => 'Motivation'],
                ['type' => 'text', 'label' => 'Expérience pertinente'],
            ]], JSON_UNESCAPED_UNICODE),
            'status' => 'published',
            'created_by' => 1,
        ]);
        $formId = (int) $pdo->lastInsertId();
    }

    $count = $pdo->prepare('SELECT COUNT(*) FROM submissions WHERE form_id = :form_id');
    $count->execute(['form_id' => $formId]);
    if ((int) $count->fetchColumn() === 0) {
        $insert = $pdo->prepare(
            'INSERT INTO submissions (form_id, candidate_email, candidate_name, country_code, data_json, status)
             VALUES (:form_id, :email, :name, :country, :data, :status)'
        );
        foreach ([
            ['Aïcha Diallo', 'aicha.' . ($index + 1) . '@example.test'],
            ['Moussa Ndiaye', 'moussa.' . ($index + 1) . '@example.test'],
        ] as $candidate) {
            $insert->execute([
                'form_id' => $formId,
                'email' => $candidate[1],
                'name' => $candidate[0],
                'country' => $createdOrganisations[$index][1][4],
                'data' => json_encode(['motivation' => 'Participation au programme de formation et mise en pratique dans ma communauté.'], JSON_UNESCAPED_UNICODE),
                'status' => 'pending',
            ]);
        }
    }
}

echo 'Seed terminé : 3 organisations, 3 formations et leurs candidatures sont disponibles.' . PHP_EOL;
