<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/inc/config/index.php';
require_once dirname(__DIR__, 2) . '/inc/seo/index.php';

$system    = xds_get_system();
$page      = xds_fetch_single_table('page_credit_services', 'page_credit_services');
$offerings = xds_decode_json($page['offerings_json'] ?? null);
$partners  = xds_decode_json($page['partners_json'] ?? null);

$activeNav     = 'products';
$activeSub     = 'credit';
$loadFlatpickr = true;
$pageMeta      = [
    'title'          => $page['meta_title'],
    'description'    => $page['meta_description'],
    'canonical_path' => 'products/credit-information/',
    'og_image_file'  => $page['og_image_file'] ?? $page['banner_image_file'],
    'schemas'        => [
        xds_seo_organization_schema($system),
        [
            '@context'    => 'https://schema.org',
            '@type'       => 'Service',
            'serviceType' => 'Credit Information Bureau Services',
            'name'        => $page['page_title'],
            'description' => $page['overview_copy'],
            'provider'    => ['@type' => 'Organization', 'name' => $system['site_name']],
            'areaServed'  => 'Zimbabwe',
        ],
    ],
];

require dirname(__DIR__, 2) . '/inc/layout/header.php';
?>

<section class="page-banner" aria-label="Credit Information Services">
  <img src="<?= e(xds_image_asset($page['banner_image_file'])) ?>" alt="XDS Credit Information Services analytics" class="page-banner-img">
  <div class="page-banner-overlay" aria-hidden="true"></div>
  <div class="container page-banner-content">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(xds_url('')) ?>">Home</a>
      <span>/</span>
      <span>Products</span>
      <span>/</span>
      <span aria-current="page">Credit Information Services</span>
    </nav>
    <h1><?= e($page['page_title']) ?></h1>
    <p class="page-banner-lead">24/7 online access to 2M+ consumer and corporate credit records supporting underwriting, commercial due diligence, and collections tracing.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="glass-card xds-scroll-card" style="padding:32px;margin-bottom:38px;">
      <div class="section-header" style="margin-bottom:14px;">
        <p class="section-eyebrow">Full Credit Lifecycle</p>
        <h2 class="section-title" style="font-size:clamp(1.5rem, 2.6vw, 2rem);">Comprehensive Consumer &amp; Commercial Intelligence</h2>
        <div class="section-accent-rule"></div>
      </div>
      <p style="font-size:1.03rem;margin-bottom:0;"><?= e($page['overview_copy']) ?></p>
    </div>

    <div class="cards-grid-3" style="margin-bottom:40px;">
      <?php foreach ($offerings as $item): ?>
      <article class="glass-card glass-card-hover xds-scroll-card offering-card">
        <div class="service-icon-orb" style="width:60px;height:60px;border-radius:16px;margin-bottom:18px;">
          <i data-lucide="<?= e($item['icon'] ?? 'file-text') ?>" style="width:28px;height:28px;"></i>
        </div>
        <h3><?= e($item['title'] ?? '') ?></h3>
        <?php if (!empty($item['subtitle'])): ?>
        <p class="offering-subtitle"><?= e($item['subtitle']) ?></p>
        <?php endif; ?>
        <ul class="xds-check-list">
          <?php foreach (($item['items'] ?? []) as $bullet): ?>
          <li>
            <i data-lucide="check-circle-2"></i>
            <span><?= e((string)$bullet) ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </article>
      <?php endforeach; ?>
    </div>

    <div id="request-report" class="glass-card xds-scroll-card" style="padding:28px 30px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px;margin-bottom:54px;">
      <div>
        <h3 style="margin-bottom:6px;">Start Using XDS Credit Profiles Today</h3>
        <p style="margin-bottom:0;font-size:0.95rem;">Onboard your institution as a bureau subscriber or request your personal credit report online.</p>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:12px;">
        <button type="button" class="btn btn-primary" data-modal-open="credit-signup-modal">
          <i data-lucide="user-plus"></i>
          <span><?= e($page['signup_cta_label']) ?></span>
        </button>
        <button type="button" class="btn btn-lime" data-modal-open="request-report-modal">
          <i data-lucide="file-search"></i>
          <span><?= e($page['report_cta_label']) ?></span>
        </button>
      </div>
    </div>

    <div class="section-header centered">
      <p class="section-eyebrow">Trusted Ecosystem</p>
      <h2 class="section-title"><?= e($page['partners_heading']) ?></h2>
      <div class="section-accent-rule"></div>
    </div>

    <div class="partners-grid" style="max-width:640px;margin:0 auto;">
      <?php foreach ($partners as $partner): ?>
      <div class="glass-card glass-card-hover xds-scroll-card partner-tile">
        <div class="partner-logo-frame">
          <img
            src="<?= e(xds_image_asset((string)($partner['logo_file'] ?? ''))) ?>"
            alt="<?= e($partner['name'] ?? '') ?>"
            loading="lazy"
          >
        </div>
        <span class="partner-name"><?= e($partner['name'] ?? '') ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div id="credit-signup-modal" class="xds-modal" role="dialog" aria-modal="true" aria-labelledby="signup-modal-title" aria-hidden="true">
  <div class="xds-modal-dialog">
    <div class="xds-modal-header">
      <div>
        <h3 id="signup-modal-title" style="margin-bottom:4px;">Bureau Subscriber Sign Up</h3>
        <p style="margin-bottom:0;font-size:0.88rem;">Register your institution for XDS Credit Information Services.</p>
      </div>
      <button type="button" class="icon-control-btn" data-modal-close aria-label="Close modal">
        <i data-lucide="x"></i>
      </button>
    </div>
    <form data-ajax-contact-form novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(xds_csrf_token()) ?>">
      <input type="hidden" name="source_page" value="credit-signup-modal">
      <input type="hidden" name="need_category" value="Credit Information Services / Bureau Subscription">
      <div class="form-alert" data-form-alert role="status" aria-live="polite"></div>
      <div class="form-grid-2">
        <div class="form-group">
          <label for="su-fname">First Name <span class="required-star">*</span></label>
          <input id="su-fname" name="firstname" type="text" class="form-control" required placeholder="First name">
          <span class="field-error" data-error-for="firstname"></span>
        </div>
        <div class="form-group">
          <label for="su-lname">Last Name <span class="required-star">*</span></label>
          <input id="su-lname" name="lastname" type="text" class="form-control" required placeholder="Last name">
          <span class="field-error" data-error-for="lastname"></span>
        </div>
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label for="su-email">Work Email <span class="required-star">*</span></label>
          <input id="su-email" name="email" type="email" class="form-control" required placeholder="name@institution.co.zw">
          <span class="field-error" data-error-for="email"></span>
        </div>
        <div class="form-group">
          <label for="su-company">Institution / Company</label>
          <input id="su-company" name="company" type="text" class="form-control" placeholder="Bank, MFI, or Enterprise">
        </div>
      </div>
      <div class="form-group">
        <label for="su-date">Preferred Onboarding Call Date (Mon–Fri)</label>
        <input id="su-date" name="preferred_date" type="text" class="form-control" data-flatpickr-date placeholder="Select a weekday">
        <span class="field-error" data-error-for="preferred_date"></span>
      </div>
      <div class="form-group">
        <label for="su-msg">Requirements <span class="required-star">*</span></label>
        <textarea id="su-msg" name="message" class="form-control" required placeholder="Tell us about your lending volume or bureau integration needs..."></textarea>
        <span class="field-error" data-error-for="message"></span>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;">
        <i data-lucide="send"></i>
        <span>Submit Subscriber Request</span>
      </button>
    </form>
  </div>
</div>

<div id="request-report-modal" class="xds-modal" role="dialog" aria-modal="true" aria-labelledby="report-modal-title" aria-hidden="true">
  <div class="xds-modal-dialog">
    <div class="xds-modal-header">
      <div>
        <h3 id="report-modal-title" style="margin-bottom:4px;">Request My Credit Report</h3>
        <p style="margin-bottom:0;font-size:0.88rem;">Submit your details and our Harare consumer desk will guide your verification.</p>
      </div>
      <button type="button" class="icon-control-btn" data-modal-close aria-label="Close modal">
        <i data-lucide="x"></i>
      </button>
    </div>
    <form data-ajax-contact-form novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(xds_csrf_token()) ?>">
      <input type="hidden" name="source_page" value="request-report-modal">
      <input type="hidden" name="need_category" value="Request My Personal Credit Report">
      <div class="form-alert" data-form-alert role="status" aria-live="polite"></div>
      <div class="form-grid-2">
        <div class="form-group">
          <label for="rr-fname">First Name <span class="required-star">*</span></label>
          <input id="rr-fname" name="firstname" type="text" class="form-control" required placeholder="First name">
          <span class="field-error" data-error-for="firstname"></span>
        </div>
        <div class="form-group">
          <label for="rr-lname">Last Name <span class="required-star">*</span></label>
          <input id="rr-lname" name="lastname" type="text" class="form-control" required placeholder="Last name">
          <span class="field-error" data-error-for="lastname"></span>
        </div>
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label for="rr-email">Email Address <span class="required-star">*</span></label>
          <input id="rr-email" name="email" type="email" class="form-control" required placeholder="you@example.com">
          <span class="field-error" data-error-for="email"></span>
        </div>
        <div class="form-group">
          <label for="rr-phone">Phone Number</label>
          <input id="rr-phone" name="phone" type="tel" class="form-control" placeholder="+263 7...">
        </div>
      </div>
      <div class="form-group">
        <label for="rr-msg">Request Details <span class="required-star">*</span></label>
        <textarea id="rr-msg" name="message" class="form-control" required placeholder="Specify whether this is an annual consumer credit report request or a profile update enquiry..."></textarea>
        <span class="field-error" data-error-for="message"></span>
      </div>
      <button type="submit" class="btn btn-lime" style="width:100%;">
        <i data-lucide="file-search"></i>
        <span>Submit Report Request</span>
      </button>
    </form>
  </div>
</div>

<?php require dirname(__DIR__, 2) . '/inc/layout/footer.php'; ?>
