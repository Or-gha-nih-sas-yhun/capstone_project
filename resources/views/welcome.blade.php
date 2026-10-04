<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome — Barangay Pili Digital Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Official online services portal for Barangay Pili. Request certificates, track status, and view announcements.">
  <link rel="icon" type="image/png" href="{{ asset('assets/images/pili_logo.png?v=2') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico?v=2') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script>
    if (navigator.userAgent.includes('BrgyPiliApp')) {
      window.location.href = "{{ route('register') }}";
    }
  </script>
  <style>
    :root {
      --primary-color: #b91c1c;
      --primary-dark: #450a0a;
      --primary-gradient: linear-gradient(135deg, #b91c1c, #450a0a);
      --secondary-gradient: linear-gradient(135deg, #10b981, #059669);
      --accent-color: #b91c1c;
      --accent-light: #fef2f2;
      --text-main: #0f172a;
      --text-muted: #64748b;
      --bg-light: #f8fafc;
      --card-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
      --card-shadow-hover: 0 20px 40px rgba(185, 28, 28, 0.08);
      --font-family: 'Plus Jakarta Sans', sans-serif;
    }
    body {
      font-family: var(--font-family);
      margin: 0;
      padding: 0;
      color: var(--text-main);
      background-color: #ffffff;
      overflow-x: hidden;
      scroll-behavior: smooth;
    }
    /* Navigation Bar */
    .navbar {
      background: transparent;
      border-bottom: 1px solid transparent;
      padding: 24px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: fixed;
      top: 0;
      width: 100%;
      box-sizing: border-box;
      z-index: 1000;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .navbar.scrolled {
      background: #7a0c0c;
      border-bottom: 1px solid #5c0707;
      padding: 12px 40px;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
    }
    .logo-container {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .nav-links {
      display: flex;
      gap: 32px;
      list-style: none;
      margin: 0;
      padding: 0;
    }
        .nav-links a {
      text-decoration: none;
      color: #ffffff;
      font-weight: 600;
      font-size: 14px;
      transition: color 0.2s ease;
    }
    .nav-links a:hover {
      color: #fca5a5;
    }
    .nav-actions {
      display: flex;
      gap: 12px;
      align-items: center;
    }
    .btn-nav-primary {
      background: #ffffff;
      color: var(--primary-color) !important;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 13px;
      text-decoration: none;
      transition: transform 0.2s ease, background 0.2s ease;
    }
    .btn-nav-primary:hover {
      background: #f8fafc;
      transform: translateY(-1px);
    }
    .btn-nav-secondary {
      border: 1px solid rgba(255, 255, 255, 0.4);
      color: #ffffff;
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 13px;
      text-decoration: none;
      transition: background 0.2s ease;
    }
    .btn-nav-secondary:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    /* Hero Section - SaaS Dual Column Layout */
        .hero {
      background: #fcf6f6;
      padding: 160px 40px 100px 40px;
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 220px;
      background: linear-gradient(to bottom, #7a0c0c 0%, #7a0c0c 80px, transparent 100%);
      pointer-events: none;
      z-index: 0;
    }
    .bg-shape {
      position: absolute;
      z-index: 0;
      opacity: 0.1;
      pointer-events: none;
    }
    .bg-circle-1 {
      width: 350px;
      height: 350px;
      background: #b91c1c;
      border-radius: 50%;
      top: -50px;
      left: -100px;
      animation: floatCircle 22s infinite ease-in-out alternate;
    }
    .bg-circle-2 {
      width: 450px;
      height: 450px;
      background: #450a0a;
      border-radius: 50%;
      bottom: -150px;
      right: -100px;
      animation: floatCircle2 28s infinite ease-in-out alternate;
      opacity: 0.05;
    }
    .bg-triangle-1 {
      width: 0;
      height: 0;
      border-left: 60px solid transparent;
      border-right: 60px solid transparent;
      border-bottom: 104px solid #b91c1c;
      top: 30%;
      right: 20%;
      animation: driftRotate 20s infinite linear;
      opacity: 0.08;
    }
    .bg-triangle-2 {
      width: 0;
      height: 0;
      border-left: 40px solid transparent;
      border-right: 40px solid transparent;
      border-bottom: 69px solid #b91c1c;
      bottom: 25%;
      left: 15%;
      animation: driftRotate2 25s infinite linear reverse;
      opacity: 0.08;
    }
    .bg-outline-1 {
      width: 250px;
      height: 250px;
      border: 2px solid #b91c1c;
      border-radius: 50%;
      top: 50%;
      left: 45%;
      animation: floatRotate 35s infinite linear;
      opacity: 0.15;
    }
    
    @keyframes floatCircle {
      0% { transform: translate(0, 0) scale(1); }
      100% { transform: translate(60px, 90px) scale(1.15); }
    }
    @keyframes floatCircle2 {
      0% { transform: translate(0, 0) scale(1); }
      100% { transform: translate(-90px, -60px) scale(1.1); }
    }
    @keyframes driftRotate {
      0% { transform: translate(0, 0) rotate(0deg); }
      25% { transform: translate(30px, 40px) rotate(90deg); }
      50% { transform: translate(50px, 0px) rotate(180deg); }
      75% { transform: translate(30px, -40px) rotate(270deg); }
      100% { transform: translate(0, 0) rotate(360deg); }
    }
    @keyframes driftRotate2 {
      0% { transform: translate(0, 0) rotate(0deg); }
      33% { transform: translate(-40px, 50px) rotate(120deg); }
      66% { transform: translate(40px, 30px) rotate(240deg); }
      100% { transform: translate(0, 0) rotate(360deg); }
    }
    @keyframes floatRotate {
      0% { transform: translate(-50%, -50%) rotate(0deg); }
      100% { transform: translate(-50%, -50%) rotate(360deg); }
    }

    .hero-grid-container {
      max-width: 800px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      position: relative;
      z-index: 1;
    }
    .hero-content {
      text-align: center;
    }
    .hero-badge {
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      color: var(--primary-color);
      padding: 6px 14px;
      border-radius: 100px;
      font-weight: 700;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 24px;
    }
    .pulse-dot {
      width: 8px;
      height: 8px;
      background-color: #b91c1c;
      border-radius: 50%;
      display: inline-block;
      box-shadow: 0 0 0 0 rgba(185, 28, 28, 0.7);
      animation: pulse 1.6s infinite;
    }
    @keyframes pulse {
      0% {
        transform: scale(0.9);
        box-shadow: 0 0 0 0 rgba(185, 28, 28, 0.7);
      }
      70% {
        transform: scale(1);
        box-shadow: 0 0 0 6px rgba(185, 28, 28, 0);
      }
      100% {
        transform: scale(0.9);
        box-shadow: 0 0 0 0 rgba(185, 28, 28, 0);
      }
    }
    .hero h1 {
      font-size: 46px;
      font-weight: 800;
      color: var(--primary-dark);
      margin: 0 0 20px 0;
      line-height: 1.15;
      letter-spacing: -1.5px;
    }
    .hero p {
      font-size: 17px;
      color: var(--text-muted);
      margin: 0 auto 36px auto;
      max-width: 600px;
      font-weight: 400;
      line-height: 1.65;
    }
    .hero-ctas {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      justify-content: center;
    }
    .btn-hero-primary {
      background: var(--primary-color);
      color: white !important;
      padding: 16px 32px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 15px;
      text-decoration: none;
      box-shadow: 0 4px 20px rgba(30, 64, 175, 0.15);
      transition: transform 0.2s, background 0.2s;
    }
    .btn-hero-primary:hover {
      background: var(--primary-dark);
      color: white !important;
      transform: translateY(-2px);
    }
    .btn-hero-secondary {
      background: #ffffff;
      border: 1px solid #cbd5e1;
      color: #334155;
      padding: 16px 30px;
      border-radius: 10px;
      font-weight: 700;
      font-size: 15px;
      text-decoration: none;
      transition: background 0.2s, transform 0.2s;
    }
    .btn-hero-secondary:hover {
      background: var(--bg-light);
      transform: translateY(-2px);
    }

    /* Hero Illustration Mockup */
    .hero-illustration {
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .hero-mockup {
      background: #0f172a;
      border-radius: 16px;
      border: 1px solid #334155;
      box-shadow: 0 20px 50px rgba(15, 23, 42, 0.15);
      width: 100%;
      max-width: 440px;
      overflow: hidden;
    }
    .mockup-bar {
      background: #1e293b;
      padding: 12px 16px;
      display: flex;
      align-items: center;
      gap: 8px;
      border-bottom: 1px solid #334155;
    }
    .mockup-bar .dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      display: inline-block;
    }
    .mockup-bar .dot.red { background: #ef4444; }
    .mockup-bar .dot.yellow { background: #eab308; }
    .mockup-bar .dot.green { background: #22c55e; }
    .mockup-title {
      color: #94a3b8;
      font-size: 11px;
      font-family: monospace;
      margin-left: 8px;
    }
    .mockup-view {
      background: #f8fafc;
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .stats-mini-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }
    .stat-mini-card {
      background: white;
      border-radius: 10px;
      padding: 14px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }
    .stat-mini-card i {
      font-size: 16px;
      margin-bottom: 8px;
      display: block;
    }
    .stat-mini-card i.text-blue { color: #b91c1c; }
    .stat-mini-card i.text-yellow { color: #d97706; }
    .stat-num {
      font-size: 18px;
      font-weight: 800;
      color: var(--text-main);
    }
    .stat-label {
      font-size: 11px;
      color: var(--text-muted);
      margin-top: 2px;
    }
    .mockup-chart-placeholder {
      background: white;
      border-radius: 10px;
      padding: 16px;
      border: 1px solid #e2e8f0;
    }
    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 11px;
      font-weight: 700;
      color: var(--text-main);
      margin-bottom: 14px;
    }
    .badge-live {
      background: #ecfdf5;
      color: #065f46;
      padding: 2px 6px;
      border-radius: 4px;
      font-size: 9px;
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .chart-bars {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      height: 120px;
      padding: 0 10px;
    }
    .chart-bar {
      width: 40px;
      background: #cbd5e1;
      border-radius: 4px 4px 0 0;
      position: relative;
      transition: height 0.3s ease;
    }
    .chart-bar::after {
      content: attr(data-label);
      position: absolute;
      bottom: -18px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 9px;
      color: var(--text-muted);
    }

    /* Wave Divider */
    .hero-wave {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      overflow: hidden;
      line-height: 0;
    }
    .hero-wave svg {
      position: relative;
      display: block;
      width: calc(100% + 1.3px);
      height: 40px;
    }
    .hero-wave .shape-fill {
      fill: #ffffff;
    }

    /* Services Grid */
    .section {
      padding: 100px 40px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .section-header {
      text-align: center;
      margin-bottom: 60px;
    }
    .section-header h2 {
      font-size: 34px;
      font-weight: 800;
      letter-spacing: -1px;
      color: var(--primary-dark);
      margin: 0 0 12px 0;
    }
    .section-header p {
      color: var(--text-muted);
      max-width: 550px;
      margin: 0 auto;
      font-size: 16px;
      line-height: 1.5;
    }
    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
    }
    .service-card {
      background: white;
      border-radius: 16px;
      padding: 32px;
      box-shadow: var(--card-shadow);
      border: 1px solid #e2e8f0;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
    }
    .service-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--card-shadow-hover);
      border-color: #dbeafe;
    }
    .service-icon {
      width: 54px;
      height: 54px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-bottom: 24px;
      background: #fef2f2;
      color: #b91c1c;
    }
    
    .service-card h3 {
      font-size: 19px;
      font-weight: 800;
      margin: 0 0 12px 0;
      color: var(--text-main);
      letter-spacing: -0.5px;
    }
    .service-card p {
      color: var(--text-muted);
      font-size: 14px;
      line-height: 1.6;
      margin: 0 0 24px 0;
      flex: 1;
    }
    .service-link {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 700;
      font-size: 13.5px;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: gap 0.2s ease;
    }
    .service-link:hover {
      gap: 10px;
      text-decoration: none;
    }

    /* Track Document section */
    .track-section {
      background: var(--bg-light);
      border-radius: 24px;
      border: 1px solid #e2e8f0;
      padding: 60px;
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 60px;
      align-items: center;
    }
    .track-info h2 {
      font-size: 32px;
      font-weight: 800;
      letter-spacing: -1px;
      margin: 0 0 16px 0;
      color: var(--primary-dark);
    }
    .track-info p {
      color: var(--text-muted);
      font-size: 16px;
      line-height: 1.6;
      margin: 0 0 24px 0;
    }
    .feature-list {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .feature-list li {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
      color: #475569;
      font-size: 14.5px;
    }
    .feature-list i {
      color: #b91c1c;
    }
    .track-box-widget {
      background: white;
      border-radius: 16px;
      padding: 32px;
      border: 1px solid #e2e8f0;
      box-shadow: var(--card-shadow);
    }
    .track-form label {
      font-weight: 700;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #475569;
      display: block;
      margin-bottom: 8px;
    }
    .track-form input {
      width: 100%;
      background: white;
      border: 2px solid #cbd5e1;
      padding: 14px 16px;
      border-radius: 8px;
      font-family: monospace;
      font-size: 16px;
      box-sizing: border-box;
      margin-bottom: 16px;
      transition: border-color 0.2s;
    }
    .track-form input:focus {
      border-color: var(--primary-color);
      outline: none;
    }
    .btn-track-submit {
      width: 100%;
      background: var(--primary-color);
      color: white;
      border: none;
      padding: 14px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 15px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: background 0.2s;
    }
    .btn-track-submit:hover {
      background: var(--primary-dark);
    }

    /* Bulletins Section */
    .bulletins-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
    }
    .bulletin-item {
      background: white;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      box-shadow: var(--card-shadow);
      padding: 28px;
      transition: border-color 0.3s ease;
      display: flex;
      flex-direction: column;
      height: 100%;
      box-sizing: border-box;
    }
    .bulletin-item:hover {
      border-color: #cbd5e1;
    }
    .bulletin-badge {
      background: #eff6ff;
      color: var(--primary-color);
      font-weight: 700;
      font-size: 11px;
      padding: 4px 12px;
      border-radius: 100px;
      text-transform: uppercase;
      display: inline-block;
      margin-bottom: 16px;
    }
    .bulletin-item h4 {
      font-size: 19px;
      font-weight: 800;
      margin: 0 0 12px 0;
      color: var(--text-main);
      letter-spacing: -0.5px;
    }
    .bulletin-item p {
      color: var(--text-muted);
      font-size: 14px;
      line-height: 1.6;
      margin: 0 0 20px 0;
      flex-grow: 1;
    }
    .bulletin-meta {
      font-size: 12px;
      color: #94a3b8;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    /* Footer */
    .footer {
      background: #0f172a;
      color: white;
      padding: 80px 40px 40px 40px;
    }
    .footer-grid {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 2fr 1fr 1fr;
      gap: 60px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding-bottom: 40px;
      margin-bottom: 40px;
    }
    .footer-col h3 {
      font-size: 15px;
      font-weight: 700;
      text-transform: uppercase;
      margin: 0 0 20px 0;
      letter-spacing: 0.5px;
      color: #94a3b8;
    }
    .footer-col ul {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .footer-col ul a {
      color: #94a3b8;
      text-decoration: none;
      font-size: 14px;
      transition: color 0.2s;
    }
    .footer-col ul a:hover {
      color: white;
    }
    .footer-bottom {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #64748b;
      font-size: 13px;
      flex-wrap: wrap;
      gap: 16px;
    }

    /* Mobile Drawer & Hamburger Styles */
        .mobile-menu-toggle {
      display: none;
      background: none;
      border: none;
      color: #ffffff;
      font-size: 24px;
      cursor: pointer;
      padding: 6px;
      z-index: 1010;
      transition: color 0.2s ease;
    }
    .mobile-menu-toggle:hover {
      color: #fca5a5;
    }
    .mobile-drawer {
      position: fixed;
      top: 0;
      right: -320px;
      width: 300px;
      height: 100vh;
      background: #ffffff;
      box-shadow: -10px 0 40px rgba(15, 23, 42, 0.08);
      z-index: 2000;
      box-sizing: border-box;
      padding: 40px 24px;
      display: flex;
      flex-direction: column;
      gap: 32px;
      transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .mobile-drawer.active {
      right: 0;
    }
    .mobile-drawer-close {
      position: absolute;
      top: 24px;
      right: 24px;
      background: none;
      border: none;
      color: var(--text-muted);
      font-size: 22px;
      cursor: pointer;
      transition: color 0.2s ease;
    }
    .mobile-drawer-close:hover {
      color: var(--text-main);
    }
    .drawer-logo-container {
      display: flex;
      justify-content: flex-start;
      margin-bottom: 8px;
    }
    .drawer-links {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 24px;
    }
    .drawer-links a {
      text-decoration: none;
      color: #334155;
      font-weight: 700;
      font-size: 15px;
      display: block;
      transition: color 0.2s;
    }
    .drawer-links a:hover {
      color: var(--primary-color);
    }
    .drawer-actions {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-top: auto;
    }
    .btn-drawer-primary {
      background: var(--primary-color);
      color: white;
      text-align: center;
      padding: 14px;
      border-radius: 8px;
      font-weight: 700;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.2s;
    }
    .btn-drawer-primary:hover {
      background: var(--primary-dark);
    }
    .btn-drawer-secondary {
      border: 1px solid #cbd5e1;
      color: #334155;
      text-align: center;
      padding: 14px;
      border-radius: 8px;
      font-weight: 700;
      text-decoration: none;
      font-size: 14px;
      transition: background 0.2s;
    }
    .btn-drawer-secondary:hover {
      background: var(--bg-light);
    }
    .drawer-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(15, 23, 42, 0.4);
      backdrop-filter: blur(4px);
      z-index: 1500;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.4s ease;
    }
    .drawer-overlay.active {
      opacity: 1;
      pointer-events: auto;
    }

    /* Media Queries for Cross-Device Responsiveness */
    @media(max-width: 992px) {
      .hero-grid-container { grid-template-columns: 1fr; gap: 40px; text-align: center; }
      .hero-content { text-align: center; }
      .hero-ctas { justify-content: center; }
      .track-section { grid-template-columns: 1fr; gap: 40px; }
      .chart-bar { width: 18%; }
    }

    @media(max-width: 768px) {
      .navbar { padding: 14px 20px; }
      .navbar.scrolled { padding: 10px 20px; }
      .nav-links, .nav-actions { display: none; }
      .mobile-menu-toggle { display: block; }
      .track-section { padding: 40px 24px; }
      .footer-grid { grid-template-columns: 1fr; gap: 40px; }
      .hero h1 { font-size: 36px; }
    }

    @media(max-width: 576px) {
      .section { padding: 60px 20px; }
      .services-grid { grid-template-columns: 1fr; gap: 20px; }
      .hero h1 { font-size: 28px; }
      .hero p { font-size: 15px; margin-bottom: 24px; }
      .hero-ctas { flex-direction: column; gap: 12px; }
      .btn-hero-primary, .btn-hero-secondary { width: 100%; text-align: center; box-sizing: border-box; }
      .track-section { padding: 32px 16px; }
      .track-info h2 { font-size: 24px; }
      .track-info p { font-size: 14px; }
      .feature-list li { font-size: 13px; }
      .track-box-widget { padding: 24px 16px; }
      .footer { padding: 60px 20px 30px 20px; }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar">
    <div class="logo-container">
      <img src="{{ asset('assets/images/pili_logo.png?v=2') }}" alt="Barangay Pili Logo" style="height: 48px; width: auto; object-fit: contain;">
    </div>
    <ul class="nav-links">
      <li><a href="#">HOME</a></li>
      <li><a href="#services">BARANGAY SERVICES</a></li>
      <li><a href="#tracking">TRACK APPLICATION</a></li>
      <li><a href="#bulletins">ANNOUNCEMENT</a></li>
      <li><a href="{{ asset('downloads/barangay-pili-resident-portal-v1.0.3.apk') }}" download>DOWNLOAD APP</a></li>
    </ul>
    <div class="nav-actions">
      <a href="{{ route('login') }}" class="btn-nav-secondary">Resident Login</a>
      <a href="{{ route('register') }}" class="btn-nav-primary">Register Account</a>
    </div>
    <button class="mobile-menu-toggle" aria-label="Toggle Menu">
      <i class="fas fa-bars"></i>
    </button>
  </nav>

  <!-- Mobile Menu Drawer -->
  <div class="mobile-drawer">
    <button class="mobile-drawer-close" aria-label="Close Menu">
      <i class="fas fa-times"></i>
    </button>
    <div class="drawer-logo-container">
      <img src="{{ asset('assets/images/pili_logo.png?v=2') }}" alt="Barangay Pili Logo" style="height: 54px; width: auto; object-fit: contain;">
    </div>
    <ul class="drawer-links">
      <li><a href="#">HOME</a></li>
      <li><a href="#services">BARANGAY SERVICES</a></li>
      <li><a href="#tracking">TRACK APPLICATION</a></li>
      <li><a href="#bulletins">ANNOUNCEMENT</a></li>
      <li><a href="{{ asset('downloads/barangay-pili-resident-portal-v1.0.3.apk') }}" download>DOWNLOAD APP</a></li>
    </ul>
    <div class="drawer-actions">
      <a href="{{ route('login') }}" class="btn-drawer-secondary">Resident Login</a>
      <a href="{{ route('register') }}" class="btn-drawer-primary">Register Account</a>
    </div>
  </div>
  <div class="drawer-overlay"></div>

  <!-- Hero Section -->
    <header class="hero">
    <!-- Animated Shapes -->
    <div class="bg-shape bg-circle-1"></div>
    <div class="bg-shape bg-circle-2"></div>
    <div class="bg-shape bg-triangle-1"></div>
    <div class="bg-shape bg-triangle-2"></div>
    <div class="bg-shape bg-outline-1"></div>

    <div class="hero-grid-container">
      <div class="hero-content">
        <div style="font-weight: 700; color: var(--primary-color); letter-spacing: 2px; margin-bottom: 16px; font-size: 14px; text-transform: uppercase;">
          BARANGAY PILI
        </div>
        <h1>Modern Digital Services for Residents</h1>
        <p>Request certificates, submit applications, track requests, and receive announcements online.</p>
        <div class="hero-ctas">
          <a href="{{ route('login') }}" class="btn-hero-primary"><i class="fas fa-id-card" style="margin-right:8px;"></i> Request Certifications</a>
          <a href="#tracking" class="btn-hero-secondary"><i class="fas fa-search" style="margin-right:8px;"></i> Track Request</a>
        </div>
      </div>
    </div>
    
    
  </header>

  <!-- Services Grid -->
  <section class="section" id="services">
    <div class="section-header">
      <h2>Document Services</h2>
      <p>Request official barangay certificates and submit reports completely online. Fast processing with digital logs.</p>
    </div>
    <div class="services-grid">
      
      <div class="service-card">
        <div class="service-icon"><i class="fas fa-file-shield"></i></div>
        <h3>Barangay Clearances</h3>
        <p>Issuance of official barangay documents, including Barangay Clearance, Certificate of Indigency, and Certificate of Residency for residents.</p>
        <a href="{{ route('login') }}" class="service-link">File Request <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="service-card">
        <div class="service-icon"><i class="fas fa-briefcase"></i></div>
        <h3>Business Clearance</h3>
        <p>Renewals of Barangay Business Clearances for local businesses and establishments operating within the barangay.</p>
        <a href="{{ route('login') }}" class="service-link">File Request <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="service-card">
        <div class="service-icon"><i class="fas fa-file-signature"></i></div>
        <h3>Blotters</h3>
        <p>Record of incidents, complaints, or community disputes reported to the barangay for proper documentation and resolution.</p>
        <a href="{{ route('login') }}" class="service-link">File Blotter <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="service-card">
        <div class="service-icon"><i class="fas fa-gavel"></i></div>
        <h3>Summons</h3>
        <p>Generation, issuance, and tracking of barangay summons requiring involved parties to appear for mediation, hearings, or other official barangay proceedings.</p>
        <a href="{{ route('login') }}" class="service-link">Access Portal <i class="fas fa-arrow-right"></i></a>
      </div>

      <div class="service-card">
        <div class="service-icon"><i class="fas fa-chair"></i></div>
        <h3>Borrow Equipment</h3>
        <p>Request and reservation of barangay-owned equipment and facilities for community events, official activities, or personal use, subject to barangay policies and availability.</p>
        <a href="{{ route('login') }}" class="service-link">Reserve Equipment <i class="fas fa-arrow-right"></i></a>
      </div>

    </div>
  </section>

  <!-- Realtime tracking -->
  <section class="section" id="tracking" style="padding-top:0;">
    <div class="track-section">
      <div class="track-info">
        <h2>Zero Login Public Tracking</h2>
        <p>Monitor your filed document requests in real-time. Simply type in your unique 10-character application tracking number to visually track approval and printing milestones.</p>
        <ul class="feature-list">
          <li><i class="fas fa-check-circle"></i> Live timeline checklist milestones</li>
          <li><i class="fas fa-check-circle"></i> View payment receipt logs</li>
          <li><i class="fas fa-check-circle"></i> Receive immediate staff notice messages</li>
        </ul>
      </div>
      <div class="track-box-widget">
        <form method="POST" action="{{ route('track') }}" class="track-form">
          @csrf
          <label>Enter Tracking Number</label>
          <input type="text" name="tracking" placeholder="e.g. PILI-20260730-XXXX" required>
          <button type="submit" class="btn-track-submit"><i class="fas fa-search"></i> Track Application</button>
        </form>
      </div>
    </div>
  </section>

  <!-- Announcements / Bulletins -->
  <section class="section" id="bulletins" style="padding-top:0;">
    <div class="section-header">
      <h2>Announcements &amp; Advisories</h2>
      <p>Stay up to date with official programs, schedules, and alerts from the Barangay Pili administration.</p>
    </div>
    <div class="bulletins-row">

      @forelse($bulletins as $bulletin)
        @php
          $cat = strtolower($bulletin->category ?? 'general');
          $colorMap = [
            'health'      => ['border' => '#b91c1c', 'bg' => '#fef2f2',   'text' => '#b91c1c'],
            'environment' => ['border' => '#10b981', 'bg' => '#ecfdf5',   'text' => '#059669'],
            'alert'       => ['border' => '#ef4444', 'bg' => '#fee2e2',   'text' => '#b91c1c'],
            'education'   => ['border' => '#3b82f6', 'bg' => '#eff6ff',   'text' => '#1d4ed8'],
            'livelihood'  => ['border' => '#f59e0b', 'bg' => '#fffbeb',   'text' => '#d97706'],
            'sports'      => ['border' => '#8b5cf6', 'bg' => '#f5f3ff',   'text' => '#7c3aed'],
            'safety'      => ['border' => '#ef4444', 'bg' => '#fee2e2',   'text' => '#b91c1c'],
            'general'     => ['border' => 'var(--primary-color)', 'bg' => '#fef2f2', 'text' => 'var(--primary-color)'],
          ];
          $colors = $colorMap[$cat] ?? $colorMap['general'];
        @endphp
        <div class="bulletin-item" style="border-top: 4px solid {{ $colors['border'] }};">
          @if($bulletin->image_path && file_exists(public_path('assets/uploads/' . $bulletin->image_path)))
            <div style="margin-bottom: 20px; border-radius: 12px; height: 160px; background-color: #f1f5f9; background-image: url('{{ asset('assets/uploads/' . $bulletin->image_path) }}'); background-size: cover; background-position: center;">
            </div>
          @endif
          @if($bulletin->is_pinned)
            <span style="font-size:11px; color:#f59e0b; font-weight:700; margin-bottom:4px; display:block;">
              <i class="fas fa-thumbtack"></i> PINNED
            </span>
          @endif
          <span class="bulletin-badge" style="background:{{ $colors['bg'] }}; color:{{ $colors['text'] }};">
            {{ ucfirst($bulletin->category) }}
          </span>
          <h4>{{ $bulletin->title }}</h4>
          <p>{{ Str::limit($bulletin->content, 160) }}</p>
          <div class="bulletin-meta">
            <i class="fas fa-calendar-alt"></i>
            {{ optional($bulletin->published_at)->format('F d, Y') ?? $bulletin->created_at->format('F d, Y') }}
          </div>
        </div>
      @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--text-muted);">
          <i class="fas fa-bullhorn" style="font-size: 40px; margin-bottom: 16px; display: block; opacity: 0.3;"></i>
          <p style="font-size: 16px; font-weight: 600;">No announcements yet.</p>
          <p style="font-size: 14px;">Check back soon for updates from Barangay Pili.</p>
        </div>
      @endforelse

    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-grid">
      <div class="footer-col" style="padding-right:40px;">
        <div style="margin-bottom:16px;">
          <img src="{{ asset('assets/images/pili_logo.png?v=2') }}" alt="Barangay Pili Logo" style="height: 60px; width: auto; object-fit: contain;">
        </div>
        <p style="color:#94a3b8; font-size:14px; line-height:1.6;">Our mission is to establish a transparent, digital, and streamlined administrative portal empowering residents with reliable public service document deliveries and complaint conciliation facilities.</p>
      </div>
      <div class="footer-col">
        <h3>Portal Access</h3>
        <ul>
          <li><a href="{{ route('login') }}">Resident Sign In</a></li>
          <li><a href="{{ route('register') }}">Resident Registration</a></li>
          <li><a href="{{ route('login') }}">Admin Login Portal</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h3>Contact Info</h3>
        <ul style="color:#94a3b8; font-size:14px; gap:12px; display:flex; flex-direction:column; list-style:none; padding:0; margin:0;">
          <li style="display:flex; align-items:flex-start; gap:10px;">
            <i class="fas fa-map-marker-alt" style="color:#b91c1c; margin-top:3px;"></i> 
            <span style="line-height:1.4;">Barangay Pili, Madridejos, Cebu, Philippines</span>
          </li>
          <li style="display:flex; align-items:flex-start; gap:10px;">
            <i class="fas fa-envelope" style="color:#b91c1c; margin-top:3px;"></i> 
            <span style="line-height:1.4; word-break:break-all;">adminbrgypilieclearance@gmail.com</span>
          </li>
          <li style="display:flex; align-items:flex-start; gap:10px;">
            <i class="fas fa-phone-alt" style="color:#b91c1c; margin-top:3px;"></i> 
            <span style="line-height:1.4;">+63 917 123 4567</span>
          </li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom" style="justify-content:center;">
      <div>&copy; 2026 Barangay Pili Online Services Portal. All Rights Reserved.</div>
    </div>
  </footer>

  <!-- Scroll & Mobile Drawer JS Script -->
  <script>
    // Scroll Listener
    window.addEventListener('scroll', function() {
      const navbar = document.querySelector('.navbar');
      if (window.scrollY > 20) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });

    // Mobile Drawer Toggle
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const drawerClose = document.querySelector('.mobile-drawer-close');
    const mobileDrawer = document.querySelector('.mobile-drawer');
    const drawerOverlay = document.querySelector('.drawer-overlay');
    const drawerLinks = document.querySelectorAll('.drawer-links a, .drawer-actions a');

    function openDrawer() {
      mobileDrawer.classList.add('active');
      drawerOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
      mobileDrawer.classList.remove('active');
      drawerOverlay.classList.remove('active');
      document.body.style.overflow = '';
    }

    if (mobileToggle) mobileToggle.addEventListener('click', openDrawer);
    if (drawerClose) drawerClose.addEventListener('click', closeDrawer);
    if (drawerOverlay) drawerOverlay.addEventListener('click', closeDrawer);

    // Auto close drawer when a drawer link is clicked
    drawerLinks.forEach(link => {
      link.addEventListener('click', closeDrawer);
    });
  </script>

</body>
</html>
