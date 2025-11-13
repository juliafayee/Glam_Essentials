<?php
require_once __DIR__ . '/includes/config.php';
$pageCss = '';
include __DIR__ . '/includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="shipping-page">
  <div class="shipping-container">
    <div class="shipping-header">
      <h1 class="shipping-title">Shipping & Delivery</h1>
      <p class="shipping-subtitle">Information about shipping options, timelines, and order tracking</p>
    </div>

    <section class="shipping-section">
      <h2 class="section-title">| Shipping Options</h2>
      <div class="section-content">
        <ul>
          <li><strong>Standard Shipping:</strong> 5–7 business days</li>
          <li><strong>Express Shipping:</strong> 2–4 business days</li>
          <li><strong>Overnight Shipping:</strong> Next business day (selected areas)</li>
        </ul>
      </div>
    </section>

    <section class="shipping-section">
      <h2 class="section-title">| Processing Times</h2>
      <div class="section-content">
        <p>Orders are processed within 1–2 business days. Orders placed after 2 PM are processed on the next business day. High volume periods may require an additional 1–2 days.</p>
      </div>
    </section>

    <section class="shipping-section">
      <h2 class="section-title">| Shipping Rates</h2>
      <div class="section-content">
        <ul>
          <li><strong>Standard:</strong> Calculated at checkout based on weight and location</li>
          <li><strong>Express/Overnight:</strong> Displayed at checkout</li>
          <li><strong>Free Shipping:</strong> Eligible orders may qualify based on promotions</li>
        </ul>
      </div>
    </section>

    <section class="shipping-section">
      <h2 class="section-title">| Order Tracking</h2>
      <div class="section-content">
        <p>Once your order ships, you will receive an email with a tracking number. You can also view tracking details in your account under <em>My Orders</em>.</p>
      </div>
    </section>

    <section class="shipping-section">
      <h2 class="section-title">| International Shipping</h2>
      <div class="section-content">
        <p>We currently ship within selected regions. For international shipping inquiries, please contact our support team.</p>
      </div>
    </section>

    <section class="shipping-section">
      <h2 class="section-title">| Damaged or Lost Packages</h2>
      <div class="section-content">
        <p>If your package arrives damaged or is lost in transit, contact us within 48 hours with your order number and photos (if applicable). We will assist with a replacement or claim.</p>
      </div>
    </section>

    <section class="shipping-section">
      <h2 class="section-title">| Address Changes & Cancellations</h2>
      <div class="section-content">
        <p>Address changes or order cancellations can be requested within 24 hours of placing your order. After shipping, we are unable to modify the address.</p>
      </div>
    </section>

    <section class="shipping-section">
      <h2 class="section-title">| Support</h2>
      <div class="section-content">
        <p>Questions? Email us at <a href="mailto:glamessentialscompany@gmail.com">glamessentialscompany@gmail.com</a>.</p>
      </div>
    </section>
  </div>
</main>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Montserrat', sans-serif; background: #ffffff; color: #1a1a1a; line-height: 1.6; }
.shipping-page { min-height: 100vh; padding: 100px 30px 60px; background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%); }
.shipping-container { max-width: 1000px; margin: 0 auto; }
.shipping-header { text-align: center; margin-bottom: 50px; }
.shipping-title { font-family: 'Playfair Display', serif; font-size: 46px; font-weight: 400; color: #0a0a0a; margin-bottom: 10px; }
.shipping-subtitle { font-size: 14px; color: rgba(0,0,0,0.55); }
.shipping-section { background: #ffffff; border: 1px solid rgba(0,0,0,0.08); padding: 28px; margin-bottom: 18px; }
.section-title { font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 400; color: #0a0a0a; margin-bottom: 14px; }
.section-content p { font-size: 14px; color: rgba(0,0,0,0.75); }
.section-content ul { padding-left: 18px; }
.section-content li { font-size: 14px; color: rgba(0,0,0,0.78); margin-bottom: 8px; }
@media (max-width: 768px) {
  .shipping-page { padding: 80px 20px 50px; }
  .shipping-title { font-size: 34px; }
  .section-title { font-size: 20px; }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
