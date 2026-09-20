<?php declare(strict_types=1); $layout = $form['layout_json'] ?? '[]'; ?>
<main class="builder-page">
  <p><a href="<?= BASE_URL ?>/admin/forms">← Galerie</a></p><h1>Form Builder</h1>
  <?php if (isset($_GET['saved'])): ?><p class="success">Formulaire enregistré.</p><?php endif; ?>
  <form method="post" action="<?= BASE_URL ?>/admin/forms" id="form-builder">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) ($form['id'] ?? 0) ?>">
    <div class="builder-meta"><label>Titre <input required name="title" value="<?= e($form['title'] ?? '') ?>"></label><label>Organisation liée <select name="project_id"><option value="">Aucune</option><?php foreach ($projects as $project): ?><option value="<?= (int) $project['id'] ?>" <?= (int) ($form['project_id'] ?? 0) === (int) $project['id'] ? 'selected' : '' ?>><?= e($project['organization'] ?: $project['title']) ?></option><?php endforeach; ?></select></label><label>Statut <select name="status"><?php foreach (['draft'=>'Brouillon','published'=>'Publié','archived'=>'Archivé'] as $key=>$label): ?><option value="<?= $key ?>" <?= ($form['status'] ?? 'draft') === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></label></div>
    <label class="builder-description">Description <textarea name="description" rows="2"><?= e($form['description'] ?? '') ?></textarea></label>
    <div class="builder-workspace">
      <aside class="builder-tools"><strong>Éléments</strong><div id="element-palette"></div><hr><button type="button" id="toggle-grid">Grille : active</button><button type="button" id="split-element">Scinder</button><button type="button" id="duplicate-element">Dupliquer</button><button type="button" id="delete-element">Supprimer</button></aside>
      <section><div class="builder-toolbar"><span>Glissez les blocs ou utilisez les poignées pour les redimensionner.</span><button type="button" id="undo-builder">Annuler</button><button type="button" id="redo-builder">Rétablir</button></div><div id="builder-canvas" class="grid-on" aria-label="Canvas du formulaire"></div></section>
      <aside class="builder-properties"><strong>Propriétés</strong><div id="property-empty">Sélectionnez un élément.</div><div id="property-editor" hidden>
        <label>Libellé <input data-prop="label"></label><label>Nom technique <input data-prop="name" pattern="[A-Za-z0-9_-]+" title="Lettres, chiffres, tirets et underscores uniquement"></label><label>Texte d’aide <input data-prop="placeholder"></label><label>Type <select data-prop="type"></select></label><label>Options (une par ligne) <textarea data-prop="options" rows="3"></textarea></label><label><input type="checkbox" data-prop="required"> Obligatoire</label><label>Colonnes <input type="range" min="1" max="12" data-prop="span"><output data-out="span"></output></label><label>Lignes <input type="range" min="1" max="6" data-prop="rowSpan"><output data-out="rowSpan"></output></label><label>Échelle <input type="range" min="75" max="125" data-prop="scale"><output data-out="scale"></output>%</label><label>Fond <input type="color" data-prop="background"></label><label>Texte <input type="color" data-prop="color"></label><label>Rayon <input type="range" min="0" max="24" data-prop="radius"><output data-out="radius"></output>px</label>
      </div></aside>
    </div>
    <input type="hidden" id="layout_json" name="layout_json" value="<?= e($layout) ?>"><button class="btn btn-secondary" type="submit">Enregistrer le formulaire</button>
  </form>
</main>
<script src="<?= BASE_URL ?>/assets/js/builder.js"></script><script>window.CritevalBuilder.init({canvas:'builder-canvas',input:'layout_json'});</script>
