<?php
// ============================================================
//  PUBLIC: Certificate Verification Page
//  No login required. CAPTCHA + rate-limit protected.
// ============================================================
require_once __DIR__ . '/includes/config.php';
// No requireLogin() — intentionally public

// ── Rate limiting (session-based, 15 attempts / 15 min) ───
if (!isset($_SESSION['verify_rate'])) {
    $_SESSION['verify_rate'] = ['count' => 0, 'start' => time()];
}
if (time() - $_SESSION['verify_rate']['start'] > 900) {
    $_SESSION['verify_rate'] = ['count' => 0, 'start' => time()];
}
$rateLimited = $_SESSION['verify_rate']['count'] >= 15;

// ── Generate / refresh math CAPTCHA ───────────────────────
if (!isset($_SESSION['captcha_q']) || time() > ($_SESSION['captcha_exp'] ?? 0)) {
    $a = rand(2, 19);
    $b = rand(1, 12);
    $_SESSION['captcha_q']   = "$a + $b";
    $_SESSION['captcha_a']   = $a + $b;
    $_SESSION['captcha_exp'] = time() + 600;
}

$certPrefill = htmlspecialchars(clean($_GET['cert'] ?? ''), ENT_QUOTES);
$result      = null;
$error       = null;

// ── Handle POST ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($rateLimited) {
        $error = 'Too many verification attempts. Please wait 15 minutes.';
    } else {
        $_SESSION['verify_rate']['count']++;

        $certNumber    = clean($_POST['cert_number']    ?? '');
        $captchaAnswer = (int) ($_POST['captcha_answer'] ?? -999);

        if (time() > ($_SESSION['captcha_exp'] ?? 0)) {
            $error = 'CAPTCHA expired. Please try again.';
        } elseif ($captchaAnswer !== (int) $_SESSION['captcha_a']) {
            $error = 'Incorrect CAPTCHA answer. Please try again.';
        } elseif (empty($certNumber)) {
            $error = 'Please enter a certificate number.';
        } else {
            // Valid CAPTCHA — look up certificate
            $db = getDB();
            try {
                $stmt = $db->prepare("
                    SELECT c.cert_number, c.party_name, c.site_location,
                           c.calibration_date, c.next_due_date, c.pdf_url,
                           it.label AS instrument_label
                    FROM   certificates c
                    JOIN   instrument_types it ON it.id = c.instrument_type_id
                    WHERE  c.cert_number = ?
                    LIMIT  1
                ");
                $stmt->execute([$certNumber]);
                $cert = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$cert) {
                    $error       = 'No certificate found with that number. Please check and try again.';
                    $certPrefill = htmlspecialchars($certNumber, ENT_QUOTES);
                } else {
                    $result              = $cert;
                    $result['is_expired'] = !empty($cert['next_due_date'])
                        && $cert['next_due_date'] < date('Y-m-d');
                }
            } catch (Exception $e) {
                error_log('verify_certificate: ' . $e->getMessage());
                $error = 'A database error occurred. Please try again later.';
                $certPrefill = htmlspecialchars($certNumber, ENT_QUOTES);
            }
        }

        // Always regenerate CAPTCHA after a POST attempt
        $a = rand(2, 19);
        $b = rand(1, 12);
        $_SESSION['captcha_q']   = "$a + $b";
        $_SESSION['captcha_a']   = $a + $b;
        $_SESSION['captcha_exp'] = time() + 600;
    }
}

$captchaQuestion = htmlspecialchars($_SESSION['captcha_q'] ?? '? + ?', ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#00796b">
  <title>Verify Certificate | Calibration Management System</title>
  <link rel="icon" href="<?= APP_URL ?>/assets/images/logo.png" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
      background: #f0f7f6;
      color: #004d40;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    /* ── Top bar ── */
    .verify-header {
      background: linear-gradient(135deg, #00796b 0%, #004d40 100%);
      padding: 1rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .verify-header img { height: 40px; border-radius: 6px; }
    .verify-header h1 { color: #fff; font-size: 1.2rem; font-weight: 700; }
    .verify-header a {
      margin-left: auto;
      color: rgba(255,255,255,0.8);
      font-size: 0.85rem;
      text-decoration: none;
      display: flex; align-items: center; gap: 0.4rem;
    }
    .verify-header a:hover { color: #fff; }
    /* ── Main ── */
    main {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 3rem 1rem;
    }
    .verify-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 24px rgba(0,121,107,0.10);
      border: 1px solid #b2dfdb;
      padding: 2.5rem 2rem;
      width: 100%;
      max-width: 480px;
    }
    .verify-card .card-icon {
      width: 56px; height: 56px;
      background: #e0f2f1;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1.25rem;
      font-size: 1.6rem;
      color: #00796b;
    }
    .verify-card h2 {
      text-align: center;
      font-size: 1.35rem;
      font-weight: 700;
      color: #004d40;
      margin-bottom: 0.4rem;
    }
    .verify-card p.sub {
      text-align: center;
      font-size: 0.88rem;
      color: #5c7c77;
      margin-bottom: 1.75rem;
    }
    label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      color: #00695c;
      margin-bottom: 0.35rem;
    }
    input[type="text"], input[type="number"] {
      width: 100%;
      padding: 0.65rem 0.9rem;
      border: 1.5px solid #b2dfdb;
      border-radius: 6px;
      font-size: 0.95rem;
      color: #004d40;
      background: #fff;
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
      margin-bottom: 1.1rem;
    }
    input:focus {
      border-color: #00796b;
      box-shadow: 0 0 0 3px rgba(0,121,107,0.12);
    }
    .captcha-row {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.35rem;
    }
    .captcha-row .captcha-question {
      background: #e0f2f1;
      border: 1.5px solid #b2dfdb;
      border-radius: 6px;
      padding: 0.65rem 1rem;
      font-weight: 700;
      font-size: 1rem;
      color: #004d40;
      white-space: nowrap;
    }
    .captcha-row input[type="number"] {
      margin-bottom: 0;
      width: 110px;
      flex-shrink: 0;
    }
    .captcha-hint { font-size: 0.78rem; color: #5c7c77; margin-bottom: 1.1rem; }
    .btn-verify {
      width: 100%;
      padding: 0.75rem;
      background: #00796b;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.2s, transform 0.1s;
    }
    .btn-verify:hover { background: #004d40; }
    .btn-verify:active { transform: scale(0.98); }
    /* ── Alert / Result ── */
    .alert {
      border-radius: 8px;
      padding: 0.9rem 1rem;
      margin-bottom: 1.25rem;
      font-size: 0.9rem;
      display: flex;
      align-items: flex-start;
      gap: 0.6rem;
    }
    .alert-error { background: #fef2f2; border: 1.5px solid #fecaca; color: #b91c1c; }
    .alert-warn  { background: #fef3c7; border: 1.5px solid #fde68a; color: #7c4f00; }
    .rate-limited { background: #fef3c7; border: 1.5px solid #fde68a; color: #7c4f00; border-radius: 8px; padding: 1rem; text-align:center; margin-bottom: 1.25rem; font-size:0.9rem; }
    /* ── Result card ── */
    .result-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 24px rgba(0,121,107,0.12);
      border: 1px solid #b2dfdb;
      padding: 2rem;
      width: 100%;
      max-width: 560px;
      margin-top: 1.5rem;
    }
    .result-card .result-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1.5px solid #e0f2f1;
    }
    .badge-valid {
      display: inline-flex; align-items: center; gap: 0.3rem;
      background: #dcfce7; color: #15803d;
      border: 1px solid #86efac;
      border-radius: 20px; padding: 0.3rem 0.8rem;
      font-size: 0.82rem; font-weight: 700;
    }
    .badge-expired {
      display: inline-flex; align-items: center; gap: 0.3rem;
      background: #fef2f2; color: #b91c1c;
      border: 1px solid #fecaca;
      border-radius: 20px; padding: 0.3rem 0.8rem;
      font-size: 0.82rem; font-weight: 700;
    }
    .cert-number {
      font-family: 'Courier New', monospace;
      font-size: 1.05rem;
      font-weight: 700;
      color: #00796b;
    }
    .detail-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem 1.5rem;
    }
    .detail-item label {
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: #5c7c77;
      margin-bottom: 0.2rem;
      font-weight: 600;
    }
    .detail-item .value {
      font-size: 0.95rem;
      color: #004d40;
      font-weight: 500;
    }
    .pdf-link-row {
      margin-top: 1.25rem;
      padding-top: 1.25rem;
      border-top: 1.5px solid #e0f2f1;
    }
    .btn-pdf {
      display: inline-flex; align-items: center; gap: 0.5rem;
      background: #00796b; color: #fff;
      padding: 0.6rem 1.2rem; border-radius: 6px;
      font-size: 0.9rem; font-weight: 600;
      text-decoration: none;
      transition: background 0.2s;
    }
    .btn-pdf:hover { background: #004d40; }
    .btn-verify-again {
      display: inline-flex; align-items: center; gap: 0.5rem;
      background: transparent; color: #00796b;
      border: 1.5px solid #b2dfdb;
      padding: 0.6rem 1.2rem; border-radius: 6px;
      font-size: 0.9rem; font-weight: 600;
      text-decoration: none; cursor: pointer;
      margin-left: 0.75rem;
      transition: background 0.2s;
    }
    .btn-verify-again:hover { background: #e0f2f1; }
    /* ── Footer ── */
    .verify-footer {
      text-align: center;
      padding: 1.5rem;
      color: #5c7c77;
      font-size: 0.8rem;
    }
    @media (max-width: 540px) {
      .detail-grid { grid-template-columns: 1fr; }
      .verify-card { padding: 1.75rem 1.25rem; }
      .captcha-row { flex-wrap: wrap; }
    }
  </style>
</head>
<body>

<header class="verify-header">
  <img src="<?= APP_URL ?>/assets/images/logo.png" alt="Calibration Management System Logo">
  <h1>Calibration Management System</h1>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <a href="<?= APP_URL ?>/index.php"><i class="fas fa-arrow-left"></i> Back to App</a>
  <?php else: ?>
    <a href="<?= APP_URL ?>/login.php"><i class="fas fa-sign-in-alt"></i> Staff Login</a>
  <?php endif; ?>
</header>

<main>

  <?php if ($result): ?>
    <!-- ── Verification Result ── -->
    <div class="result-card">
      <div class="result-header">
        <div>
          <div style="font-size:0.78rem; color:#5c7c77; margin-bottom:0.25rem; text-transform:uppercase; letter-spacing:0.04em;">Certificate Number</div>
          <div class="cert-number"><?= htmlspecialchars($result['cert_number']) ?></div>
        </div>
        <div style="margin-left:auto;">
          <?php if ($result['is_expired']): ?>
            <span class="badge-expired"><i class="fas fa-times-circle"></i> Expired</span>
          <?php else: ?>
            <span class="badge-valid"><i class="fas fa-check-circle"></i> Valid</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="detail-grid">
        <div class="detail-item">
          <label>Party / Company</label>
          <div class="value"><?= htmlspecialchars($result['party_name'] ?? '—') ?></div>
        </div>
        <?php if (!empty($result['site_location'])): ?>
        <div class="detail-item">
          <label>Site Location</label>
          <div class="value"><?= htmlspecialchars($result['site_location']) ?></div>
        </div>
        <?php endif; ?>
        <div class="detail-item">
          <label>Instrument</label>
          <div class="value"><?= htmlspecialchars($result['instrument_label'] ?? '—') ?></div>
        </div>
        <div class="detail-item">
          <label>Calibration Date</label>
          <div class="value">
            <?= !empty($result['calibration_date'])
                ? date('d M Y', strtotime($result['calibration_date']))
                : '—' ?>
          </div>
        </div>
        <div class="detail-item">
          <label>Next Due Date</label>
          <div class="value <?= $result['is_expired'] ? 'badge-expired' : '' ?>" style="<?= $result['is_expired'] ? 'display:inline-flex;' : '' ?>">
            <?php if ($result['is_expired']): ?>
              <i class="fas fa-exclamation-circle"></i>
            <?php endif; ?>
            <?= !empty($result['next_due_date'])
                ? date('d M Y', strtotime($result['next_due_date']))
                : '—' ?>
          </div>
        </div>
      </div>

      <?php if (!empty($result['pdf_url'])): ?>
      <div class="pdf-link-row">
        <a href="<?= htmlspecialchars($result['pdf_url']) ?>" target="_blank" rel="noopener" class="btn-pdf">
          <i class="fas fa-file-pdf"></i> View PDF Certificate
        </a>
        <a href="<?= APP_URL ?>/verify.php" class="btn-verify-again">
          <i class="fas fa-search"></i> Verify Another
        </a>
      </div>
      <?php else: ?>
      <div class="pdf-link-row">
        <a href="<?= APP_URL ?>/verify.php" class="btn-verify-again">
          <i class="fas fa-search"></i> Verify Another Certificate
        </a>
      </div>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <!-- ── Verification Form ── -->
    <div class="verify-card">
      <div class="card-icon"><i class="fas fa-shield-alt"></i></div>
      <h2>Verify Certificate</h2>
      <p class="sub">Enter the certificate number from the document and solve the CAPTCHA to confirm its authenticity.</p>

      <?php if ($rateLimited): ?>
        <div class="rate-limited">
          <i class="fas fa-clock"></i>
          Too many attempts. Please wait 15 minutes before trying again.
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle" style="margin-top:0.1rem;flex-shrink:0;"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if (!$rateLimited): ?>
      <form method="POST" action="<?= APP_URL ?>/verify.php" autocomplete="off">
        <label for="cert_number">Certificate Number</label>
        <input
          type="text"
          id="cert_number"
          name="cert_number"
          value="<?= $certPrefill ?>"
          placeholder="e.g. CTM-260601"
          required
          autocomplete="off"
          spellcheck="false"
        >

        <label>Security Check (Anti-Bot)</label>
        <div class="captcha-row">
          <span class="captcha-question"><?= $captchaQuestion ?> =</span>
          <input
            type="number"
            id="captcha_answer"
            name="captcha_answer"
            placeholder="?"
            required
            min="0"
            max="99"
            autocomplete="off"
          >
        </div>
        <p class="captcha-hint"><i class="fas fa-info-circle"></i> Solve the simple math problem above to proceed.</p>

        <button type="submit" class="btn-verify">
          <i class="fas fa-search"></i> Verify Certificate
        </button>
      </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</main>

<footer class="verify-footer">
  &copy; <?= date('Y') ?> Calibration Management System &mdash; Certificate Verification Portal
</footer>

</body>
</html>
