<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/inc/config/index.php';
require_once dirname(__DIR__) . '/inc/seo/index.php';

$system  = xds_get_system();
$contact = xds_fetch_single_table('page_contact', 'page_contact');
$needs   = xds_decode_json($contact['inquiry_needs_json'] ?? null);

$activeNav     = 'contact';
$loadFlatpickr = true;
$pageMeta      = [
    'title'          => $contact['meta_title'],
    'description'    => $contact['meta_description'],
    'canonical_path' => 'contact/',
    'og_image_file'  => $contact['og_image_file'] ?? 'hero-home.webp',
    'schemas'        => [
        xds_seo_organization_schema($system),
        [
            '@context' => 'https://schema.org',
            '@type'    => 'ContactPage',
            'name'     => $contact['meta_title'],
            'url'      => xds_canonical('contact/'),
        ],
    ],
];

require dirname(__DIR__) . '/inc/layout/header.php';
?>

<section class="page-banner" aria-label="Contact XDS Zimbabwe">
  <img src="<?= e(xds_image_asset($contact['og_image_file'] ?? 'hero-home.webp')) ?>" alt="Contact Expert Decision Systems Zimbabwe" class="page-banner-img">
  <div class="page-banner-overlay" aria-hidden="true"></div>
  <div class="container page-banner-content">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(xds_url('')) ?>">Home</a>
      <span>/</span>
      <span aria-current="page"><?= e($contact['page_title']) ?></span>
    </nav>
    <h1><?= e($contact['page_title']) ?></h1>
    <p class="page-banner-lead"><?= e($contact['intro_copy']) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="contact-grid">
      <div class="glass-card xds-scroll-card contact-form-card">
        <h2 style="font-size:1.45rem;margin-bottom:6px;"><?= e($contact['heading']) ?></h2>
        <p style="font-size:0.93rem;margin-bottom:22px;">Complete the form below to send us a message or request a weekday callback during operating hours.</p>

        <form id="contact-form" data-ajax-contact-form novalidate>
          <input type="hidden" name="csrf_token" value="<?= e(xds_csrf_token()) ?>">
          <input type="hidden" name="source_page" value="contact">
          <div style="position:absolute;left:-9999px;" aria-hidden="true">
            <label for="website_url">Website</label>
            <input id="website_url" name="website_url" type="text" tabindex="-1" autocomplete="off">
          </div>

          <div class="form-alert" data-form-alert role="status" aria-live="polite"></div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="c-firstname">First Name <span class="required-star">*</span></label>
              <input id="c-firstname" name="firstname" type="text" class="form-control" required placeholder="Please enter your firstname">
              <span class="field-error" data-error-for="firstname"></span>
            </div>
            <div class="form-group">
              <label for="c-lastname">Last Name <span class="required-star">*</span></label>
              <input id="c-lastname" name="lastname" type="text" class="form-control" required placeholder="Please enter your lastname">
              <span class="field-error" data-error-for="lastname"></span>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="c-email">Email <span class="required-star">*</span></label>
              <input id="c-email" name="email" type="email" class="form-control" required placeholder="Please enter your email">
              <span class="field-error" data-error-for="email"></span>
            </div>
            <div class="form-group">
              <label for="c-phone">Phone Number</label>
              <input id="c-phone" name="phone" type="tel" class="form-control" placeholder="+263 242 ... / +263 7...">
              <span class="field-error" data-error-for="phone"></span>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="c-need">Please specify your need <span class="required-star">*</span></label>
              <select id="c-need" name="need_category" class="form-control" required>
                <option value="">Select an enquiry topic...</option>
                <?php foreach ($needs as $need): ?>
                <option value="<?= e((string)$need) ?>"><?= e((string)$need) ?></option>
                <?php endforeach; ?>
              </select>
              <span class="field-error" data-error-for="need_category"></span>
            </div>
            <div class="form-group">
              <label for="c-company">Organisation (Optional)</label>
              <input id="c-company" name="company" type="text" class="form-control" placeholder="Company or Institution">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label for="c-date">Preferred Callback Date <span style="font-weight:400;color:var(--text-muted);">(Optional, Mon–Fri)</span></label>
              <input id="c-date" name="preferred_date" type="text" class="form-control" data-flatpickr-date placeholder="Pick a weekday...">
              <span class="field-error" data-error-for="preferred_date"></span>
            </div>
            <div class="form-group">
              <label for="c-time">Preferred Time <span style="font-weight:400;color:var(--text-muted);">(08:00 – 16:30)</span></label>
              <input id="c-time" name="preferred_time" type="text" class="form-control" data-flatpickr-time placeholder="Select time slot...">
              <span class="field-error" data-error-for="preferred_time"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="c-message">Message <span class="required-star">*</span></label>
            <textarea id="c-message" name="message" class="form-control" required placeholder="Tell us how we can assist you..."></textarea>
            <span class="field-error" data-error-for="message"></span>
          </div>

          <div class="contact-form-footer">
            <span class="contact-required-note"><span class="required-star">*</span> These fields are required.</span>
            <button type="submit" class="btn btn-primary contact-submit-btn">
              <i data-lucide="send"></i>
              <span>Send Message</span>
            </button>
          </div>
        </form>
      </div>

      <div style="display:flex;flex-direction:column;gap:22px;">
        <aside class="glass-card xds-scroll-card offering-card">
          <h2 style="font-size:1.3rem;margin-bottom:18px;"><?= e($contact['office_title']) ?></h2>
          <div class="info-list">
            <div class="info-row">
              <span class="info-icon"><i data-lucide="map-pin"></i></span>
              <div>
                <strong style="display:block;color:var(--heading-color);font-size:0.92rem;">Harare Head Office</strong>
                <span style="font-size:0.92rem;color:var(--text-secondary);">
                  <?= e($contact['address_line1']) ?><br>
                  <?= e($contact['address_line2']) ?><br>
                  <?= e($contact['city_country']) ?>
                </span>
              </div>
            </div>

            <div class="info-row">
              <span class="info-icon"><i data-lucide="phone-call"></i></span>
              <div>
                <strong style="display:block;color:var(--heading-color);font-size:0.92rem;">Telephone</strong>
                <div style="font-size:0.92rem;color:var(--text-secondary);display:flex;flex-direction:column;gap:2px;">
                  <a href="tel:+263242779344"><?= e($contact['phone_1']) ?></a>
                  <a href="tel:+263242779341"><?= e($contact['phone_2']) ?></a>
                  <a href="tel:+263242779392"><?= e($contact['phone_3']) ?></a>
                </div>
              </div>
            </div>

            <div class="info-row">
              <span class="info-icon"><i data-lucide="mail"></i></span>
              <div>
                <strong style="display:block;color:var(--heading-color);font-size:0.92rem;">Email</strong>
                <a href="mailto:<?= e($contact['email']) ?>" style="font-size:0.94rem;color:var(--xds-lime-deep);font-weight:600;">
                  <?= e($contact['email']) ?>
                </a>
              </div>
            </div>

            <div class="info-row">
              <span class="info-icon"><i data-lucide="clock"></i></span>
              <div>
                <strong style="display:block;color:var(--heading-color);font-size:0.92rem;">Operating Hours</strong>
                <span style="font-size:0.92rem;color:var(--text-secondary);">
                  <?= e($contact['hours_weekday']) ?><br>
                  <?= e($contact['hours_weekend']) ?>
                </span>
              </div>
            </div>
          </div>
        </aside>

        <div class="glass-card xds-scroll-card contact-portal-card">
          <h3 style="font-size:1.08rem;margin-bottom:8px;">Existing Subscriber?</h3>
          <p style="font-size:0.9rem;margin-bottom:16px;">Access your XDS Credit Bureau or E Check Authentication portal directly.</p>
          <div class="contact-portal-actions">
            <a href="<?= e(xds_portal_href((string)$system['credit_portal_url'])) ?>"<?= xds_is_external_url((string)$system['credit_portal_url']) ? ' target="_blank" rel="noopener noreferrer"' : '' ?> class="btn btn-glass btn-sm">
              <i data-lucide="log-in"></i>
              <span>Credit Portal</span>
            </a>
            <a href="<?= e(xds_portal_href((string)$system['auth_portal_url'])) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-glass btn-sm">
              <i data-lucide="external-link"></i>
              <span>E Check Portal</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="glass-card xds-scroll-card map-frame-wrap" aria-label="XDS Zimbabwe Harare Office Map">
      <iframe
        title="Expert Decision Systems Zimbabwe — 8th Floor Pegasus House, Samora Machel, Harare"
        src="<?= e($contact['map_embed_url']) ?>"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        allowfullscreen
      ></iframe>
    </div>
  </div>
</section>

<?php require dirname(__DIR__) . '/inc/layout/footer.php'; ?>
