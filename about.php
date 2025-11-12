<?php
require_once __DIR__ . '/includes/config.php';
$pageCss = '';
include __DIR__ . '/includes/header.php';
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@500;600;700&display=swap');
  .about-wrapper { max-width: 960px; margin: 70px auto 90px; padding: 0 20px; }
  .about-title { font-family: 'Playfair Display', serif; font-size: 46px; font-weight: 600; text-align: center; letter-spacing: 0.2px; margin: 0 0 10px; color: #121212; }
  .about-subtitle { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; text-align: center; letter-spacing: 4px; font-size: 12px; color: #7a1530; text-transform: uppercase; margin-bottom: 18px; }
  .about-divider { width: 56px; height: 2px; background: #7a1530; margin: 0 auto 32px; opacity: 0.85; }
  .about-content { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; max-width: 820px; margin: 0 auto; color: #2b2b2b; font-size: 17px; line-height: 1.9; text-align: justify; }
  .about-content p { margin: 0 0 18px; }
  .about-tagline { 
    font-family: 'Playfair Display', serif; 
    text-align: center; 
    font-size: 28px; 
    font-weight: 600; 
    color: #7a1530; 
    margin: 48px 0 0; 
    letter-spacing: 0.5px;
    font-style: italic;
    position: relative;
    padding: 32px 0;
  }
  .about-tagline::before,
  .about-tagline::after {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 1px;
    background: linear-gradient(90deg, transparent, #7a1530, transparent);
  }
  .about-tagline::before {
    top: 0;
  }
  .about-tagline::after {
    bottom: 0;
  }
  @media (min-width: 1024px) { 
    .about-title { font-size: 54px; }
    .about-tagline { font-size: 32px; }
  }
</style>

<main class="about-wrapper">
  <h1 class="about-title">About GlamEssentials</h1>
  <div class="about-subtitle">Elevating Professional Beauty Standards</div>
  <div class="about-divider"></div>
  <div class="about-content">
    <p>GlamEssentials was founded with a singular vision: to provide salon professionals and beauty enthusiasts with premium-quality products that deliver exceptional results. We understand that in the world of beauty, excellence isn't optional—it's essential.</p>
    <br>

    <p><strong><p style="text-align:center;">OUR MISSION</p></strong>
    We curate only the finest salon essentials for the modern professional who demands both quality and performance. Every product in our collection is carefully selected to meet the highest industry standards, ensuring that you have access to tools and products that elevate your craft.</p>
    <br>

    <p><strong><p style="text-align:center;">WHY CHOOSE GlamEssentials?</p></strong>
    We don't just sell products—we curate experiences. Each item is carefully selected and vetted for its quality, effectiveness, and professional-grade performance. Whether you're a salon owner, an independent stylist, or a passionate beauty enthusiast, our products are crafted to meet professional standards and deliver exceptional results. Staying ahead of industry trends, we offer modern solutions that blend timeless elegance with cutting-edge technology, ensuring you always have access to the best the beauty industry has to offer.</p>
    <br>

    <p><strong><p style="text-align:center;">OUR COMMITMENT</p></strong>
    At GlamEssentials, we believe that great beauty work starts with great products. We're committed to being your trusted partner in delivering exceptional results to every client, every time.</p>
    <br>
    <br>
    <p class="about-tagline">Discover the difference that true quality makes.</p>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>