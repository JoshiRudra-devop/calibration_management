<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Theme Preview Replica | Shreeji Instruments</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
  <style>
    /* ── Light Mint & Deep Teal SaaS Theme ──
       Base Background (80%): #f0f7f6 (Soft Mint/Teal Cream)
       Header & Cards (Surfaces): #ffffff (Pure White)
       Hero Card Background: #e0f2f1 (Soft Light Teal/Mint)
       Primary Accent (10%): #00796b (Deep Teal) / #00897b (Vibrant Teal)
       Borders: #b2dfdb / #e0f2f1 (Soft teal grey)
       Text Primary: #004d40 (Darkest Forest Teal)
       Text Secondary: #00695c (Medium Teal)
       Alerts: #ef4444 (Crimson Red) / #22b55d (Lime/Success Green)
    */

    *, *::before, *::after { 
      box-sizing: border-box; 
      margin: 0; 
      padding: 0; 
      border-radius: 6px !important; /* Subtle rounded corners */
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f0f7f6; /* Soft Mint/Teal Cream BG */
      color: #004d40;            /* Darkest Teal text */
      line-height: 1.6;
      font-size: 12.5px;
      padding-bottom: 80px;
    }

    /* Theme Preview Indicator Banner */
    .preview-banner {
      background: #00796b; /* Deep Teal Banner */
      color: #ffffff;
      text-align: center;
      padding: 0.6rem;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      border-radius: 0px !important;
    }
    .preview-banner span {
      color: #e0f2f1;
      font-weight: 800;
    }

    /* ── Top Brand Header (Clean White BG, Teal Bottom Border) ── */
    .top-brand-header {
      position: sticky;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      height: 62px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #ffffff; /* Clean White BG */
      padding: 0 2rem;
      color: #004d40;
      border-bottom: 1.5px solid #b2dfdb; /* Teal bottom border */
      box-shadow: 0 2px 8px rgba(0, 121, 107, 0.02);
      border-radius: 0px !important;
    }
    .top-brand-header__left {
      display: flex;
      align-items: center;
      gap: 0.8rem;
      text-decoration: none;
      color: #004d40;
    }
    .top-brand-header__logo {
      width: 36px;
      height: 36px;
      border-radius: 50% !important;
      border: 2px solid #00796b; /* Deep Teal logo border */
    }
    .top-brand-header__title {
      font-size: 1.25rem;
      font-weight: 800;
      letter-spacing: 0.5px;
      color: #00796b;
    }
    .top-brand-header__right {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      font-size: 0.9rem;
    }
    .top-brand-header__profile {
      font-weight: 500;
      color: #00695c;
    }
    .top-brand-header__link {
      color: #00695c !important;
      text-decoration: none;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      transition: color 0.15s;
    }
    .top-brand-header__link:hover {
      color: #004d40 !important; /* dark teal hover */
    }
    .top-brand-header__logout {
      color: #ef4444 !important; /* red logout */
      font-weight: 600;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.4rem;
      transition: color 0.15s;
    }
    .top-brand-header__logout:hover {
      color: #b91c1c !important;
    }

    /* ── Main Layout ── */
    .page-wrapper {
      padding: 2.5rem 1rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    /* Hero Header Card (Soft Mint BG, Teal Heading) */
    .hero-card {
      background: #e0f2f1; /* Soft Light Teal/Mint BG */
      color: #004d40;
      padding: 2.5rem 2rem;
      margin-bottom: 2rem;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0, 121, 107, 0.03);
      border: 1.5px solid #b2dfdb;
    }
    .hero-card h2 {
      font-size: 1.8rem;
      font-weight: 800;
      margin-bottom: 0.5rem;
      letter-spacing: -0.5px;
      color: #00796b; /* Heading in Deep Teal */
    }
    .hero-card p {
      color: #00695c; /* Medium Teal description text */
      font-size: 0.95rem;
    }

    /* Controls row */
    .controls-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 1.5rem;
      margin-bottom: 1.5rem;
      flex-wrap: wrap;
    }
    .search-box {
      position: relative;
      flex: 1;
      max-width: 450px;
    }
    .search-box input {
      width: 100%;
      padding: 0.75rem 1rem 0.75rem 2.8rem;
      border: 1.5px solid #b2dfdb; /* soft teal border */
      background: #ffffff; /* pure white surface */
      color: #004d40;
      font-size: 0.9rem;
      outline: none;
      transition: all 0.15s;
    }
    .search-box input:focus {
      border-color: #00796b;
      box-shadow: 0 0 0 3px rgba(0, 121, 107, 0.12);
    }
    .search-icon {
      position: absolute;
      left: 1.1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #00796b;
    }

    /* Buttons */
    .btn-primary {
      background: linear-gradient(135deg, #00796b, #00695c); /* Deep Teal gradient */
      color: #ffffff;
      padding: 0.75rem 1.6rem;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      border: none;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0, 121, 107, 0.2);
      transition: all 0.2s;
    }
    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(0, 121, 107, 0.3);
      filter: brightness(1.05);
    }
    .btn-primary:active {
      transform: translateY(0);
    }

    .btn-secondary {
      background: #ffffff;
      color: #00695c;
      padding: 0.45rem 0.9rem;
      font-weight: 600;
      text-decoration: none;
      border: 1px solid #b2dfdb;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      transition: all 0.15s;
    }
    .btn-secondary:hover {
      background: #f0f7f6;
      border-color: #00796b;
      color: #004d40;
    }

    /* ── Table Card ── */
    .table-card {
      background: #ffffff; /* pure white surface */
      border: 1.5px solid #b2dfdb;
      box-shadow: 0 4px 20px -2px rgba(0, 121, 107, 0.03);
      padding: 1.5rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
    }
    th {
      padding: 1.1rem 0.75rem;
      background: #f5fbfb; /* light teal head BG */
      border-bottom: 2px solid #b2dfdb;
      color: #00695c;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 0.78rem;
      letter-spacing: 0.5px;
    }
    td {
      padding: 1.1rem 0.75rem;
      border-bottom: 1px solid #eef7f6;
      vertical-align: middle;
    }
    tr:hover {
      background: #f8fdfd;
    }

    /* Badges */
    .badge-instrument {
      background: #e0f2f1; /* light teal badge */
      color: #004d40;      /* dark teal text */
      padding: 0.25rem 0.6rem;
      font-size: 0.8rem;
      font-weight: 600;
      border: 1px solid #b2dfdb;
    }
    .badge-overdue {
      background: #fee2e2;
      color: #ef4444;
      padding: 0.2rem 0.5rem;
      font-size: 0.75rem;
      font-weight: 700;
      border: 1px solid #fecaca;
      margin-left: 0.5rem;
    }

    /* Actions */
    .actions-cell {
      display: flex;
      justify-content: flex-end;
      gap: 0.5rem;
    }

    /* ── Fixed Bottom Navigation Bar ── */
    .bottom-nav-bar {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      height: 65px;
      background: #ffffff;
      border-top: 1.5px solid #b2dfdb;
      box-shadow: 0 -5px 20px rgba(0, 121, 107, 0.03);
      z-index: 9999;
      display: flex;
      justify-content: space-around;
      align-items: center;
      border-radius: 0px !important;
    }
    .bottom-nav-bar__btn {
      flex: 1;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: 4px;
      color: #00695c !important;
      font-size: 0.8rem;
      font-weight: 600;
      text-align: center;
      text-decoration: none;
      transition: all 0.15s;
    }
    .bottom-nav-bar__btn i {
      font-size: 1.25rem;
    }
    .bottom-nav-bar__btn:hover {
      color: #00796b !important;
      background-color: #f0f7f6; /* Soft teal hover */
    }
    .bottom-nav-bar__btn.active {
      color: #00796b !important;
      background-color: #e0f2f1; /* Deeper teal tint for active button */
    }

  </style>
</head>
<body>

  <!-- Theme Preview Top Bar -->
  <div class="preview-banner">
    SHREEJI INSTRUMENTS – COLOR SCHEME PREVIEW: 
    <span>80% Light Mint Background</span> & <span>Deep Teal Accents</span>
  </div>

  <!-- Top Brand Header -->
  <header class="top-brand-header">
    <a href="#" class="top-brand-header__left">
      <img src="assets/images/logo.png" alt="Shreeji Instruments Logo" class="top-brand-header__logo">
      <span class="top-brand-header__title">SHREEJI INSTRUMENTS</span>
    </a>
    <div class="top-brand-header__right">
      <a href="#" class="top-brand-header__link">
        <i class="fas fa-chart-line"></i> Dashboard
      </a>
      <span class="top-brand-header__profile" style="margin-right: 0.5rem; margin-left: 0.5rem;">
        <i class="fas fa-user-circle"></i> Welcome, Admin
      </span>
      <a href="#" class="top-brand-header__logout">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </header>

  <!-- Page Wrapper Content -->
  <div class="page-wrapper">
    
    <!-- Hero Card (Soft Mint card with teal headings) -->
    <div class="hero-card">
      <h2>Recent Certificates Log</h2>
      <p>View, edit, and access all recently generated calibration certificates in chronological order.</p>
    </div>

    <!-- Controls Row -->
    <div class="controls-row">
      <!-- Search Input (White BG, Teal Border, Darkest Teal text) -->
      <div class="search-box">
        <span class="search-icon"><i class="fas fa-search"></i></span>
        <input type="text" placeholder="Search certificates by number, company, or instrument..." readonly>
      </div>

      <!-- Action Button (Deep Teal gradient) -->
      <a href="#" class="btn-primary" onclick="alert('This is a theme preview replica. Navigation buttons are disabled.'); return false;">
        <i class="fas fa-plus-circle"></i> Create New Lot Report
      </a>
    </div>

    <!-- Log Table Card (White BG, Teal Border) -->
    <div class="table-card">
      <table>
        <thead>
          <tr>
            <th>Certificate No</th>
            <th>Company Name</th>
            <th>Site Location</th>
            <th>Instrument Type</th>
            <th>Calibration Date</th>
            <th>Next Due Date</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Row 1: Normal Row -->
          <tr>
            <td style="font-weight: 700; color: #00796b;">AL-260601</td>
            <td style="font-weight: 600;">L&T Construction Ltd</td>
            <td style="color: #00695c;">Ahmedabad Metro Project</td>
            <td><span class="badge-instrument">Auto Level</span></td>
            <td>13/06/2026</td>
            <td style="font-weight: 600;">12/06/2027</td>
            <td class="actions-cell">
              <a href="#" class="btn-secondary" onclick="alert('PDF view disabled in preview mode.'); return false;"><i class="fas fa-file-pdf"></i> PDF</a>
              <a href="#" class="btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="alert('Edit prefill disabled in preview mode.'); return false;"><i class="fas fa-edit"></i> Edit</a>
            </td>
          </tr>
          
          <!-- Row 2: Overdue Row (1% Red Highlight) -->
          <tr>
            <td style="font-weight: 700; color: #00796b;">CTM-250512</td>
            <td style="font-weight: 600;">Reliance Industries Group</td>
            <td style="color: #00695c;">Jamnagar Refinery Terminal</td>
            <td><span class="badge-instrument">Cube Testing Machine</span></td>
            <td>12/05/2025</td>
            <td style="font-weight: 600; color: #ef4444;">
              11/05/2026
              <span class="badge-overdue">OVERDUE</span>
            </td>
            <td class="actions-cell">
              <a href="#" class="btn-secondary" onclick="alert('PDF view disabled in preview mode.'); return false;"><i class="fas fa-file-pdf"></i> PDF</a>
              <a href="#" class="btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="alert('Edit prefill disabled in preview mode.'); return false;"><i class="fas fa-edit"></i> Edit</a>
            </td>
          </tr>

          <!-- Row 3: Normal Row -->
          <tr>
            <td style="font-weight: 700; color: #00796b;">VC-260603</td>
            <td style="font-weight: 600;">Tata Motors Ltd</td>
            <td style="color: #00695c;">Sanand Plant G-1</td>
            <td><span class="badge-instrument">Vernier Caliper</span></td>
            <td>11/06/2026</td>
            <td style="font-weight: 600;">10/06/2027</td>
            <td class="actions-cell">
              <a href="#" class="btn-secondary" onclick="alert('PDF view disabled in preview mode.'); return false;"><i class="fas fa-file-pdf"></i> PDF</a>
              <a href="#" class="btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="alert('Edit prefill disabled in preview mode.'); return false;"><i class="fas fa-edit"></i> Edit</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>

  <!-- Bottom Navigation Bar -->
  <div class="bottom-nav-bar">
    <a href="#" class="bottom-nav-bar__btn active">
      <i class="fas fa-home"></i>
      <span>Home</span>
    </a>
    <a href="#" class="bottom-nav-bar__btn">
      <i class="fas fa-building"></i>
      <span>All Companies</span>
    </a>
    <a href="#" class="bottom-nav-bar__btn">
      <i class="fas fa-plus-circle"></i>
      <span>New Report</span>
    </a>
    <a href="#" class="bottom-nav-bar__btn">
      <i class="fas fa-file-alt"></i>
      <span>Instrument Wise</span>
    </a>
    <a href="#" class="bottom-nav-bar__btn">
      <i class="fas fa-clock"></i>
      <span>Due Near</span>
    </a>
    <a href="#" class="bottom-nav-bar__btn">
      <i class="fas fa-tools"></i>
      <span>Add Instrument</span>
    </a>
    <a href="#" class="bottom-nav-bar__btn">
      <i class="fas fa-cog"></i>
      <span>Settings</span>
    </a>
  </div>

</body>
</html>
