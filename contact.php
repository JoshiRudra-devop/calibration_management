<?php
require_once __DIR__ . '/includes/config.php';
requireLogin();
$pageTitle  = 'Contact Us';
$activePage = 'contact';
include __DIR__ . '/includes/header.php';
?>

<div class="page-wrapper">
  <div class="container" style="max-width: 800px; margin: 3rem auto; padding: 2rem;">

    <h1 style="text-align: center; color: var(--primary); margin-bottom: 0.5rem;">Contact Us</h1>
    <p style="text-align: center; color: var(--text-mid); margin-bottom: 2rem;">Get in touch with us for any questions or inquiries</p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
      
      <!-- Contact Info -->
      <div>
        <h3 style="color: var(--primary); margin-bottom: 1.5rem;">📍 Our Location</h3>
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-md);">
          <p style="margin-bottom: 1rem;">
            <strong>Calibration Management System</strong><br>
            Shop 9, Karnavati Shopping Center<br>
            Ghodasar, Ahmedabad – 380050<br>
            Gujarat, India
          </p>
          <p style="margin-bottom: 0.5rem;">
            <i class="fas fa-phone" style="color: var(--primary); margin-right: 0.5rem;"></i>
            <strong>Phone:</strong>
          </p>
          <p style="margin-left: 1.5rem; margin-bottom: 1rem;">
            +91 99049-04610<br>
            +91 93282-01463<br>
            +91 93771-96244
          </p>
          <p style="margin-bottom: 0.5rem;">
            <i class="fas fa-envelope" style="color: var(--primary); margin-right: 0.5rem;"></i>
            <strong>Email:</strong>
          </p>
          <p style="margin-left: 1.5rem; margin-bottom: 1rem;">
            shreejiinstrument83@gmail.com<br>
            shreejiinstrument83@yahoo.com
          </p>
          <p style="margin-bottom: 0;">
            <i class="fas fa-globe" style="color: var(--primary); margin-right: 0.5rem;"></i>
            <strong>Website:</strong><br>
            <a href="https://www.shreejiinstruments.com" target="_blank" style="margin-left: 1.5rem;">www.shreejiinstruments.com</a>
          </p>
        </div>

        <h3 style="color: var(--primary); margin-bottom: 1.5rem; margin-top: 2rem;">⏰ Business Hours</h3>
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-md);">
          <p style="margin-bottom: 0.5rem;"><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM</p>
          <p style="margin-bottom: 0.5rem;"><strong>Saturday:</strong> 10:00 AM - 4:00 PM</p>
          <p style="margin-bottom: 0;"><strong>Sunday:</strong> Closed</p>
        </div>
      </div>

      <!-- Contact Form -->
      <div>
        <h3 style="color: var(--primary); margin-bottom: 1.5rem;">💬 Send us a Message</h3>
        <form id="contactForm" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-md);">
          
          <div style="margin-bottom: 1.5rem;">
            <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text);">Your Name *</label>
            <input 
              type="text" 
              id="name" 
              name="name" 
              required
              style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 1rem; font-family: inherit;"
            >
          </div>

          <div style="margin-bottom: 1.5rem;">
            <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text);">Email Address</label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 1rem; font-family: inherit;"
            >
          </div>

          <div style="margin-bottom: 1.5rem;">
            <label for="subject" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text);">Subject</label>
            <input 
              type="text" 
              id="subject" 
              name="subject" 
              style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 1rem; font-family: inherit;"
            >
          </div>

          <div style="margin-bottom: 1.5rem;">
            <label for="message" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text);">Message *</label>
            <textarea 
              id="message" 
              name="message" 
              required
              rows="5"
              style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 6px; font-size: 1rem; font-family: inherit; resize: vertical;"
            ></textarea>
          </div>

          <button 
            type="submit" 
            style="width: 100%; padding: 0.75rem; background: var(--primary); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem; transition: background 0.2s;"
          >
            Send Message
          </button>
        </form>

        <div id="contactMessage" style="margin-top: 1rem; padding: 1rem; border-radius: 6px; display: none;"></div>
      </div>

    </div>

  </div>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const subject = document.getElementById('subject').value.trim();
  const message = document.getElementById('message').value.trim();
  
  if (!name || !message) {
    showContactMessage('Please fill in all required fields', 'error');
    return;
  }

  const submitBtn = this.querySelector('button[type="submit"]');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Sending...';

  try {
    const response = await fetch(SHREEJI_CONFIG.apiBase + '/contact.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email, subject, message })
    });
    
    const data = await response.json();
    
    if (data.success) {
      showContactMessage('Thank you! Your message has been sent successfully. We will get back to you soon.', 'success');
      document.getElementById('contactForm').reset();
    } else {
      showContactMessage('Error: ' + (data.message || 'Failed to send message'), 'error');
    }
  } catch (error) {
    showContactMessage('Network error: ' + error.message, 'error');
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Send Message';
  }
});

function showContactMessage(text, type) {
  const msgDiv = document.getElementById('contactMessage');
  msgDiv.textContent = text;
  msgDiv.style.display = 'block';
  
  if (type === 'success') {
    msgDiv.style.background = '#d4edda';
    msgDiv.style.color = '#155724';
    msgDiv.style.border = '1px solid #c3e6cb';
  } else {
    msgDiv.style.background = '#f8d7da';
    msgDiv.style.color = '#721c24';
    msgDiv.style.border = '1px solid #f5c6cb';
  }
  
  setTimeout(() => {
    msgDiv.style.display = 'none';
  }, 5000);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
