<?php
require_once __DIR__ . '/includes/config.php';

// If already logged in, redirect to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$pageTitle  = 'Login';
$activePage = 'login';
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrapper">
  <div class="container" style="max-width: 400px; margin: 6rem auto;">
    
    <div class="card" style="padding: 2rem; border-radius: var(--radius-lg); box-shadow: 0 4px 20px -2px rgba(0, 121, 107, 0.03); border: 1.5px solid var(--border); background: white;">
      <h2 style="text-align: center; margin-bottom: 1.5rem; color: var(--primary); font-size: 1.3rem; font-weight: 700;">Login</h2>
      
      <form id="loginForm">
        <div class="form-group" style="margin-bottom: 1rem;">
          <label for="phone" style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 12px;">Phone Number</label>
          <input 
            type="tel" 
            id="phone" 
            name="phone" 
            placeholder="e.g., 9999999999"
            pattern="[0-9]{10}"
            required
            style="width: 100%; padding: 0.65rem 0.8rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 12px; color: var(--text); background: white;"
          >
        </div>
 
        <div class="form-group" style="margin-bottom: 1.5rem;">
          <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 12px;">Password</label>
          <input 
            type="password" 
            id="password" 
            name="password" 
            placeholder="Enter password"
            required
            style="width: 100%; padding: 0.65rem 0.8rem; border: 1.5px solid var(--border); border-radius: 6px; font-size: 12px; color: var(--text); background: white;"
          >
        </div>
 
        <button 
          type="submit" 
          style="width: 100%; padding: 0.65rem 0.8rem; background: var(--primary-dk); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 12px; transition: background 0.2s;"
        >
          Login
        </button>
      </form>

    </div>

  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const phone = document.getElementById('phone').value.trim();
  const password = document.getElementById('password').value;
  
  Loader.show('Logging in...');
  
  try {
    const response = await fetch(SHREEJI_CONFIG.apiBase + '/auth.php?action=login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': SHREEJI_CONFIG.csrfToken },
      body: JSON.stringify({ phone, password, csrf_token: SHREEJI_CONFIG.csrfToken })
    });
    
    const data = await response.json();
    
    if (data.success) {
      Loader.success('Login successful! ✨');
      setTimeout(() => {
        window.location.href = SHREEJI_CONFIG.appUrl + '/dashboard.php';
      }, 1000);
    } else {
      Loader.error(data.message || 'Login failed');
    }
  } catch (error) {
    Loader.error('Network error: ' + error.message);
  }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
