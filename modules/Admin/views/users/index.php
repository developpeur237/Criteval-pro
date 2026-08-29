<?php
/** @var array $users */
$users = $users ?? [];
$permissionModules = permission_module_keys();
$permissionLabels = permission_module_labels();
$permissionActions = permission_action_labels();
$roleLabels = role_labels();
$rolePermissionDefaults = [
    'superadmin' => default_permissions_for_role('superadmin'),
    'admin' => default_permissions_for_role('admin'),
    'user' => default_permissions_for_role('user'),
    'visitor' => default_permissions_for_role('visitor'),
];
?>

<div class="dash-module" id="module-users">
  <div class="module-header">
    <div>
      <h1 class="module-title">Gestion des utilisateurs</h1>
      <p class="module-sub">Créer, modifier et gérer l'accès des administrateurs dans l'application.</p>
    </div>
    <div class="module-actions">
      <button class="btn btn-secondary" id="btnNewUser" type="button"><i class="fas fa-user-plus"></i> Nouvel utilisateur</button>
    </div>
  </div>

  <div style="padding:18px">
    <table class="table" style="width:100%;border-collapse:collapse">
      <thead>
        <tr style="text-align:left;color:rgba(0,0,0,0.7)">
          <th>Nom</th>
          <th>Email</th>
          <th>Rôle</th>
          <th>Actif</th>
          <th style="width:180px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e(role_label($u['role'])) ?></td>
            <td><?= $u['is_active'] ? 'Oui' : 'Non' ?></td>
            <td>
              <button class="btn btn-ghost-dark btn-sm btnEditUser" data-user='<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>Modifier</button>
              <button class="btn btn-danger btn-sm btnDeleteUser" data-id="<?= $u['id'] ?>">Supprimer</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="modalUser" role="dialog" aria-modal="true" aria-labelledby="userModalTitle" onclick="if(event.target===this)closeUserModal()">
  <div class="modal" style="max-width:760px">
    <div class="modal-header">
      <div class="modal-title" id="userModalTitle"><i class="fas fa-user-plus" style="color:var(--secondary);margin-right:8px"></i> Nouvel utilisateur</div>
      <button class="modal-close" type="button" aria-label="Fermer" onclick="closeUserModal()"><i class="fas fa-times"></i></button>
    </div>
    <form id="userForm">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px">
          <div><label class="form-label" for="userName">Nom complet *</label><input id="userName" class="form-input-dark" style="width:100%" type="text" name="name" required autocomplete="name"></div>
          <div><label class="form-label" for="userEmail">Email *</label><input id="userEmail" class="form-input-dark" style="width:100%" type="email" name="email" required autocomplete="email"></div>
          <div><label class="form-label" for="userUsername">Nom d'utilisateur *</label><input id="userUsername" class="form-input-dark" style="width:100%" type="text" name="username" required minlength="3" autocomplete="username"></div>
          <div><label class="form-label" for="userRole">Rôle *</label><select id="userRole" class="form-input-dark" style="width:100%" name="role"><?php foreach ($roleLabels as $roleKey => $roleLabel): ?><option value="<?= e($roleKey) ?>"><?= e($roleLabel) ?></option><?php endforeach; ?></select></div>
          <div><label class="form-label" for="userPassword">Mot de passe *</label><input id="userPassword" class="form-input-dark" style="width:100%" type="password" name="password" minlength="8" required autocomplete="new-password"><small class="settings-desc" id="userPasswordHint">8 caractères minimum.</small></div>
          <div style="display:flex;align-items:center;padding-top:22px"><label style="display:flex;align-items:center;gap:8px;color:rgba(255,255,255,0.75)"><input type="checkbox" name="is_active" value="1" checked> Compte actif</label></div>
        </div>
        <div style="margin-top:18px">
          <div class="settings-label">Droits par module</div>
          <div class="settings-desc" style="margin-top:4px">Voir, créer, modifier et supprimer pour chaque module.</div>
          <div style="display:grid;gap:10px;margin-top:10px">
            <?php foreach ($permissionLabels as $key => $label): ?>
              <div style="display:grid;grid-template-columns:minmax(140px,1fr) repeat(4,minmax(84px,auto));gap:10px;align-items:center;padding:10px 12px;border:1px solid rgba(255,255,255,0.06);border-radius:8px;background:rgba(255,255,255,0.02)">
                <div style="color:#fff;font-weight:600"><?= e($label) ?></div>
                <?php foreach ($permissionActions as $actionKey => $actionLabel): ?>
                  <label style="display:flex;align-items:center;gap:6px;color:rgba(255,255,255,0.72);font-size:13px;justify-content:flex-start">
                    <input type="checkbox" name="permissions[modules][<?= e($key) ?>][<?= e($actionKey) ?>]" value="1"> <?= e($actionLabel) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <p id="userFormStatus" class="settings-desc" role="status" style="margin:14px 0 0"></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-ghost-dark" type="button" onclick="closeUserModal()">Annuler</button>
        <button class="btn btn-secondary" type="submit" id="userSaveBtn"><i class="fas fa-save"></i> Créer l'utilisateur</button>
      </div>
    </form>
  </div>
</div>

<script>
const rolePermissionDefaults = <?= json_encode($rolePermissionDefaults, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
$(function() {
  const form = document.getElementById('userForm');
  const field = (name) => form.elements.namedItem(name);
  const title = document.getElementById('userModalTitle');
  const submitBtn = document.getElementById('userSaveBtn');
  const status = document.getElementById('userFormStatus');
  const passwordInput = document.getElementById('userPassword');
  const passwordHint = document.getElementById('userPasswordHint');
  const roleSelect = document.getElementById('userRole');

  function setMode(isEdit) {
    title.innerHTML = `<i class="fas fa-user-${isEdit ? 'edit' : 'plus'}" style="color:var(--secondary);margin-right:8px"></i> ${isEdit ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur'}`;
    submitBtn.innerHTML = `<i class="fas fa-save"></i> ${isEdit ? 'Mettre à jour' : 'Créer l\'utilisateur'}`;
    passwordInput.required = !isEdit;
    passwordInput.value = '';
    passwordInput.placeholder = isEdit ? 'Laisser vide pour conserver le mot de passe' : '';
    passwordHint.textContent = isEdit ? 'Laisser vide pour conserver le mot de passe.' : '8 caractères minimum.';
  }

  function resetPermissions() {
    form.querySelectorAll('input[name^="permissions"]').forEach(input => { input.checked = false; });
  }

  function applyRoleDefaults(role) {
    const defaults = rolePermissionDefaults[role] || rolePermissionDefaults.user;
    Object.entries(defaults.modules || {}).forEach(([moduleKey, modulePerms]) => {
      Object.entries(modulePerms || {}).forEach(([action, enabled]) => {
        const input = form.querySelector(`input[name="permissions[modules][${moduleKey}][${action}]"]`);
        if (input) input.checked = Boolean(enabled);
      });
    });
  }

  window.openUserModal = function(user = null) {
    form.reset();
    field('id').value = '';
    status.textContent = '';
    resetPermissions();
    const selectedRole = user && user.role ? user.role : 'user';
    roleSelect.value = selectedRole;
    setMode(Boolean(user));
    applyRoleDefaults(selectedRole);

    if (user) {
      field('id').value = user.id;
      field('name').value = user.name || '';
      field('email').value = user.email || '';
      field('username').value = user.username || '';
      field('role').value = user.role || 'user';
      field('is_active').checked = user.is_active == 1;

      try {
        const perms = typeof user.permissions === 'string' ? JSON.parse(user.permissions) : (user.permissions || {});
        if (perms.modules) {
          Object.entries(perms.modules).forEach(([moduleKey, modulePerms]) => {
            if (modulePerms && typeof modulePerms === 'object') {
              Object.entries(modulePerms).forEach(([action, enabled]) => {
                const input = form.querySelector(`input[name="permissions[modules][${moduleKey}][${action}]"]`);
                if (input) input.checked = Boolean(enabled);
              });
            } else {
              const input = form.querySelector(`input[name="permissions[modules][${moduleKey}][view]"]`);
              if (input) input.checked = Boolean(modulePerms);
            }
          });
        }
      } catch (error) {}
    }

    $('#modalUser').addClass('open');
  };

  window.closeUserModal = function() {
    $('#modalUser').removeClass('open');
  };

  $('#btnNewUser').on('click', function() {
    openUserModal();
  });

  roleSelect.addEventListener('change', function() {
    if (field('id').value) return;
    resetPermissions();
    applyRoleDefaults(roleSelect.value);
  });

  $('.btnEditUser').on('click', function() {
    openUserModal($(this).data('user'));
  });

  $('.btnDeleteUser').on('click', async function() {
    if (!confirm('Supprimer cet utilisateur ?')) return;
    const fd = new FormData();
    fd.append('csrf_token', '<?= e(csrf_token()) ?>');
    fd.append('id', $(this).data('id'));

    const response = await fetch('<?= BASE_URL ?>/admin/users/delete', { method: 'POST', body: fd });
    const result = await response.json().catch(() => ({}));
    if (response.ok && result.success) {
      showToast('Utilisateur supprimé', 'success');
      location.reload();
    } else {
      showToast(result.message || 'Erreur', 'danger');
    }
  });

  form.addEventListener('submit', async function(event) {
    event.preventDefault();
    if (!form.reportValidity()) return;

    submitBtn.disabled = true;
    status.textContent = 'Enregistrement en cours...';

    try {
      const response = await fetch('<?= BASE_URL ?>/admin/users', { method: 'POST', body: new FormData(form) });
      const result = await response.json().catch(() => ({}));
      if (!response.ok || !result.success) throw new Error(result.message || 'Impossible d\'enregistrer l\'utilisateur.');
      showToast(result.message || 'Utilisateur créé avec succès.', 'success');
      closeUserModal();
      location.reload();
    } catch (error) {
      status.textContent = error.message || 'Erreur lors de l\'enregistrement.';
      showToast(status.textContent, 'danger');
    } finally {
      submitBtn.disabled = false;
    }
  });
});
</script>
