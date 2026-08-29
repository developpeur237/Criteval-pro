<?php declare(strict_types=1); ?>
<main class="stub-page">
  <h1>Acces au formulaire</h1>
  <p>Saisissez votre email pour recevoir votre code candidat.</p>

  <form id="candidateOtpRequestForm" method="post" action="<?= BASE_URL ?>/otp">
    <label>Email candidat</label>
    <input type="email" name="email" required autocomplete="email">
    <input type="hidden" name="form_id" value="<?= e((string) ($_GET['form_id'] ?? 1)) ?>">
    <button type="submit">Recevoir mon code</button>
  </form>

  <form id="candidateOtpVerifyForm" method="post" action="<?= BASE_URL ?>/otp/verify" hidden>
    <label>Code a 6 chiffres</label>
    <input name="otp" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required>
    <input type="hidden" name="email">
    <input type="hidden" name="form_id" value="<?= e((string) ($_GET['form_id'] ?? 1)) ?>">
    <button type="submit">Verifier le code</button>
  </form>

  <p id="candidateOtpStatus" role="status"></p>
</main>

<script>
(function () {
  const requestForm = document.getElementById('candidateOtpRequestForm');
  const verifyForm = document.getElementById('candidateOtpVerifyForm');
  const status = document.getElementById('candidateOtpStatus');
  if (!requestForm || !verifyForm || !status) return;

  requestForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    status.textContent = 'Envoi du code...';
    const response = await fetch(requestForm.action, { method: 'POST', body: new FormData(requestForm) });
    const result = await response.json();
    status.textContent = result.message || (result.success ? 'Code envoye.' : 'Envoi impossible.');
    if (!response.ok || !result.success) return;
    verifyForm.email.value = requestForm.email.value;
    verifyForm.hidden = false;
    verifyForm.otp.focus();
  });

  verifyForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    status.textContent = 'Verification du code...';
    const response = await fetch(verifyForm.action, { method: 'POST', body: new FormData(verifyForm) });
    const result = await response.json();
    status.textContent = result.message || (result.success ? 'Code valide.' : 'Code refuse.');
    if (response.ok && result.success && result.redirect) {
      window.location.href = result.redirect;
    }
  });
})();
</script>
