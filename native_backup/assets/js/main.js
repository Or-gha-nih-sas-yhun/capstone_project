// ── Sidebar toggle ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('sidebar-toggle');
  const sidebar   = document.getElementById('sidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', (e) => {
      if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
        sidebar.classList.remove('open');
      }
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
