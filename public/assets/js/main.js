// ── Sidebar toggle ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('sidebar-toggle');
  const sidebar   = document.getElementById('sidebar');
  const overlay   = document.getElementById('sidebar-overlay');

  function openSidebar() {
    sidebar && sidebar.classList.add('open');
    overlay && overlay.classList.add('show');
  }
  function closeSidebar() {
    sidebar && sidebar.classList.remove('open');
    overlay && overlay.classList.remove('show');
  }

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => {
      sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
  }

  // Clicking overlay closes sidebar
  if (overlay) {
    overlay.addEventListener('click', closeSidebar);
  }

  // Close sidebar on nav-link click (mobile)
  if (sidebar) {
    sidebar.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 768) closeSidebar();
      });
    });
  }


  // ── Flash auto-dismiss ─────────────────────────────────
  const flash = document.getElementById('flash-message');
  if (flash) setTimeout(() => flash.remove(), 4500);

  // ── Confirm delete ─────────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm || 'Are you sure?')) e.preventDefault();
    });
  });

  // ── Tooltips ───────────────────────────────────────────
  document.querySelectorAll('[data-tooltip]').forEach(el => {
    el.title = el.dataset.tooltip;
  });
});

// Prevent browser zoom shortcuts for kiosk-style deployment.
document.addEventListener('wheel', e => {
  if (e.ctrlKey) e.preventDefault();
}, { passive: false });

document.addEventListener('keydown', e => {
  const key = e.key.toLowerCase();
  if ((e.ctrlKey || e.metaKey) && ['+', '=', '-', '_', '0'].includes(key)) {
    e.preventDefault();
  }
});

document.addEventListener('gesturestart', e => e.preventDefault());
document.addEventListener('gesturechange', e => e.preventDefault());
document.addEventListener('gestureend', e => e.preventDefault());

// ── Modal helpers ────────────────────────────────────────
function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('show');
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('show');
}
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('show');
  }
});

// ── Auth tabs ────────────────────────────────────────────
function switchTab(tabId, btn) {
  document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
  btn.classList.add('active');
  const panel = document.getElementById(tabId);
  if (panel) panel.style.display = 'block';
}

// ── Preview file upload ───────────────────────────────────
function previewImage(input, imgId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.getElementById(imgId);
      if (img) img.src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// ── Table filter search ────────────────────────────────────
function filterTable(inputId, tableId) {
  const val = document.getElementById(inputId).value.toLowerCase();
  const rows = document.querySelectorAll(`#${tableId} tbody tr`);
  rows.forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
  });
}

// ── Print page ────────────────────────────────────────────
function printDocument(elId) {
  const content = document.getElementById(elId).innerHTML;
  const win = window.open('', '_blank');
  win.document.write('<html><head><title>Print</title>');
  win.document.write('<link rel="stylesheet" href="' + window.location.origin + '/Brgy.pili_clearance/assets/css/style.css">');
  win.document.write('</head><body onload="window.print();window.close()">');
  win.document.write(content);
  win.document.write('</body></html>');
  win.document.close();
}

// ── Chart helper (dashboard) ─────────────────────────────
function drawBarChart(canvasId, labels, data, color = '#1a56db') {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const max = Math.max(...data, 1);
  const w = canvas.width, h = canvas.height;
  const barW = w / (labels.length * 2);
  ctx.clearRect(0, 0, w, h);
  ctx.fillStyle = '#f3f4f6';
  ctx.fillRect(0, 0, w, h);
  data.forEach((val, i) => {
    const x = i * (barW * 2) + barW * 0.5;
    const barH = (val / max) * (h - 40);
    ctx.fillStyle = color;
    ctx.beginPath();
    ctx.roundRect(x, h - barH - 20, barW, barH, 4);
    ctx.fill();
    ctx.fillStyle = '#374151';
    ctx.font = '10px Inter';
    ctx.textAlign = 'center';
    ctx.fillText(labels[i], x + barW / 2, h - 4);
    ctx.fillText(val, x + barW / 2, h - barH - 26);
  });
}
// ── Stylish Form Validation ───────────────────────────────
(function () {
  // Inject validation styles once
  const style = document.createElement('style');
  style.textContent = `
    .form-control.is-valid,
    .form-select.is-valid {
      border-color: #0d9488 !important;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='%230d9488' d='M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 16px;
      padding-right: 40px !important;
      box-shadow: 0 0 0 3px rgba(13,148,136,.12) !important;
    }
    .form-control.is-invalid,
    .form-select.is-invalid {
      border-color: #dc2626 !important;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='%23dc2626' d='M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm0 3.5a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5zm0 6a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      background-size: 16px;
      padding-right: 40px !important;
      box-shadow: 0 0 0 3px rgba(220,38,38,.12) !important;
    }
    .field-error-msg {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 11.5px;
      font-weight: 600;
      color: #dc2626;
      margin-top: 5px;
      animation: slideInError .18s ease;
    }
    .field-error-msg::before {
      content: '\\f071';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      font-size: 10px;
    }
    .field-success-msg {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 11.5px;
      font-weight: 600;
      color: #0d9488;
      margin-top: 5px;
      animation: slideInError .18s ease;
    }
    .field-success-msg::before {
      content: '\\f00c';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      font-size: 10px;
    }
    @keyframes slideInError {
      from { opacity: 0; transform: translateY(-4px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .btn-loading {
      opacity: .7;
      pointer-events: none;
      position: relative;
    }
    .btn-loading::after {
      content: '';
      display: inline-block;
      width: 14px;
      height: 14px;
      margin-left: 8px;
      border: 2px solid rgba(255,255,255,.5);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin .6s linear infinite;
      vertical-align: middle;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Password strength bar */
    .pw-strength-bar {
      height: 4px;
      border-radius: 2px;
      margin-top: 6px;
      background: #e5e7eb;
      overflow: hidden;
      transition: all .3s;
    }
    .pw-strength-fill {
      height: 100%;
      border-radius: 2px;
      transition: width .3s ease, background-color .3s ease;
      width: 0%;
    }
    .pw-strength-label {
      font-size: 11px;
      font-weight: 600;
      margin-top: 3px;
      color: #9ca3af;
    }
  `;
  document.head.appendChild(style);

  function showError(input, msg) {
    input.classList.remove('is-valid');
    input.classList.add('is-invalid');
    let hint = input.parentElement.querySelector('.field-error-msg, .field-success-msg');
    if (hint) hint.remove();
    hint = document.createElement('div');
    hint.className = 'field-error-msg';
    hint.textContent = msg;
    input.parentElement.appendChild(hint);
  }

  function showSuccess(input, msg = '') {
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
    let hint = input.parentElement.querySelector('.field-error-msg, .field-success-msg');
    if (hint) hint.remove();
    if (msg) {
      hint = document.createElement('div');
      hint.className = 'field-success-msg';
      hint.textContent = msg;
      input.parentElement.appendChild(hint);
    }
  }

  function clearState(input) {
    input.classList.remove('is-valid', 'is-invalid');
    const hint = input.parentElement.querySelector('.field-error-msg, .field-success-msg');
    if (hint) hint.remove();
  }

  function validateField(input) {
    const val = input.value.trim();
    const name = input.name || input.id || '';
    const type = input.type;

    // Skip hidden, submit, button types
    if (['hidden','submit','button','checkbox','radio','file'].includes(type)) return true;

    // Required check
    if (input.required && !val) {
      showError(input, 'This field is required.');
      return false;
    }

    if (!val) { clearState(input); return true; } // optional & empty = ok

    // Email
    if (type === 'email' || name === 'email') {
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
        showError(input, 'Please enter a valid email address.');
        return false;
      }
      showSuccess(input, 'Looks good!');
      return true;
    }

    // Phone / contact
    if (name === 'contact_number' || name.includes('phone') || name.includes('contact')) {
      if (!/^(09|\+639)\d{9}$/.test(val.replace(/\s/g,''))) {
        showError(input, 'Enter a valid PH number (e.g. 09XXXXXXXXX).');
        return false;
      }
      showSuccess(input);
      return true;
    }

    // Username
    if (name === 'username') {
      if (val.length < 3) { showError(input, 'Username must be at least 3 characters.'); return false; }
      if (/\s/.test(val)) { showError(input, 'Username cannot contain spaces.'); return false; }
      showSuccess(input);
      return true;
    }

    // Password (registration)
    if (type === 'password' && input.hasAttribute('minlength')) {
      const min = parseInt(input.getAttribute('minlength') || 6);
      if (val.length < min) {
        showError(input, `Password must be at least ${min} characters.`);
        return false;
      }
      showSuccess(input);
      return true;
    }

    // Number min
    if (type === 'number' && input.hasAttribute('min')) {
      if (parseFloat(val) < parseFloat(input.getAttribute('min'))) {
        showError(input, `Value must be at least ${input.getAttribute('min')}.`);
        return false;
      }
    }

    // Date
    if (type === 'date' && input.hasAttribute('max')) {
      if (new Date(val) > new Date(input.getAttribute('max'))) {
        showError(input, 'Date cannot be in the future.');
        return false;
      }
    }

    // Pattern
    if (input.pattern && val) {
      const re = new RegExp('^(?:' + input.pattern + ')$');
      if (!re.test(val)) {
        showError(input, input.title || 'Invalid format.');
        return false;
      }
    }

    showSuccess(input);
    return true;
  }

  // Password strength indicator
  function addPasswordStrength(pwInput) {
    let bar = pwInput.parentElement.querySelector('.pw-strength-bar');
    if (bar) return;
    bar = document.createElement('div');
    bar.className = 'pw-strength-bar';
    const fill = document.createElement('div');
    fill.className = 'pw-strength-fill';
    bar.appendChild(fill);
    const label = document.createElement('div');
    label.className = 'pw-strength-label';
    pwInput.parentElement.appendChild(bar);
    pwInput.parentElement.appendChild(label);

    pwInput.addEventListener('input', () => {
      const v = pwInput.value;
      let score = 0;
      if (v.length >= 6) score++;
      if (v.length >= 10) score++;
      if (/[A-Z]/.test(v)) score++;
      if (/[0-9]/.test(v)) score++;
      if (/[^A-Za-z0-9]/.test(v)) score++;
      const pct = (score / 5) * 100;
      const colors = ['#dc2626','#f97316','#d97706','#0d9488','#059669'];
      const labels = ['Too weak','Weak','Fair','Good','Strong'];
      fill.style.width = pct + '%';
      fill.style.backgroundColor = colors[score - 1] || '#e5e7eb';
      label.textContent = score ? labels[score - 1] : '';
      label.style.color = colors[score - 1] || '#9ca3af';
    });
  }

  function initFormValidation() {
    // Attach blur + input validation to all form fields
    document.querySelectorAll('form input, form select, form textarea').forEach(input => {
      if (['hidden','submit','button','checkbox','radio'].includes(input.type)) return;

      // Add strength bar to password fields in registration
      if (input.type === 'password' && input.hasAttribute('minlength')) {
        addPasswordStrength(input);
      }

      input.addEventListener('blur', () => validateField(input));
      input.addEventListener('input', () => {
        if (input.classList.contains('is-invalid')) validateField(input);
      });
    });

    // On form submit: validate all + show loading state
    document.querySelectorAll('form').forEach(form => {
      // Skip logout form
      if (form.id === 'logout-form') return;

      form.addEventListener('submit', function (e) {
        let valid = true;
        form.querySelectorAll('input, select, textarea').forEach(input => {
          if (['hidden','submit','button','checkbox','radio'].includes(input.type)) return;
          if (!validateField(input)) valid = false;
        });

        if (!valid) {
          e.preventDefault();
          // Scroll to first error
          const first = form.querySelector('.is-invalid');
          if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
          return;
        }

        // Show loading on submit button
        const btn = form.querySelector('[type="submit"]');
        if (btn) {
          btn.classList.add('btn-loading');
          btn.textContent = 'Please wait…';
        }
      });
    });
  }

  // Init on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFormValidation);
  } else {
    initFormValidation();
  }
})();
