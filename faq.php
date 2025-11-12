<?php
require_once __DIR__ . '/includes/config.php';
$pageCss = '';
include __DIR__ . '/includes/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;500&display=swap" rel="stylesheet">

<main class="faq-page">
  <div class="faq-container">
    <!-- Page Header -->
    <div class="faq-header">
      <h1 class="faq-title">Frequently Asked Questions</h1>
      <p class="faq-subtitle">Everything you need to know about shopping with GlamEssentials</p>
    </div>

    <!-- FAQ Sections -->
    <div class="faq-sections">
      <!-- Ordering & Payment -->
      <section class="faq-section">
        <div class="section-header">
          <h2 class="section-title">| Ordering & Payment</h2>
        </div>
        <div class="faq-grid">
          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">What payment methods do you accept?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>We accept all major credit cards (Visa, Mastercard, American Express), PayPal, and other secure payment methods at checkout.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">Can I modify or cancel my order?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Orders can be modified or cancelled within 24 hours of placement. Please contact our customer service team immediately at <a href="mailto:support@glamessentials.com">support@glamessentials.com</a> for assistance.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">How do I place an order?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Simply browse our products, add items to your cart, and proceed to checkout. You'll need to create an account or sign in to complete your purchase.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">Do you offer bulk or wholesale pricing?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Yes! We offer special pricing for salon professionals and bulk orders. Please contact us at <a href="mailto:sales@glamessentials.com">sales@glamessentials.com</a> for more information.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Shipping & Delivery -->
      <section class="faq-section">
        <div class="section-header">
          <h2 class="section-title">| Shipping & Delivery</h2>
        </div>
        <div class="faq-grid">
          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">How long does shipping take?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Standard shipping typically takes 5–7 business days. Express shipping options are available at checkout for faster delivery.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">How can I track my order?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Once your order ships, you'll receive a tracking number via email. You can also track your order by logging into your account.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">Do you ship internationally?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Currently, we ship within selected regions. International shipping options may be available—please contact us for details.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">What if my order arrives damaged?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>We take great care in packaging, but if your order arrives damaged, please contact us within 48 hours with photos, and we'll send a replacement immediately.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Products & Quality -->
      <section class="faq-section">
        <div class="section-header">
          <h2 class="section-title">|  Products & Quality</h2>
        </div>
        <div class="faq-grid">
          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">Are your products authentic?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Absolutely. We only source authentic, professional-grade products directly from authorized distributors and manufacturers.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">Are your products suitable for professional salon use?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Yes! All our products are curated specifically for professional use and meet industry standards.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">Do you offer product samples?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Sample availability varies by product. Please check individual product pages or contact us for specific requests.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">What if I'm not satisfied with a product?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Your satisfaction is our priority. Please see our Returns & Exchanges policy for next steps.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Returns & Exchanges -->
      <section class="faq-section">
        <div class="section-header">
          <h2 class="section-title">| Returns & Exchanges</h2>
        </div>
        <div class="faq-grid">
          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">What is your return policy?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>We accept returns within 30 days of purchase for unopened products in original packaging. Opened products may be subject to restocking fees.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">How do I initiate a return?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Contact our customer service team at <a href="mailto:support@glamessentials.com">support@glamessentials.com</a> with your order number. We'll provide you with return instructions and a return authorization number.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">When will I receive my refund?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Refunds are processed within 5–7 business days after we receive your return. Please allow additional time for your bank to process the refund.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">Do you offer exchanges?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Yes! If you'd like to exchange a product for a different item or size, please contact our customer service team.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Account & Privacy -->
      <section class="faq-section">
        <div class="section-header">
          <h2 class="section-title">| Account & Privacy</h2>
        </div>
        <div class="faq-grid">
          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">Do I need an account to shop?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>While you can browse as a guest, creating an account allows you to track orders, save favorites, and enjoy exclusive member benefits.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">Is my personal information secure?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Yes. We use industry-standard encryption and security measures to protect your personal and payment information.</p>
            </div>
          </div>

          <div class="faq-item">
            <button class="faq-question" type="button">
              <span class="question-text">How do I sign up for exclusive deals?</span>
              <span class="faq-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </span>
            </button>
            <div class="faq-answer">
              <p>Click "SIGN UP NOW TO START SHOPPING" in our header banner or subscribe to our newsletter at the bottom of any page.</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</main>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Montserrat', sans-serif;
  background: #ffffff;
  color: #1a1a1a;
  line-height: 1.6;
}

.faq-page {
  min-height: 100vh;
  padding: 100px 30px 60px;
  background: linear-gradient(to bottom, #fafafa 0%, #ffffff 100%);
}

.faq-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* Page Header */
.faq-header {
  text-align: center;
  margin-bottom: 60px;
}

.faq-title {
  font-family: 'Playfair Display', serif;
  font-size: 48px;
  font-weight: 400;
  color: #0a0a0a;
  margin-bottom: 15px;
}

.faq-subtitle {
  font-size: 14px;
  color: rgba(0,0,0,0.5);
  letter-spacing: 0.3px;
}

/* FAQ Sections */
.faq-sections {
  display: flex;
  flex-direction: column;
  gap: 40px;
}

.faq-section {
  background: #ffffff;
  border: 1px solid rgba(0,0,0,0.08);
  padding: 40px;
  transition: all 0.3s ease;
}

.faq-section:hover {
  border-color: rgba(0,0,0,0.12);
  box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.section-header {
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 2px solid rgba(0,0,0,0.08);
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 28px;
  font-weight: 400;
  color: #0a0a0a;
  letter-spacing: 0.5px;
}

/* FAQ Grid */
.faq-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 15px;
}

@media (min-width: 768px) {
  .faq-grid {
    grid-template-columns: 1fr 1fr;
  }
}

/* FAQ Items */
.faq-item {
  border: 1px solid rgba(0,0,0,0.08);
  background: #fafafa;
  overflow: hidden;
  transition: all 0.3s ease;
}

.faq-item:hover {
  border-color: rgba(0,0,0,0.15);
  background: #ffffff;
}

.faq-item.open {
  border-color: #0a0a0a;
  background: #ffffff;
}

.faq-question {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 15px;
  padding: 20px;
  background: none;
  border: none;
  cursor: pointer;
  text-align: left;
  font-family: 'Montserrat', sans-serif;
  transition: all 0.3s ease;
}

.faq-question:hover {
  background: rgba(0,0,0,0.02);
}

.question-text {
  font-size: 14px;
  font-weight: 500;
  color: #0a0a0a;
  letter-spacing: 0.3px;
  flex: 1;
}

.faq-icon {
  flex-shrink: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(0,0,0,0.05);
  transition: all 0.3s ease;
}

.faq-item.open .faq-icon {
  background: #0a0a0a;
  transform: rotate(45deg);
}

.faq-item.open .faq-icon svg {
  stroke: #ffffff;
}

.faq-answer {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease, padding 0.3s ease;
}

.faq-item.open .faq-answer {
  max-height: 500px;
  padding: 0 20px 20px 20px;
}

.faq-answer p {
  font-size: 13px;
  color: rgba(0,0,0,0.7);
  line-height: 1.8;
  letter-spacing: 0.3px;
  margin: 0;
}

.faq-answer a {
  color: #0a0a0a;
  text-decoration: none;
  font-weight: 500;
  border-bottom: 1px solid rgba(0,0,0,0.2);
  transition: all 0.3s ease;
}

.faq-answer a:hover {
  border-bottom-color: #0a0a0a;
}

/* Responsive Design */
@media (max-width: 768px) {
  .faq-page {
    padding: 80px 20px 50px;
  }

  .faq-header {
    margin-bottom: 40px;
  }

  .faq-title {
    font-size: 36px;
  }

  .faq-subtitle {
    font-size: 13px;
  }

  .faq-section {
    padding: 30px 20px;
  }

  .section-title {
    font-size: 24px;
  }

  .faq-grid {
    grid-template-columns: 1fr;
  }

  .faq-question {
    padding: 16px;
  }

  .question-text {
    font-size: 13px;
  }

  .faq-item.open .faq-answer {
    padding: 0 16px 16px 16px;
  }

  .faq-answer p {
    font-size: 12px;
  }
}

@media (max-width: 480px) {
  .faq-title {
    font-size: 28px;
  }

  .section-title {
    font-size: 20px;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const questions = document.querySelectorAll('.faq-question');
  
  questions.forEach(function(question) {
    question.addEventListener('click', function() {
      const item = this.closest('.faq-item');
      const wasOpen = item.classList.contains('open');
      
      // Close all other items in the same section (optional)
      // const section = item.closest('.faq-section');
      // section.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      
      // Toggle current item
      if (wasOpen) {
        item.classList.remove('open');
      } else {
        item.classList.add('open');
      }
    });
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>