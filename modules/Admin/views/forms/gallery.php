<?php declare(strict_types=1); $currentReturnUrl = (string) ($_SERVER['REQUEST_URI'] ?? (BASE_URL . '/admin/forms')); ?>
<main class="stub-page admin-stub">
  <p><a class="btn btn-secondary" href="<?= BASE_URL ?>/admin/forms/builder">Créer un formulaire</a> <a class="btn" href="<?= BASE_URL ?>/admin/forms/planning">Planifier</a></p>
  <h1>Galerie des formulaires</h1>
  <?php if (!$forms): ?><p>Aucun formulaire. Créez votre premier brouillon.</p><?php else: ?>
    <table class="data-table"><thead><tr><th>Titre</th><th>Organisation</th><th>Statut</th><th>Créé le</th><th></th></tr></thead><tbody>
    <?php foreach ($forms as $item): ?><tr><td><?= e($item['title']) ?></td><td><?= e($item['project_title'] ?? '—') ?></td><td><?= e($item['status']) ?></td><td><?= e($item['created_at']) ?></td><td style="white-space:nowrap"><a href="<?= BASE_URL ?>/formulaires?edit_form=<?= (int) $item['id'] ?>">Éditer</a> <form method="post" action="<?= BASE_URL ?>/admin/forms/delete" style="display:inline" onsubmit="return confirm('Retirer ce formulaire ? Ses réponses et historiques seront conservés.');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><input type="hidden" name="return_url" value="<?= e($currentReturnUrl) ?>"><button type="submit" class="btn btn-danger">Retirer</button></form></td></tr><?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
</main>
