<?php declare(strict_types=1); ?>
<main class="stub-page admin-stub">
  <p><a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/forms/builder">Créer un formulaire</a> <a class="btn" href="<?= BASE_URL ?>/admin/forms/planning">Planifier</a></p>
  <h1>Galerie des formulaires</h1>
  <?php if (!$forms): ?><p>Aucun formulaire. Créez votre premier brouillon.</p><?php else: ?>
    <table class="data-table"><thead><tr><th>Titre</th><th>Organisation</th><th>Statut</th><th>Créé le</th><th></th></tr></thead><tbody>
    <?php foreach ($forms as $item): ?><tr><td><?= e($item['title']) ?></td><td><?= e($item['project_title'] ?? '—') ?></td><td><?= e($item['status']) ?></td><td><?= e($item['created_at']) ?></td><td><a href="<?= BASE_URL ?>/admin/forms/builder?id=<?= (int) $item['id'] ?>">Éditer</a></td></tr><?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
</main>
