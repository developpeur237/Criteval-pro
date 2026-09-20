<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';
initialize_database();
$pdo = db();
$criteria = [
    [1, 'Participation régulière aux réunions et activités CS4ME (minimum 75 %) au cours de l’année', 'Présence documentée aux réunions et activités CS4ME.'],
    [2, 'Réponse aux consultations, enquêtes et appels à contribution dans les délais demandés', 'Réponses transmises dans les délais convenus.'],
    [3, 'Au moins X relais par trimestre sur les réseaux sociaux ou canaux de communication de l’organisation', 'Relais et publications vérifiables sur les canaux de communication.'],
    [4, 'Contribution financière aux activités communes', 'Cofinancement d’événements ou d’actions collectives.'],
    [5, 'Partage régulier de données, rapports et résultats d’activité par trimestre', 'Données et rapports d’activité partagés chaque trimestre.'],
    [6, 'Participation au recrutement de nouveaux membres CS4ME au niveau pays', 'Contribution attestée au recrutement de nouveaux membres.'],
    [7, 'Mise en œuvre d’activités de terrain documentées et rapportées', 'Activités de terrain accompagnées de comptes rendus.'],
    [8, 'Actions de plaidoyer menées au nom ou en lien avec CS4ME', 'Actions de plaidoyer documentées et reliées à CS4ME.'],
    [9, 'Au moins une activité de renforcement des capacités par an', 'Activité organisée par CS4ME ou ses partenaires.'],
    [10, 'Formation des pairs, partage d’expertise et mentorat', 'Transmission d’expertise à des organisations moins expérimentées.'],
];
$projects = $pdo->query('SELECT id FROM projects')->fetchAll(PDO::FETCH_COLUMN);
$check = $pdo->prepare('SELECT id FROM criteria WHERE project_id = :project_id AND order_index = :order_index LIMIT 1');
$insert = $pdo->prepare('INSERT INTO criteria (project_id, label, description, weight, max_score, is_required, order_index) VALUES (:project_id, :label, :description, 1, 20, 1, :order_index)');
$update = $pdo->prepare('UPDATE criteria SET label = :label, description = :description WHERE project_id = :project_id AND order_index = :order_index');
foreach ($projects as $projectId) {
    foreach ($criteria as [$order, $label, $description]) {
        $check->execute(['project_id' => $projectId, 'order_index' => $order]);
        if (!$check->fetchColumn()) {
            $insert->execute(['project_id' => $projectId, 'label' => $label, 'description' => $description, 'order_index' => $order]);
        } else {
            $update->execute(['project_id' => $projectId, 'order_index' => $order, 'label' => $label, 'description' => $description]);
        }
    }
}
$pdo->exec("DELETE FROM criteria WHERE label LIKE 'CRUD matrix %' OR label LIKE 'CRUD updated %'");
foreach ($pdo->query('SELECT project_id, COUNT(*) AS total FROM criteria GROUP BY project_id') as $row) echo $row['project_id'] . '=' . $row['total'] . PHP_EOL;
