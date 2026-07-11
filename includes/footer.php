<?php 
// includes/footer.php 
$isEmbed = isset($_GET['embed']) && $_GET['embed'] === 'true';
$showFooterContent = (basename($_SERVER['PHP_SELF']) === 'about.php' || basename($_SERVER['PHP_SELF']) === 'contact.php');
?>

<?php if ($showFooterContent && !$isEmbed): ?>
<footer>
  <div class="social-links">
    <a href="https://www.facebook.com/profile.php?id=100064984329642" target="_blank" aria-label="Facebook">
      <i class="fab fa-facebook-f"></i>
    </a>
    <a href="https://maps.app.goo.gl/oNEbQerty7R3vzC88" target="_blank" aria-label="Location">
      <i class="fas fa-map-marker-alt"></i>
    </a>
    <a href="https://wa.me/919904904610" target="_blank" aria-label="WhatsApp">
      <i class="fab fa-whatsapp"></i>
    </a>
    <a href="mailto:shreejiinstrument83@gmail.com" aria-label="Email">
      <i class="fas fa-envelope"></i>
    </a>
  </div>
  <img src="<?= APP_URL ?>/assets/images/logo.png" alt="Logo"
       style="width:48px;border-radius:50%;margin:0 auto .75rem;border:2px solid rgba(255,255,255,.2);">
  <p><strong>Calibration Management System</strong></p>
  <p><i class="fas fa-map-marker-alt"></i> Shop 9, Karnavati Shopping Center, Ghodasar, Ahmedabad – 380050</p>
  <p><i class="fas fa-phone"></i> +91 99049-04610 &nbsp;|&nbsp; +91 93282-01463 &nbsp;|&nbsp; +91 93771-96244</p>
  <p><i class="fas fa-envelope"></i> shreejiinstrument83@yahoo.com</p>
  <p><a href="https://www.shreejiinstruments.com" target="_blank" style="color:#b2dfdb;">
    <i class="fas fa-globe"></i> www.shreejiinstruments.com
  </a></p>
  <div class="copyright">&copy; <?= date('Y') ?> Calibration Management System. All rights reserved.</div>
</footer>
<?php endif; ?>

<?php if (!$isEmbed): ?>
<!-- Bottom Navigation Bar (Laptops/Desktops) -->
<div class="bottom-nav-bar">
  <a href="<?= APP_URL ?>/index.php" class="bottom-nav-bar__btn <?= $activePage==='home'?'active':'' ?>">
    <i class="fas fa-home"></i>
    <span>Home</span>
  </a>
  <a href="<?= APP_URL ?>/companies.php" class="bottom-nav-bar__btn <?= $activePage==='companies'?'active':'' ?>">
    <i class="fas fa-building"></i>
    <span>All Companies</span>
  </a>
  <a href="<?= APP_URL ?>/create_certificate.php" class="bottom-nav-bar__btn <?= $activePage==='create_certificate'?'active':'' ?>">
    <i class="fas fa-plus-circle"></i>
    <span>New Report</span>
  </a>
  <a href="<?= APP_URL ?>/instrument_reports.php" class="bottom-nav-bar__btn <?= $activePage==='instrument_reports'?'active':'' ?>">
    <i class="fas fa-file-alt"></i>
    <span>Instrument Wise</span>
  </a>
  <a href="<?= APP_URL ?>/due_near.php" class="bottom-nav-bar__btn <?= $activePage==='due_near'?'active':'' ?>">
    <i class="fas fa-clock"></i>
    <span>Due Near</span>
  </a>
  <a href="<?= APP_URL ?>/add_instrument.php" class="bottom-nav-bar__btn <?= $activePage==='add_instrument'?'active':'' ?>">
    <i class="fas fa-tools"></i>
    <span>Add Instrument</span>
  </a>
  <a href="<?= APP_URL ?>/settings.php" class="bottom-nav-bar__btn <?= $activePage==='settings'?'active':'' ?>">
    <i class="fas fa-cog"></i>
    <span>Settings</span>
  </a>
</div>
<?php endif; ?>

<script>
  if (typeof SHREEJI_CONFIG !== 'undefined') {
    SHREEJI_CONFIG.instrumentLabel = '<?= isset($instrument['label']) ? htmlspecialchars($instrument['label']) : '' ?>';
  }
</script>
<script src="<?= APP_URL ?>/assets/js/offline_storage.js?v=1.6"></script>
<script src="<?= APP_URL ?>/assets/js/app.js?v=1.1"></script>
</body>
</html>