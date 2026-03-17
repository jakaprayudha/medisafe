<style>
  /* FOOTER */
  .app-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-top: 1px solid #e5e7eb;
    z-index: 999;
  }

  .footer-inner {
    padding: 8px 16px;
  }

  .footer-text {
    font-size: 12px;
    color: #6b7280;
  }

  .footer-text a {
    color: #0f9b8e;
    font-weight: 600;
    text-decoration: none;
  }

  .footer-text a:hover {
    text-decoration: underline;
  }

  /* SPACE BIAR GA KETUTUP */
  .body-wrapper-inner {
    padding-bottom: 60px;
  }

  /* FLOATING HELP BUTTON */
  .floating-help {
    position: fixed;
    bottom: 70px;
    right: 20px;
    width: 55px;
    height: 55px;
    background: linear-gradient(135deg, #0f9b8e, #38ef7d);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 22px;
    cursor: pointer;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    transition: 0.3s;
  }

  .floating-help:hover {
    transform: scale(1.1);
  }

  /* CHAT BOX */
  .help-chat-box {
    position: fixed;
    bottom: 140px;
    right: 20px;
    width: 280px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    display: none;
    overflow: hidden;
    z-index: 1000;
  }

  .help-header {
    background: linear-gradient(135deg, #0f9b8e, #38ef7d);
    color: white;
    padding: 10px;
    font-size: 14px;
  }

  .help-body {
    padding: 10px;
    font-size: 13px;
    color: #555;
  }

  .help-body a {
    display: block;
    margin-top: 8px;
    color: #0f9b8e;
    text-decoration: none;
  }

  .help-body a:hover {
    text-decoration: underline;
  }
</style>
<footer class="app-footer">
  <div class="footer-inner text-center">
    <span class="footer-text">
      Medisafe © 2026 • Developed by
      <a href="https://imzack.id/" target="_blank">ImZack</a>
    </span>
  </div>
</footer>

<!-- FLOATING HELP -->
<div class="floating-help" onclick="toggleHelp()">
  💬
</div>

<!-- CHAT BOX -->
<div class="help-chat-box" id="helpBox">
  <div class="help-header">
    Bantuan Medisafe
  </div>
  <div class="help-body">
    Butuh bantuan? Hubungi kami:

    <a href="https://wa.me/628xxxx" target="_blank">📱 WhatsApp</a>
    <a href="mailto:support@medisafe.id">📧 Email Support</a>
  </div>
</div>
<script>
  function toggleHelp() {
    const box = document.getElementById("helpBox");
    box.style.display = box.style.display === "block" ? "none" : "block";
  }
</script>