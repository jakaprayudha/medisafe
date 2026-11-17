// Interaksi form Login & Reset Password (tanpa dependensi)
(function () {
  var form = document.getElementById('authForm');
  if (!form) return;

  var user = document.getElementById('username');
  var pass = document.getElementById('password');
  var btn = document.getElementById('btnSubmit');
  var toggle = document.getElementById('togglePass');
  var caps = document.getElementById('capsHint');
  var remember = document.getElementById('rememberUser');
  var KEY = 'medisafe_remember_user';

  // Ingat username (hanya kenyamanan per browser)
  if (remember && user) {
    try {
      var saved = localStorage.getItem(KEY);
      if (saved) {
        user.value = saved;
        remember.checked = true;
        if (pass) pass.focus();
      }
    } catch (e) { /* storage tidak tersedia */ }
  }

  // Tampilkan / sembunyikan password
  if (toggle && pass) {
    toggle.addEventListener('click', function () {
      var show = pass.type === 'password';
      pass.type = show ? 'text' : 'password';
      toggle.classList.toggle('on', show);
      toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
      toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
      pass.focus();
    });
  }

  // Peringatan Caps Lock
  if (caps && pass) {
    var cek = function (e) {
      if (e.getModifierState) caps.classList.toggle('show', e.getModifierState('CapsLock'));
    };
    pass.addEventListener('keyup', cek);
    pass.addEventListener('keydown', cek);
    pass.addEventListener('blur', function () { caps.classList.remove('show'); });
  }

  // Validasi ringan + status loading
  form.addEventListener('submit', function (e) {
    var kosong = [user, pass].filter(function (el) { return el && !el.value.trim(); });
    if (kosong.length) {
      e.preventDefault();
      kosong[0].focus();
      kosong.forEach(function (el) {
        el.style.borderColor = '#d64545';
        el.addEventListener('input', function h() { el.style.borderColor = ''; el.removeEventListener('input', h); });
      });
      return;
    }
    if (remember) {
      try {
        if (remember.checked) localStorage.setItem(KEY, user.value.trim());
        else localStorage.removeItem(KEY);
      } catch (err) { /* abaikan */ }
    }
    if (btn) {
      if (btn.classList.contains('loading')) { e.preventDefault(); return; }
      btn.classList.add('loading');
      var t = btn.querySelector('.label-text');
      if (t) t.textContent = 'Memproses...';
    }
  });
})();
