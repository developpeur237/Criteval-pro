<?php declare(strict_types=1); ?>
<main class="stub-page">
  <h1>Verification OTP</h1>
  <form method="post" action="<?= BASE_URL ?>/otp/verify">
    <label>Email</label>
    <input type="email" name="email" value="<?= e((string) ($_GET['email'] ?? ($_SESSION['candidate_email'] ?? ''))) ?>" required autocomplete="email">
    <label>Code a 6 chiffres</label>
    <input name="otp" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required>
    <input type="hidden" name="form_id" value="<?= e((string) ($_GET['form_id'] ?? ($_SESSION['candidate_form_id'] ?? 1))) ?>">
    <button type="submit">Verifier</button>
  </form>
</main>
