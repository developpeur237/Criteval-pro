<?php
declare(strict_types=1);
$layout = $form['layout_json'] ?? '[]';
$availableCriteria = is_array($availableCriteria ?? null) ? $availableCriteria : [];
$formCriteria = is_array($formCriteria ?? null) ? $formCriteria : [];
$formCriteriaConfigured = ($form['criteria_mode'] ?? 'inherit') === 'selected';
$selectedCriteriaIds = $formCriteriaConfigured ? array_map('intval', array_column($formCriteria, 'id')) : array_map('intval', array_column($availableCriteria, 'id'));
?>
<main class="stub-page admin-stub builder-shell-wrap">
  <div class="builder-page">
    <div class="builder-header">
      <div>
        <a class="builder-back" href="<?= BASE_URL ?>/admin/forms">← Retour à la galerie</a>
        <div class="builder-title-row">
          <div class="builder-title-icon" aria-hidden="true">✦</div>
          <div>
            <p class="builder-eyebrow">Gestion des formulaires</p>
            <h1>Modifier le formulaire</h1>
            <p class="builder-subtitle">Structurez une expérience claire et professionnelle pour vos candidats.</p>
          </div>
        </div>
      </div>
      <?php if (isset($_GET['saved'])): ?><p class="builder-saved"><span aria-hidden="true">✓</span> Formulaire enregistré</p><?php endif; ?>
    </div>
    <form method="post" action="<?= BASE_URL ?>/admin/forms" id="form-builder">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="id" value="<?= (int) ($form['id'] ?? 0) ?>">
      <section class="builder-details" aria-labelledby="builder-details-title">
        <div class="builder-section-heading">
          <div><span class="builder-section-kicker">01</span><h2 id="builder-details-title">Informations générales</h2></div>
          <p>Donnez à votre formulaire un nom et un contexte reconnaissables.</p>
        </div>
        <div class="builder-meta">
          <label class="builder-field builder-field-wide"><span>Titre du formulaire <b>*</b></span><input required name="title" value="<?= e($form['title'] ?? '') ?>" placeholder="Ex. Candidature au programme 2026"></label>
          <label class="builder-field"><span>Organisation liée</span>
          <select name="project_id">
            <option value="">Aucune</option>
            <?php foreach ($projects as $project): ?>
              <option value="<?= (int) $project['id'] ?>" <?= (int) ($form['project_id'] ?? 0) === (int) $project['id'] ? 'selected' : '' ?>><?= e($project['organization'] ?: $project['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="builder-field"><span>Statut</span>
          <select name="status">
            <?php foreach (['draft' => 'Brouillon', 'published' => 'Publié', 'archived' => 'Archivé'] as $key => $label): ?>
              <option value="<?= $key ?>" <?= ($form['status'] ?? 'draft') === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        </div>
        <label class="builder-field builder-description"><span>Description <small>Optionnel</small></span><textarea name="description" rows="2" placeholder="Expliquez brièvement l’objectif de ce formulaire…"><?= e($form['description'] ?? '') ?></textarea></label>
      </section>
      <section class="builder-details criteria-selection-panel" aria-labelledby="criteria-selection-title">
        <div class="builder-section-heading">
          <div><span class="builder-section-kicker">02</span><h2 id="criteria-selection-title">Critères utilisés par les candidats</h2></div>
          <p>Choisissez les critères affichés dans ce formulaire. Sans sélection personnalisée, tous les critères de l’organisation sont utilisés.</p>
        </div>
        <input type="hidden" name="criteria_selection_submitted" value="1">
        <?php if ($availableCriteria): ?>
          <div class="criteria-selection-grid">
            <?php foreach ($availableCriteria as $criterion): ?>
              <label class="criteria-selection-item">
                <input type="checkbox" name="criteria_ids[]" value="<?= (int) $criterion['id'] ?>" <?= in_array((int) $criterion['id'], $selectedCriteriaIds, true) ? 'checked' : '' ?>>
                <span><strong><?= e((string) $criterion['label']) ?></strong><small><?= e((string) ($criterion['description'] ?? '')) ?> · <?= (float) ($criterion['max_score'] ?? 20) ?> points<?= !empty($criterion['is_required']) ? ' · obligatoire' : '' ?></small></span>
              </label>
            <?php endforeach; ?>
          </div>
          <p class="criteria-selection-hint">Les critères non cochés resteront disponibles dans l’organisation, mais ne seront pas demandés aux candidats pour ce formulaire.</p>
        <?php else: ?>
          <div class="criteria-selection-empty">Associez d’abord une organisation pour sélectionner ses critères. <a href="<?= BASE_URL ?>/admin/criteria">Gérer les critères</a></div>
        <?php endif; ?>
      </section>
      <section class="builder-editor-section" aria-labelledby="builder-editor-title">
        <div class="builder-section-heading builder-section-heading-editor">
          <div><span class="builder-section-kicker">03</span><h2 id="builder-editor-title">Construisez votre formulaire</h2></div>
          <p>Ajoutez, personnalisez et organisez les champs de votre formulaire.</p>
        </div>
      <div class="builder-workspace">
        <aside class="builder-tools" aria-label="Éléments disponibles">
          <div class="builder-panel-heading"><span class="builder-panel-icon">+</span><div><strong>Éléments</strong><small>Glissez ou cliquez pour ajouter</small></div></div>
          <div id="element-palette"></div>
          <div class="builder-tool-actions">
            <p>Actions sur la sélection</p>
            <button type="button" id="toggle-grid">▦ <span>Grille : active</span></button>
            <button type="button" id="split-element">◫ <span>Scinder</span></button>
            <button type="button" id="duplicate-element">⧉ <span>Dupliquer</span></button>
            <button type="button" id="delete-element" class="builder-delete">⌫ <span>Supprimer</span></button>
          </div>
        </aside>
        <section>
          <div class="builder-toolbar">
            <span><b>Aperçu du formulaire</b><small>Glissez les blocs ou utilisez les poignées pour les redimensionner.</small></span>
            <div><button type="button" id="undo-builder" title="Annuler">↶ <span>Annuler</span></button><button type="button" id="redo-builder" title="Rétablir">↷ <span>Rétablir</span></button></div>
          </div>
          <div id="builder-canvas" class="grid-on" aria-label="Canvas du formulaire"></div>
        </section>
        <aside class="builder-properties" aria-label="Propriétés de l'élément">
          <div class="builder-panel-heading"><span class="builder-panel-icon">⚙</span><div><strong>Propriétés</strong><small>Personnalisez l’élément sélectionné</small></div></div>
          <div id="property-empty">Sélectionnez un élément.</div>
          <div id="property-editor" hidden>
            <label>Libellé <input data-prop="label"></label>
            <label>Nom technique <input data-prop="name" pattern="[A-Za-z0-9_-]+" title="Lettres, chiffres, tirets et underscores uniquement"></label>
            <label>Texte d’aide <input data-prop="placeholder"></label>
            <label>Type <select data-prop="type"></select></label>
            <label>Options (une par ligne) <textarea data-prop="options" rows="3"></textarea></label>
            <label><input type="checkbox" data-prop="required"> Obligatoire</label>
            <label>Colonnes <input type="range" min="1" max="12" data-prop="span"><output data-out="span"></output></label>
            <label>Lignes <input type="range" min="1" max="6" data-prop="rowSpan"><output data-out="rowSpan"></output></label>
            <label>Échelle <input type="range" min="75" max="125" data-prop="scale"><output data-out="scale"></output>%</label>
            <label>Fond <input type="color" data-prop="background"></label>
            <label>Texte <input type="color" data-prop="color"></label>
            <label>Rayon <input type="range" min="0" max="24" data-prop="radius"><output data-out="radius"></output>px</label>
          </div>
        </aside>
      </div>
      </section>
      <input type="hidden" id="layout_json" name="layout_json" value="<?= e($layout) ?>">
      <div class="builder-footer"><span><span class="builder-required-dot">●</span> Les champs marqués d’un astérisque sont obligatoires.</span><button class="btn btn-secondary builder-submit" type="submit"><span aria-hidden="true">✓</span> Enregistrer le formulaire</button></div>
    </form>
  </div>
</main>
<style>
  .builder-shell-wrap { background: linear-gradient(180deg, #f5f7fb 0%, #edf3f7 100%); }
  .builder-shell-wrap .builder-page { max-width: 1500px; margin: 0 auto; padding: 24px; color: #243447; }
  .builder-shell-wrap .builder-meta { display: flex; gap: 16px; flex-wrap: wrap; margin: 16px 0; }
  .builder-shell-wrap .builder-meta label, .builder-shell-wrap .builder-description, .builder-shell-wrap .builder-properties label { display: grid; gap: 6px; }
  .builder-shell-wrap .builder-meta input, .builder-shell-wrap .builder-meta select, .builder-shell-wrap .builder-description textarea, .builder-shell-wrap .builder-properties input, .builder-shell-wrap .builder-properties select { max-width: 100%; padding: 8px; border: 1px solid #d8dee8; border-radius: 7px; }
  .builder-shell-wrap .builder-description { max-width: 780px; margin-bottom: 16px; }
  .builder-shell-wrap .builder-workspace { display: grid; grid-template-columns: 190px minmax(0, 1fr) 250px; gap: 16px; align-items: start; }
  .builder-shell-wrap .builder-tools, .builder-shell-wrap .builder-properties, .builder-shell-wrap .builder-toolbar { background: #fff; border: 1px solid #d8dee8; border-radius: 12px; padding: 12px; box-shadow: 0 2px 10px rgba(16, 42, 67, 0.08); }
  .builder-shell-wrap .builder-tools { display: grid; gap: 8px; position: sticky; top: 12px; }
  .builder-shell-wrap .builder-tools button, .builder-shell-wrap #element-palette button { padding: 8px; text-align: left; background: #f6f8fb; border: 1px solid #d8dee8; border-radius: 7px; cursor: pointer; }
  .builder-shell-wrap .builder-tools button:hover, .builder-shell-wrap #element-palette button:hover { border-color: #2eaf7d; background: #eaf8f2; }
  .builder-shell-wrap .builder-toolbar { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
  .builder-shell-wrap .builder-toolbar button { border: 0; background: #1a3c5e; color: #fff; padding: 7px 10px; border-radius: 6px; }
  .builder-shell-wrap .builder-properties { position: sticky; top: 12px; }
  .builder-shell-wrap .builder-properties label { margin: 10px 0; font-size: .88rem; }
  .builder-shell-wrap .builder-properties output { margin-left: 5px; }
  .builder-shell-wrap .builder-properties input[type=range] { padding: 0; }
  .builder-shell-wrap .builder-properties input[type=color] { height: 34px; padding: 2px; }
  .builder-shell-wrap .builder-properties [data-prop=required] { width: auto; }
  .builder-shell-wrap .builder-properties label:has([data-prop=required]) { display: flex; align-items: center; gap: 7px; }
  .builder-shell-wrap .builder-element { min-width: 0; background: var(--builder-bg); color: var(--builder-color); border: 1px solid #d5dce7; border-radius: var(--builder-radius); padding: 10px; position: relative; transform: scale(var(--builder-scale)); transform-origin: top left; cursor: pointer; }
  .builder-shell-wrap .builder-element.selected { outline: 3px solid #2eaf7d; z-index: 2; }
  .builder-shell-wrap .builder-element-head { display: flex; justify-content: space-between; gap: 8px; }
  .builder-shell-wrap .builder-element-head .remove { cursor: pointer; }
  .builder-shell-wrap #builder-canvas { min-height: 420px; border: 1px solid #d8dee8; border-radius: 12px; background: repeating-linear-gradient(90deg, rgba(15, 23, 42, 0.02), rgba(15, 23, 42, 0.02) 1px, transparent 1px, transparent 24px), rgba(255,255,255,0.5); }
  @media (max-width: 980px) { .builder-shell-wrap .builder-workspace { grid-template-columns: 1fr; } .builder-shell-wrap .builder-tools { position: static; grid-template-columns: repeat(2, minmax(0, 1fr)); } .builder-shell-wrap .builder-properties { position: static; } .builder-shell-wrap .builder-toolbar { flex-wrap: wrap; } }
</style>
<style>
  /* Builder refresh: align the editor with the Criteval admin design system. */
  .builder-shell-wrap { max-width: none; width: 100%; margin: 0; padding: 0; border: 0; border-radius: 0; background: #f4f6fa; }
  .builder-shell-wrap .builder-page { max-width: 1600px; padding: 34px clamp(20px, 3vw, 52px) 42px; color: var(--text-main, #2c3e50); }
  .builder-header { display: flex; justify-content: space-between; align-items: flex-end; gap: 24px; margin-bottom: 28px; }
  .builder-back { color: var(--text-muted); font-size: 13px; font-weight: 600; text-decoration: none; }
  .builder-back:hover { color: var(--secondary); }
  .builder-title-row { display: flex; align-items: center; gap: 16px; margin-top: 16px; }
  .builder-title-icon { display: grid; place-items: center; width: 48px; height: 48px; border-radius: 14px; color: #fff; font-size: 23px; background: linear-gradient(135deg, var(--primary), #276184); box-shadow: 0 8px 18px rgba(26,60,94,.18); }
  .builder-eyebrow, .builder-section-kicker { color: var(--secondary); font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
  .builder-shell-wrap h1 { margin: 3px 0 4px; color: var(--primary); font-family: Poppins, Inter, sans-serif; font-size: clamp(25px, 3vw, 32px); letter-spacing: -.03em; }
  .builder-subtitle { color: var(--text-muted); font-size: 14px; }
  .builder-saved { display: inline-flex; align-items: center; gap: 8px; margin: 0; padding: 9px 13px; border: 1px solid #bde8d6; border-radius: 9px; color: #21845c; background: #eaf8f2; font-size: 13px; font-weight: 700; }
  .builder-details, .builder-editor-section { margin-bottom: 24px; padding: 24px; border: 1px solid var(--border); border-radius: 16px; background: #fff; box-shadow: var(--shadow); }
  .builder-section-heading { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 22px; }
  .builder-section-heading > div { display: flex; align-items: center; gap: 10px; }
  .builder-section-heading h2 { color: var(--primary); font-family: Poppins, Inter, sans-serif; font-size: 17px; }
  .builder-section-heading p { max-width: 430px; margin-top: 3px; color: var(--text-muted); font-size: 13px; text-align: right; }
  .builder-section-kicker { display: inline-grid; place-items: center; width: 27px; height: 27px; border-radius: 8px; background: var(--secondary-light); }
  .builder-meta { display: grid; grid-template-columns: minmax(260px, 1.8fr) minmax(200px, 1fr) minmax(160px, .7fr); gap: 16px; margin: 0 0 17px; }
  .builder-field { display: grid; gap: 8px; min-width: 0; color: var(--text-main); font-size: 12px; font-weight: 700; }
  .builder-field span { display: flex; justify-content: space-between; align-items: center; }
  .builder-field b { color: var(--danger); }
  .builder-field small { color: var(--text-muted); font-size: 11px; font-weight: 500; }
  .builder-field input, .builder-field select, .builder-field textarea, .builder-properties input, .builder-properties select, .builder-properties textarea { width: 100%; min-height: 42px; padding: 10px 12px; border: 1px solid var(--border); border-radius: 9px; outline: none; background: #fbfcfe; color: var(--text-main); font: inherit; font-size: 13px; transition: border-color .2s, box-shadow .2s, background .2s; }
  .builder-field textarea { min-height: 78px; resize: vertical; }
  .builder-field input:focus, .builder-field select:focus, .builder-field textarea:focus, .builder-properties input:focus, .builder-properties select:focus, .builder-properties textarea:focus { border-color: var(--secondary); background: #fff; box-shadow: 0 0 0 3px var(--secondary-light); }
  .builder-workspace { grid-template-columns: 218px minmax(0, 1fr) 286px; gap: 18px; }
  .builder-tools, .builder-properties, .builder-toolbar { border: 1px solid var(--border); border-radius: 14px; box-shadow: var(--shadow); }
  .builder-tools, .builder-properties { padding: 16px; background: #fff; }
  .builder-tools { gap: 0; }
  .builder-panel-heading { display: flex; align-items: center; gap: 10px; padding-bottom: 15px; border-bottom: 1px solid #edf0f4; }
  .builder-panel-heading strong, .builder-panel-heading small { display: block; }
  .builder-panel-heading strong { color: var(--primary); font-family: Poppins, Inter, sans-serif; font-size: 14px; }
  .builder-panel-heading small { margin-top: 3px; color: var(--text-muted); font-size: 10px; font-weight: 500; }
  .builder-panel-icon { display: grid; place-items: center; width: 30px; height: 30px; border-radius: 9px; color: var(--secondary); background: var(--secondary-light); font-size: 17px; font-weight: 800; }
  #element-palette { display: grid; gap: 7px; padding: 16px 0; }
  .builder-tools button, #element-palette button { display: flex; align-items: center; gap: 9px; width: 100%; min-height: 36px; padding: 8px 10px; border: 1px solid #e6eaf0; border-radius: 8px; color: var(--text-main); background: #fbfcfe; font: inherit; font-size: 12px; font-weight: 600; transition: .2s; }
  .builder-tools button:hover, #element-palette button:hover { border-color: var(--secondary); color: #21845c; background: #eaf8f2; transform: translateX(2px); }
  .builder-tool-actions { padding-top: 13px; border-top: 1px solid #edf0f4; }
  .builder-tool-actions p { margin-bottom: 8px; color: var(--text-muted); font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
  .builder-tool-actions button { margin-top: 6px; padding: 7px 9px; border-color: transparent; background: #f4f6fa; }
  .builder-tool-actions button:first-letter { color: var(--primary); font-size: 16px; }
  .builder-tool-actions .builder-delete { color: var(--danger); }
  .builder-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 62px; margin-bottom: 12px; padding: 12px 16px; background: #fff; }
  .builder-toolbar > span { color: var(--primary); font-size: 12px; }
  .builder-toolbar > span b, .builder-toolbar > span small { display: block; }
  .builder-toolbar > span small { margin-top: 3px; color: var(--text-muted); font-size: 11px; font-weight: 400; }
  .builder-toolbar > div { display: flex; gap: 6px; }
  .builder-toolbar button { min-height: 32px; padding: 6px 10px; border: 1px solid var(--border); border-radius: 7px; color: var(--primary); background: #fff; font: inherit; font-size: 12px; font-weight: 700; }
  .builder-toolbar button:hover { border-color: var(--primary); background: var(--primary-light); }
  .builder-workspace #builder-canvas { position: relative; min-height: 640px; padding: 20px; border: 1px solid #dce4ed; border-radius: 14px; background-color: #f8fafc; box-shadow: inset 0 1px 3px rgba(26,60,94,.04); }
  .builder-workspace #builder-canvas.grid-on { background-image: linear-gradient(#e5ebf1 1px, transparent 1px), linear-gradient(90deg, #e5ebf1 1px, transparent 1px); background-size: 12px 12px; }
  .builder-shell-wrap .builder-element { border: 1px solid #d8e1e9; border-radius: var(--builder-radius); padding: 14px; box-shadow: 0 2px 6px rgba(26,60,94,.06); transition: box-shadow .2s, border-color .2s; }
  .builder-shell-wrap .builder-element:hover { border-color: #9bcfb9; box-shadow: 0 5px 14px rgba(26,60,94,.1); }
  .builder-shell-wrap .builder-element.selected { outline: 3px solid rgba(46,175,125,.22); border-color: var(--secondary); box-shadow: 0 0 0 1px var(--secondary), 0 7px 18px rgba(46,175,125,.14); }
  .builder-shell-wrap .edge-handle, .builder-shell-wrap .corner-handle, .builder-shell-wrap .scale-handle { display: none; position: absolute; z-index: 999; pointer-events: auto; touch-action: none; background: var(--secondary); border: 2px solid #fff; box-shadow: 0 1px 4px rgba(15,23,42,.25); }
  .builder-shell-wrap .builder-element.selected .edge-handle, .builder-shell-wrap .builder-element.selected .corner-handle, .builder-shell-wrap .builder-element.selected .scale-handle { display: block; }
  .builder-shell-wrap .edge-handle { border-radius: 4px; }
  .builder-shell-wrap .edge-left, .builder-shell-wrap .edge-right { top: 18%; bottom: 18%; width: 14px; cursor: ew-resize; }
  .builder-shell-wrap .edge-left { left: -8px; } .builder-shell-wrap .edge-right { right: -8px; }
  .builder-shell-wrap .edge-top, .builder-shell-wrap .edge-bottom { left: 18%; right: 18%; height: 14px; cursor: ns-resize; }
  .builder-shell-wrap .edge-top { top: -8px; } .builder-shell-wrap .edge-bottom { bottom: -8px; }
  .builder-shell-wrap .corner-handle { width: 18px; height: 18px; border-radius: 50%; }
  .builder-shell-wrap .corner-tl { top: -10px; left: -10px; cursor: nwse-resize; } .builder-shell-wrap .corner-tr { top: -10px; right: -10px; cursor: nesw-resize; }
  .builder-shell-wrap .corner-bl { bottom: -10px; left: -10px; cursor: nesw-resize; } .builder-shell-wrap .corner-br { bottom: -10px; right: -10px; cursor: nwse-resize; }
  .builder-shell-wrap .scale-handle { right: -12px; bottom: -12px; width: 24px; height: 24px; border-radius: 50%; background: #f5a623; cursor: nwse-resize; }
  .builder-shell-wrap .builder-element.is-moving { cursor: grabbing; user-select: none; }
  .builder-shell-wrap .builder-element input, .builder-shell-wrap .builder-element select, .builder-shell-wrap .builder-element textarea { width: 100%; box-sizing: border-box; border: 1px solid var(--builder-input-border, #d5dce7); border-radius: var(--builder-input-radius, 8px); padding: var(--builder-input-padding, 12px); background: var(--builder-input-bg, #fff); color: inherit; accent-color: var(--builder-accent, #2eaf7d); font: inherit; }
  .builder-shell-wrap .builder-element textarea { min-height: 72px; resize: none; }
  .builder-shell-wrap .builder-element input[type=file] { padding: 8px; }
  .builder-shell-wrap .builder-element h3, .builder-shell-wrap .builder-element p, .builder-shell-wrap .builder-element hr { color: inherit; }
  .builder-shell-wrap .builder-element hr { border: 0; border-top: 2px solid currentColor; opacity: .35; }
  .builder-shell-wrap .builder-element-head { color: var(--text-muted); font-size: 11px; }
  .builder-shell-wrap .builder-element-head button { color: var(--danger); }
  .builder-properties { min-height: 260px; }
  .builder-properties #property-empty { padding: 24px 8px; color: var(--text-muted); font-size: 12px; line-height: 1.6; text-align: center; }
  .builder-properties #property-editor { padding-top: 6px; }
  .builder-properties label { margin: 12px 0; color: var(--text-main); font-size: 11px; font-weight: 700; }
  .builder-properties input, .builder-properties select, .builder-properties textarea { min-height: 36px; padding: 8px 10px; font-size: 12px; }
  .builder-properties input[type=range] { min-height: 0; padding: 0; accent-color: var(--secondary); }
  .builder-properties input[type=color] { min-height: 35px; padding: 3px; }
  .builder-properties label:has([data-prop=required]) { display: flex; align-items: center; gap: 8px; padding: 9px 10px; border-radius: 8px; background: #f7f9fb; }
  .builder-properties [data-prop=required] { width: 16px; min-height: 16px; accent-color: var(--secondary); }
  .builder-advanced-properties { margin-top: 16px; padding-top: 10px; border-top: 1px solid #edf0f4; }
  .builder-property-title { margin: 15px 0 8px; color: var(--secondary); font-size: 9px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
  .builder-property-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
  .builder-property-grid.four { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 5px; }
  .builder-property-grid label { min-width: 0; }
  .builder-property-grid label > span { display: grid; grid-template-columns: minmax(0, 1fr) 48px; gap: 4px; }
  .builder-range-line { display: flex; align-items: center; gap: 6px; }
  .builder-range-line input { min-width: 0; }
  .builder-range-line output { min-width: 26px; }
  .builder-footer { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border); color: var(--text-muted); font-size: 12px; }
  .builder-required-dot { color: var(--secondary); font-size: 9px; }
  .builder-submit { padding: 12px 20px; border-radius: 9px; }
  @media (max-width: 1180px) { .builder-workspace { grid-template-columns: 190px minmax(0, 1fr); } .builder-properties { grid-column: 1 / -1; position: static; } .builder-properties #property-editor { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 18px; } }
  @media (max-width: 760px) { .builder-shell-wrap .builder-page { padding: 24px 14px 32px; } .builder-header, .builder-section-heading, .builder-footer { align-items: flex-start; flex-direction: column; } .builder-section-heading p { text-align: left; } .builder-meta { grid-template-columns: 1fr; } .builder-details, .builder-editor-section { padding: 17px; } .builder-workspace { grid-template-columns: 1fr; } .builder-tools { position: static; } .builder-tools #element-palette { grid-template-columns: repeat(2, minmax(0, 1fr)); } .builder-properties { grid-column: auto; } .builder-properties #property-editor { display: block; } .builder-toolbar { align-items: flex-start; flex-direction: column; } .builder-workspace #builder-canvas { min-height: 460px; padding: 12px; } .builder-footer { align-items: stretch; } .builder-submit { justify-content: center; } }
  .builder-shell-wrap .builder-meta { display: grid; grid-template-columns: minmax(260px, 1.8fr) minmax(200px, 1fr) minmax(160px, .7fr); gap: 16px; margin: 0 0 17px; }
  .builder-shell-wrap .builder-description { max-width: none; margin: 0; }
  .criteria-selection-panel { margin-top: 18px; }
  .criteria-selection-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 16px; }
  .criteria-selection-item { display: flex; gap: 10px; align-items: flex-start; padding: 13px; border: 1px solid #d8dee8; border-radius: 10px; background: #fff; cursor: pointer; }
  .criteria-selection-item:has(input:checked) { border-color: #2eaf7d; background: #f0fbf6; }
  .criteria-selection-item input { margin-top: 3px; accent-color: #2eaf7d; }
  .criteria-selection-item span { display: grid; gap: 4px; color: #243447; }
  .criteria-selection-item small, .criteria-selection-hint { color: #64748b; line-height: 1.45; }
  .criteria-selection-empty { padding: 16px; border: 1px dashed #cbd5e1; border-radius: 10px; color: #64748b; }
  .criteria-selection-empty a { color: #16865b; font-weight: 700; }
  @media (max-width: 760px) { .criteria-selection-grid { grid-template-columns: 1fr; } }
  .builder-shell-wrap .builder-workspace { grid-template-columns: 218px minmax(0, 1fr) 286px; gap: 18px; }
  .builder-shell-wrap .builder-tools, .builder-shell-wrap .builder-properties { padding: 16px; }
  .builder-shell-wrap .builder-properties label { margin: 12px 0; font-size: 11px; }
  .builder-shell-wrap .builder-toolbar button { min-height: 32px; padding: 6px 10px; border: 1px solid var(--border); border-radius: 7px; color: var(--primary); background: #fff; font: inherit; font-size: 12px; font-weight: 700; }
  .builder-shell-wrap .builder-toolbar button:hover { border-color: var(--primary); background: var(--primary-light); }
  .builder-shell-wrap .builder-workspace #builder-canvas { min-height: 640px; padding: 20px; border-radius: 14px; }
  @media (max-width: 1180px) { .builder-shell-wrap .builder-workspace { grid-template-columns: 190px minmax(0, 1fr); } }
  @media (max-width: 760px) { .builder-shell-wrap .builder-meta { grid-template-columns: 1fr; } .builder-shell-wrap .builder-workspace { grid-template-columns: 1fr; } .builder-shell-wrap .builder-workspace #builder-canvas { min-height: 460px; padding: 12px; } }
</style>
<script src="<?= BASE_URL ?>/assets/js/builder.js?v=20260923-advanced"></script>
<script>window.CritevalBuilder.init({canvas:'builder-canvas',input:'layout_json'});</script>
