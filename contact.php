<?php
require_once __DIR__ . '/includes/config.php';
$pageCss = '';
include __DIR__ . '/includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="contact-page">
  <div class="contact-container">
    <div class="contact-header">
      <h1 class="contact-title">Contact Us</h1>
      <p class="contact-subtitle">We’re here to help</p>
    </div>

    <section class="contact-section">
      <div class="contact-card">
        <div class="contact-item">
          <div class="contact-label">Email</div>
          <a class="contact-value" href="mailto:glamessentialscompany@gmail.com">glamessentialscompany@gmail.com</a>
        </div>
        <div class="divider"></div>
        <div class="contact-item">
          <div class="contact-label">Cellphone</div>
          <a class="contact-value" href="tel:09308357185">09308357185</a>
        </div>
      </div>
      <p class="contact-note">Business hours: Mon–Fri, 9:00 AM – 6:00 PM</p>
    </section>
  </div>
</main>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Montserrat', sans-serif; background: #ffffff; color: #1a1a1a; line-height: 1.6; }
.contact-page { min-height: 80vh; padding: 100px 24px 60px; background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%); }
.contact-container { max-width: 800px; margin: 0 auto; }
.contact-header { text-align: center; margin-bottom: 36px; }
.contact-title { font-family: 'Playfair Display', serif; font-size: 46px; font-weight: 400; color: #0a0a0a; margin-bottom: 8px; }
.contact-subtitle { font-size: 14px; color: rgba(0,0,0,0.55); }
.contact-section { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.contact-card { width: 100%; background: #fff; border: 1px solid rgba(0,0,0,0.08); padding: 28px; display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; }
.contact-item { display: flex; flex-direction: column; gap: 6px; }
.contact-label { font-size: 12px; letter-spacing: 1.2px; text-transform: uppercase; color: rgba(0,0,0,0.6); }
.contact-value { font-size: 16px; color: #0a0a0a; text-decoration: none; border-bottom: 1px solid rgba(0,0,0,0.12); width: fit-content; }
.contact-value:hover { border-bottom-color: #0a0a0a; }
.divider { width: 1px; height: 36px; background: rgba(0,0,0,0.1); margin: 0 18px; }
.contact-note { font-size: 12px; color: rgba(0,0,0,0.55); margin-top: 10px; }
@media (max-width: 640px) {
  .contact-title { font-size: 34px; }
  .contact-card { grid-template-columns: 1fr; gap: 16px; }
  .divider { display: none; }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
