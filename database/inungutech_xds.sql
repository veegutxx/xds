-- ============================================================================
-- Expert Decision Systems Zimbabwe (XDS)
-- Per-Page CMS-Ready MySQL Database Schema & Seed Data
-- Database : inungutech_xds
-- DB User  : inungutech_admin
-- Asset Rule: Store ONLY filenames / relative slugs (never full paths or URLs)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `inungutech_xds`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Optional privilege grant (uncomment as root if user does not yet exist):
-- CREATE USER IF NOT EXISTS 'inungutech_admin'@'localhost' IDENTIFIED BY 'CHANGE_ME_STRONG_PASSWORD';
-- GRANT ALL PRIVILEGES ON `inungutech_xds`.* TO 'inungutech_admin'@'localhost';
-- FLUSH PRIVILEGES;

USE `inungutech_xds`;

SET NAMES utf8mb4;
SET time_zone = '+02:00';

-- ----------------------------------------------------------------------------
-- 1. Global System & Brand Configuration (`site_system`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_system` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  `site_name` VARCHAR(160) NOT NULL,
  `site_tagline` VARCHAR(255) NOT NULL,
  `logo_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/system/',
  `favicon_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/system/',
  `apple_touch_icon_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/system/',
  `primary_email` VARCHAR(120) NOT NULL,
  `primary_phone` VARCHAR(60) NOT NULL,
  `secondary_phone` VARCHAR(60) NOT NULL,
  `tertiary_phone` VARCHAR(60) DEFAULT NULL,
  `address_line` VARCHAR(255) NOT NULL,
  `operating_hours_weekday` VARCHAR(120) NOT NULL,
  `operating_hours_weekend` VARCHAR(120) NOT NULL,
  `credit_portal_url` VARCHAR(255) NOT NULL,
  `auth_portal_url` VARCHAR(255) NOT NULL,
  `social_links_json` JSON NOT NULL,
  `regional_links_json` JSON NOT NULL,
  `copyright_text` VARCHAR(160) NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_system` (
  `id`, `site_name`, `site_tagline`,
  `logo_file`, `favicon_file`, `apple_touch_icon_file`,
  `primary_email`, `primary_phone`, `secondary_phone`, `tertiary_phone`,
  `address_line`, `operating_hours_weekday`, `operating_hours_weekend`,
  `credit_portal_url`, `auth_portal_url`,
  `social_links_json`, `regional_links_json`, `copyright_text`
) VALUES (
  1,
  'Expert Decision Systems Zimbabwe (XDS)',
  'Risk Management, Credit Bureau & Background Screening Solutions',
  'logo.png',
  'favicon.png',
  'apple-touch-icon.png',
  'info@xds.co.zw',
  '+263 242 779 344',
  '+263 242 779 341',
  '+263 242 779 392',
  '8th Floor Pegasus House, Samora Machel, Harare, Zimbabwe',
  'Monday - Friday: 0800hrs - 1630hrs',
  'Saturday - Sunday: Closed',
  'portal/',
  'https://www.xds.co.zw/echeck/',
  JSON_OBJECT(
    'linkedin', 'https://www.linkedin.com/company/xds-zimbabwe',
    'twitter', 'https://twitter.com/xdszimbabwe',
    'facebook', 'https://www.facebook.com/xdszimbabwe'
  ),
  JSON_ARRAY(
    JSON_OBJECT('label', 'XDS Zimbabwe', 'url', 'https://www.xds.co.zw/', 'country', 'ZW'),
    JSON_OBJECT('label', 'XDS South Africa', 'url', 'https://www.xds.co.za/', 'country', 'ZA'),
    JSON_OBJECT('label', 'XDS Nigeria', 'url', 'https://www.xdscreditbureau.com/', 'country', 'NG'),
    JSON_OBJECT('label', 'XDS Ghana', 'url', 'https://www.xdsdata.com/', 'country', 'GH')
  ),
  '© 2026 XDS Zimbabwe. All rights reserved.'
) ON DUPLICATE KEY UPDATE
  `site_name` = VALUES(`site_name`),
  `logo_file` = VALUES(`logo_file`),
  `credit_portal_url` = VALUES(`credit_portal_url`),
  `auth_portal_url` = VALUES(`auth_portal_url`),
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 2. Home Page (`page_home`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_home` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  `hero_badge` VARCHAR(100) NOT NULL,
  `hero_headline` VARCHAR(180) NOT NULL,
  `hero_subheadline` TEXT NOT NULL,
  `hero_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `hero_primary_cta_label` VARCHAR(80) NOT NULL,
  `hero_primary_cta_href` VARCHAR(120) NOT NULL,
  `hero_secondary_cta_label` VARCHAR(80) NOT NULL,
  `hero_secondary_cta_href` VARCHAR(120) NOT NULL,
  `hero_slides_json` JSON NOT NULL,
  `intro_title` VARCHAR(160) NOT NULL,
  `intro_subtitle` VARCHAR(120) NOT NULL,
  `intro_body` TEXT NOT NULL,
  `metrics_json` JSON NOT NULL,
  `services_heading` VARCHAR(140) NOT NULL,
  `services_subheading` VARCHAR(180) NOT NULL,
  `services_json` JSON NOT NULL,
  `cta_banner_title` VARCHAR(160) NOT NULL,
  `cta_banner_copy` TEXT NOT NULL,
  `cta_banner_button` VARCHAR(80) NOT NULL,
  `meta_title` VARCHAR(160) NOT NULL,
  `meta_description` VARCHAR(320) NOT NULL,
  `og_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Safe upgrade if `page_home` already existed before `hero_slides_json` was added
SET @xds_col_exists := (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'page_home'
    AND COLUMN_NAME = 'hero_slides_json'
);
SET @xds_alter_sql := IF(
  @xds_col_exists = 0,
  'ALTER TABLE `page_home` ADD COLUMN `hero_slides_json` JSON NULL AFTER `hero_secondary_cta_href`',
  'SELECT 1'
);
PREPARE xds_stmt FROM @xds_alter_sql;
EXECUTE xds_stmt;
DEALLOCATE PREPARE xds_stmt;

INSERT INTO `page_home` (
  `id`, `hero_badge`, `hero_headline`, `hero_subheadline`, `hero_image_file`,
  `hero_primary_cta_label`, `hero_primary_cta_href`,
  `hero_secondary_cta_label`, `hero_secondary_cta_href`,
  `hero_slides_json`,
  `intro_title`, `intro_subtitle`, `intro_body`,
  `metrics_json`, `services_heading`, `services_subheading`, `services_json`,
  `cta_banner_title`, `cta_banner_copy`, `cta_banner_button`,
  `meta_title`, `meta_description`, `og_image_file`
) VALUES (
  1,
  'Zimbabwe Credit Bureau & Risk Intelligence',
  'Turning Credit & Verification Data Into Confident Decisions',
  'Comprehensive consumer and commercial credit reporting, agricultural risk analytics, and E Check background screening tailored for Zimbabwean institutions.',
  'hero-home.webp',
  'Explore Our Solutions',
  '#our-services',
  'Schedule a Consultation',
  '/contact/',
  JSON_ARRAY(
    JSON_OBJECT(
      'eyebrow', 'Credit Bureau Intelligence',
      'headline', 'Make Faster, Smarter Lending Decisions Across Zimbabwe',
      'insight', 'Draw on over 2 million consumer and corporate credit records contributed by 125+ Zimbabwean credit providers to assess repayment capacity, curb defaults, and streamline loan approvals.',
      'primary_label', 'Explore Credit Services',
      'primary_href', '/products/credit-information/',
      'secondary_label', 'Request a Demo',
      'secondary_href', '/contact/'
    ),
    JSON_OBJECT(
      'eyebrow', 'Commercial & Collections Tracing',
      'headline', 'Protect Trade Credit & Recover Hard-to-Find Debtors',
      'insight', 'Verify company registrations, director KYC, compliance standing, and up-to-date demographic contact listings to safeguard B2B credit books across every sector in Zimbabwe.',
      'primary_label', 'View Commercial Reports',
      'primary_href', '/products/credit-information/',
      'secondary_label', 'Speak With Advisory',
      'secondary_href', '/contact/'
    ),
    JSON_OBJECT(
      'eyebrow', 'Agricultural Risk Analytics',
      'headline', 'De-Risk Seasonal Agro-Finance & Contract Farming',
      'insight', 'Combine farmer repayment history with crop productivity benchmarks, credit exposure reports, double-dip alerts, and side-marketing alerts built for Zimbabwean agro-lenders.',
      'primary_label', 'Explore Agro Services',
      'primary_href', '/products/agricultural-services/',
      'secondary_label', 'Book Agro Briefing',
      'secondary_href', '/contact/'
    ),
    JSON_OBJECT(
      'eyebrow', 'E Check Background Screening',
      'headline', 'Verify Talent & Counterparties Before You Onboard',
      'insight', 'Authenticate ZIMSEC, Cambridge, and tertiary qualifications, Zimbabwean drivers licences, police clearances, and personal credit standing through one trusted local platform.',
      'primary_label', 'Explore E Check',
      'primary_href', '/products/authentication-services/',
      'secondary_label', 'Start Screening',
      'secondary_href', '/contact/'
    )
  ),
  'Expert Decision Systems',
  'Zimbabwe',
  'Expert Decision Systems Zimbabwe (XDS) delivers actionable risk management intelligence to institutions across financial services, retail, agriculture, and the public sector. Combining large-scale data collection, analytical modeling, and strict confidentiality controls, we help Zimbabwean organizations lend responsibly and mitigate counterparty risk.',
  JSON_ARRAY(
    JSON_OBJECT('value', '2M+', 'label', 'Credit Profiles', 'detail', 'Consumer & commercial records maintained online'),
    JSON_OBJECT('value', '125+', 'label', 'Credit Providers', 'detail', 'Contributing data partners across Zimbabwe'),
    JSON_OBJECT('value', '2012', 'label', 'Zimbabwe Launch', 'detail', 'Serving Zimbabwean lenders and enterprises'),
    JSON_OBJECT('value', '24/7', 'label', 'Online Portal', 'detail', 'Real-time bureau & E Check access nationwide')
  ),
  'Our Services',
  'How can we help you?',
  JSON_ARRAY(
    JSON_OBJECT(
      'key', 'credit',
      'icon', 'file-spreadsheet',
      'title', 'Credit Information Services',
      'summary', 'Access detailed consumer and corporate credit profiles, KYC verification, risk scores, and demographic tracing tools from our robust bureau platform.',
      'back_title', 'Full Credit Lifecycle Coverage',
      'highlights', JSON_ARRAY(
        'Over 2 million consumer & corporate profiles',
        'Applicant KYC, Credit Risk Score & History',
        'Commercial Reports & Collections Tracing'
      ),
      'badge_file', NULL,
      'cta_label', 'Find Out More',
      'href', '/products/credit-information/'
    ),
    JSON_OBJECT(
      'key', 'agricultural',
      'icon', 'tractor',
      'title', 'Agricultural Services',
      'summary', 'Empowering agro-finance institutions and contracting firms to extend sustainable credit to smallholder, communal, and commercial farmers.',
      'back_title', 'Precision Agro-Risk Intelligence',
      'highlights', JSON_ARRAY(
        'Credit Exposure Reports & Agro Scoring',
        'Double-Dip & Side-Marketing Alerts',
        'Productivity & Produce Type Analytics'
      ),
      'badge_file', NULL,
      'cta_label', 'Find Out More',
      'href', '/products/agricultural-services/'
    ),
    JSON_OBJECT(
      'key', 'authentication',
      'icon', 'shield-check',
      'title', 'Authentication Services',
      'summary', 'Verify prospective employees, tenants, and business partners under our E Check screening brand to reduce onboarding and counterparty risk.',
      'back_title', 'E Check Background Screening',
      'highlights', JSON_ARRAY(
        'ZIMSEC, Cambridge & Tertiary Qualifications',
        'Drivers License & Police Clearance Checks',
        'Personal Credit Obligations & Defaults'
      ),
      'badge_file', 'badge-echeck.png',
      'cta_label', 'Find Out More',
      'href', '/products/authentication-services/'
    )
  ),
  'Ready to Strengthen Your Risk Decisions?',
  'Connect with our Harare advisory team to onboard as a data subscriber, request a credit profile, or integrate E Check verification into your hiring workflow.',
  'Get in Touch',
  'Expert Decision Systems Zimbabwe',
  'Expert Decision Systems Zimbabwe (XDS) provides credit information bureau reports, agricultural risk analytics, and E Check background authentication services.',
  'hero-home.webp'
) ON DUPLICATE KEY UPDATE
  `hero_badge` = VALUES(`hero_badge`),
  `hero_headline` = VALUES(`hero_headline`),
  `hero_slides_json` = VALUES(`hero_slides_json`),
  `intro_body` = VALUES(`intro_body`),
  `metrics_json` = VALUES(`metrics_json`),
  `meta_title` = VALUES(`meta_title`),
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 3. Unified About XDS & Why XDS Page (`page_about`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_about` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  `banner_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `overview_title` VARCHAR(180) NOT NULL,
  `overview_subtitle` VARCHAR(200) NOT NULL,
  `history_paragraphs_json` JSON NOT NULL,
  `footprint_stats_json` JSON NOT NULL,
  `why_title` VARCHAR(160) NOT NULL,
  `philosophy_title` VARCHAR(120) NOT NULL,
  `philosophy_body` TEXT NOT NULL,
  `people_title` VARCHAR(120) NOT NULL,
  `people_body` TEXT NOT NULL,
  `value_drivers_title` VARCHAR(140) NOT NULL,
  `value_drivers_json` JSON NOT NULL,
  `meta_title` VARCHAR(160) NOT NULL,
  `meta_description` VARCHAR(320) NOT NULL,
  `og_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_about` (
  `id`, `banner_image_file`, `overview_title`, `overview_subtitle`,
  `history_paragraphs_json`, `footprint_stats_json`,
  `why_title`, `philosophy_title`, `philosophy_body`,
  `people_title`, `people_body`, `value_drivers_title`, `value_drivers_json`,
  `meta_title`, `meta_description`, `og_image_file`
) VALUES (
  1,
  'banner-about.webp',
  'About Expert Decision Systems Zimbabwe',
  'Zimbabwe\'s trusted information bureau delivering accurate credit intelligence and verification solutions for the local market.',
  JSON_ARRAY(
    'Incorporated in Zimbabwe in 2012, Expert Decision Systems Zimbabwe (XDS) operates a comprehensive credit information bureau housing consumer and commercial credit records alongside verified public data. Today, more than 125 contributing Zimbabwean credit providers and over 2 million records power risk decisions across financial services, retail, agriculture, and the public sector.',
    'Beyond credit bureau reporting, XDS delivers specialised background authentication services through its E Check brand, helping Zimbabwean employers, recruitment agencies, and enterprises conduct dependable candidate and counterparty verification.',
    'Built specifically around the needs of Zimbabwean lenders, microfinance institutions, agro-financiers, and corporates, our bureau platform delivers 24/7 online access to structured credit profiles, commercial reports, and tracing analytics.',
    'Handling confidential subscriber information sits at the core of our mandate. Supported by resilient IT infrastructure and a formal Information Security Policy (ISSP), we safeguard every record entrusted to us against internal or external misuse.'
  ),
  JSON_ARRAY(
    JSON_OBJECT('metric', '2012', 'label', 'Established in Zimbabwe', 'icon', 'calendar-check'),
    JSON_OBJECT('metric', '125+', 'label', 'Zimbabwean Credit Providers', 'icon', 'building-2'),
    JSON_OBJECT('metric', '2M+', 'label', 'Consumer & Corporate Records', 'icon', 'database'),
    JSON_OBJECT('metric', '24/7', 'label', 'Online Bureau Accessibility', 'icon', 'clock')
  ),
  'Why XDS Zimbabwe?',
  'Our Philosophy',
  'We uphold rigorous standards of data privacy, legislative compliance, confidentiality, and accuracy—delivering intelligent information solutions that empower both Zimbabwean credit grantors and consumers.',
  'Our People',
  'Our multidisciplinary Zimbabwean management and technical teams bring deep bureau and screening expertise. Through continuous training and skills development, we equip our specialists to anticipate evolving local market risk challenges.',
  'Our Key Value Drivers',
  JSON_ARRAY(
    JSON_OBJECT(
      'icon', 'wrench',
      'title', 'Specialised Skillset',
      'front_copy', 'Dedicated focus on customer experience ensures lasting satisfaction while anticipating future market needs.',
      'back_copy', 'Dedicated Harare account analysts, bureau engineers, and vetting specialists tailor integrations and custom credit analytics to your exact lending workflow.'
    ),
    JSON_OBJECT(
      'icon', 'building-2',
      'title', 'Comprehensive Zimbabwe Bureau',
      'front_copy', 'Deep nationwide coverage unlocks dependable access to Zimbabwean consumer and corporate credit intelligence.',
      'back_copy', 'Backed by over 125 participating Zimbabwean banks, microfinance institutions, retailers, utilities, and agro-finance contributors across the country.'
    ),
    JSON_OBJECT(
      'icon', 'database',
      'title', 'Data Integrity, Accuracy & Detail',
      'front_copy', 'Strict validation pipelines preserve data integrity, ensuring dependable and actionable bureau products.',
      'back_copy', 'Automated quality checks, continuous data-provider reconciliation, and formal dispute resolution keep every consumer and corporate profile accurate.'
    ),
    JSON_OBJECT(
      'icon', 'cpu',
      'title', 'Innovation and Technology',
      'front_copy', 'Modern bureau platforms and cloud-ready interfaces elevate operational speed, productivity, and insight.',
      'back_copy', 'Real-time online portals, automated alerts for agro-finance, and secure API workflows help credit teams decide faster with lower operational friction.'
    )
  ),
  'About Us | XDS Zimbabwe',
  'Learn about Expert Decision Systems Zimbabwe (XDS), launched in 2012 with 125+ Zimbabwean credit providers, 2M+ records, our philosophy, people, and key value drivers.',
  'banner-about.webp'
) ON DUPLICATE KEY UPDATE
  `overview_title` = VALUES(`overview_title`),
  `overview_subtitle` = VALUES(`overview_subtitle`),
  `history_paragraphs_json` = VALUES(`history_paragraphs_json`),
  `footprint_stats_json` = VALUES(`footprint_stats_json`),
  `philosophy_body` = VALUES(`philosophy_body`),
  `people_body` = VALUES(`people_body`),
  `value_drivers_json` = VALUES(`value_drivers_json`),
  `meta_title` = VALUES(`meta_title`),
  `meta_description` = VALUES(`meta_description`),
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 4. Credit Information Services Page (`page_credit_services`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_credit_services` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  `banner_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `page_title` VARCHAR(160) NOT NULL,
  `overview_copy` TEXT NOT NULL,
  `offerings_json` JSON NOT NULL,
  `signup_cta_label` VARCHAR(80) NOT NULL,
  `signup_cta_url` VARCHAR(160) NOT NULL,
  `report_cta_label` VARCHAR(80) NOT NULL,
  `report_cta_url` VARCHAR(160) NOT NULL,
  `partners_heading` VARCHAR(100) NOT NULL,
  `partners_json` JSON NOT NULL COMMENT 'Only logo filenames stored inside JSON',
  `meta_title` VARCHAR(160) NOT NULL,
  `meta_description` VARCHAR(320) NOT NULL,
  `og_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_credit_services` (
  `id`, `banner_image_file`, `page_title`, `overview_copy`,
  `offerings_json`, `signup_cta_label`, `signup_cta_url`,
  `report_cta_label`, `report_cta_url`, `partners_heading`, `partners_json`,
  `meta_title`, `meta_description`, `og_image_file`
) VALUES (
  1,
  'banner-credit.webp',
  'Credit Information Services',
  'XDS Zimbabwe delivers current, granular intelligence on both individual consumers and corporate entities. With over 2 million credit records accessible 24/7 through our secure online portal, XDS Credit Profiles streamline credit underwriting, portfolio monitoring, and debtor recovery across every stage of the credit lifecycle.',
  JSON_ARRAY(
    JSON_OBJECT(
      'icon', 'user-check',
      'title', 'Credit Information Services',
      'subtitle', 'Consumer credit risk & KYC profiling',
      'items', JSON_ARRAY(
        'Applicant KYC Details',
        'Credit Risk Score',
        'Credit History',
        'Credit Accounts',
        'Adverse Accounts'
      )
    ),
    JSON_OBJECT(
      'icon', 'building-2',
      'title', 'Commercial Reports',
      'subtitle', 'Corporate due diligence & trade vetting',
      'items', JSON_ARRAY(
        'Company Registration Details',
        'Director KYC and Credit Data',
        'Compliance Data',
        'Registered Assets',
        'Banking Data'
      )
    ),
    JSON_OBJECT(
      'icon', 'search-check',
      'title', 'Collections and Tracing',
      'subtitle', 'Debtor location & portfolio analytics',
      'items', JSON_ARRAY(
        'Known Address Listing',
        'Contact Number Listing',
        'Employer Details',
        'Credit Book Exposure Analytics',
        'Custom Analytics'
      )
    )
  ),
  'Sign up',
  '#credit-signup-modal',
  'Request My Report',
  '#request-report-modal',
  'Our Partners',
  JSON_ARRAY(
    JSON_OBJECT('name', 'UNTU (Registered Microfinance Institution)', 'logo_file', 'partner-untu.png', 'sector', 'Microfinance'),
    JSON_OBJECT('name', 'Yambukai Holdings (Private) Limited', 'logo_file', 'partner-yambukai.png', 'sector', 'Registered Money Lender')
  ),
  'Credit Information Services | XDS Zimbabwe',
  'Access over 2 million consumer and corporate credit records, commercial reports, credit risk scores, and debtor tracing solutions from XDS Zimbabwe.',
  'banner-credit.webp'
) ON DUPLICATE KEY UPDATE
  `page_title` = VALUES(`page_title`),
  `meta_title` = VALUES(`meta_title`),
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 5. Agricultural Services Page (`page_agricultural_services`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_agricultural_services` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  `banner_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `diagram_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `page_title` VARCHAR(160) NOT NULL,
  `intro_paragraph` TEXT NOT NULL,
  `scoring_paragraph` TEXT NOT NULL,
  `risk_paragraph` TEXT NOT NULL,
  `features_json` JSON NOT NULL,
  `pillars_json` JSON NOT NULL,
  `meta_title` VARCHAR(160) NOT NULL,
  `meta_description` VARCHAR(320) NOT NULL,
  `og_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_agricultural_services` (
  `id`, `banner_image_file`, `diagram_image_file`, `page_title`,
  `intro_paragraph`, `scoring_paragraph`, `risk_paragraph`,
  `features_json`, `pillars_json`,
  `meta_title`, `meta_description`, `og_image_file`
) VALUES (
  1,
  'banner-agricultural.webp',
  'diagram-agricultural.webp',
  'Agricultural Services',
  'The XDS Agro Business Unit facilitates structured credit data sharing, agricultural credit scoring, and risk analytics designed to expand responsible lending across Zimbabwe\'s agriculture value chain—including smallholder and communal farmers.',
  'By combining historical farmer repayment behaviour, payment patterns, productivity metrics, and crop or produce profiles with agro-economist insights, XDS generates composite scores that capture both financial discipline and farm yield potential.',
  'We partner with agro-finance lenders, input suppliers, and contract-farming operators to manage exposure prudently across small-to-medium scale and commercial farming portfolios.',
  JSON_ARRAY(
    JSON_OBJECT(
      'icon', 'bar-chart-3',
      'title', 'Agro Credit Scoring',
      'description', 'Composite risk scoring blending historical repayment behaviour with agro-economist productivity benchmarks and produce types.'
    ),
    JSON_OBJECT(
      'icon', 'file-bar-chart',
      'title', 'Credit Exposure Reports',
      'description', 'Consolidated visibility into farmer obligations across participating agro-lenders and input contracting schemes.'
    ),
    JSON_OBJECT(
      'icon', 'alert-triangle',
      'title', 'Double-Dip Alerts',
      'description', 'Proactive notifications when a borrower seeks overlapping seasonal financing for the same hectarage or crop cycle.'
    ),
    JSON_OBJECT(
      'icon', 'shield-alert',
      'title', 'Side-Marketing Alerts',
      'description', 'Early warning indicators that protect contracting firms against unauthorised off-contract crop diversion.'
    )
  ),
  JSON_ARRAY(
    JSON_OBJECT('icon', 'receipt', 'label', 'Credit History'),
    JSON_OBJECT('icon', 'server', 'label', 'Credit Data'),
    JSON_OBJECT('icon', 'clipboard-check', 'label', 'Credit Scores'),
    JSON_OBJECT('icon', 'filter', 'label', 'Credit Analysis'),
    JSON_OBJECT('icon', 'sprout', 'label', 'Type of Produce'),
    JSON_OBJECT('icon', 'tractor', 'label', 'Productivity Levels')
  ),
  'Agricultural Services | XDS Zimbabwe',
  'Sustainable agro-finance risk tools from XDS Zimbabwe: agro credit scoring, credit exposure reports, double-dip alerts, and side-marketing alerts.',
  'banner-agricultural.webp'
) ON DUPLICATE KEY UPDATE
  `page_title` = VALUES(`page_title`),
  `meta_title` = VALUES(`meta_title`),
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 6. Authentication Services (E Check) Page (`page_authentication_services`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_authentication_services` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  `banner_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `echeck_badge_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `page_title` VARCHAR(160) NOT NULL,
  `overview_copy` TEXT NOT NULL,
  `checks_json` JSON NOT NULL,
  `partners_heading` VARCHAR(100) NOT NULL,
  `partners_json` JSON NOT NULL COMMENT 'Only logo filenames stored inside JSON',
  `meta_title` VARCHAR(160) NOT NULL,
  `meta_description` VARCHAR(320) NOT NULL,
  `og_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_authentication_services` (
  `id`, `banner_image_file`, `echeck_badge_file`, `page_title`,
  `overview_copy`, `checks_json`, `partners_heading`, `partners_json`,
  `meta_title`, `meta_description`, `og_image_file`
) VALUES (
  1,
  'banner-authentication.webp',
  'badge-echeck.png',
  'Authentication Services',
  'Operating under our E Check brand, XDS Zimbabwe delivers thorough employment screening and counterparty vetting solutions to employers, recruitment agencies, and organisations across Zimbabwe—confirming academic credentials, professional standing, work history, and legal integrity before you hire.',
  JSON_ARRAY(
    JSON_OBJECT(
      'icon', 'award',
      'title', 'Qualification Verification',
      'items', JSON_ARRAY(
        'ZIMSEC & Cambridge certificates',
        'Tertiary qualifications',
        'Professional qualifications'
      )
    ),
    JSON_OBJECT(
      'icon', 'id-card',
      'title', 'Drivers License Verification',
      'items', JSON_ARRAY(
        'Zimbabwe License',
        'International License',
        'Defensive Driving Certificate'
      )
    ),
    JSON_OBJECT(
      'icon', 'fingerprint',
      'title', 'Criminal Record Verification',
      'items', JSON_ARRAY(
        'Police clearance certificates',
        'Verification of police clearance certificates'
      )
    ),
    JSON_OBJECT(
      'icon', 'file-check-2',
      'title', 'Credit Checks',
      'items', JSON_ARRAY(
        'Credit obligations',
        'Confirm personal and demographic details',
        'Defaults and judgments'
      )
    )
  ),
  'Our Partners',
  JSON_ARRAY(
    JSON_OBJECT('name', 'FedEx', 'logo_file', 'partner-fedex.png'),
    JSON_OBJECT('name', 'Ifacts Employee Screening & Vetting Services', 'logo_file', 'partner-ifacts.png'),
    JSON_OBJECT('name', 'LexisNexis', 'logo_file', 'partner-lexisnexis.png'),
    JSON_OBJECT('name', 'MIE Smart Vetting Solutions', 'logo_file', 'partner-mie.png'),
    JSON_OBJECT('name', 'Pact', 'logo_file', 'partner-pact.png'),
    JSON_OBJECT('name', 'The Valcol Group', 'logo_file', 'partner-valcol.png')
  ),
  'Authentication Services | XDS Zimbabwe',
  'Verify qualifications (ZIMSEC, Cambridge, tertiary), drivers licenses, criminal records, and credit histories with XDS Zimbabwe E Check screening.',
  'banner-authentication.webp'
) ON DUPLICATE KEY UPDATE
  `page_title` = VALUES(`page_title`),
  `overview_copy` = VALUES(`overview_copy`),
  `meta_title` = VALUES(`meta_title`),
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 7. Articles & Insights Page (`page_articles`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_articles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(140) NOT NULL UNIQUE COMMENT 'Folder name inside assets/images/articles/{slug}/',
  `title` VARCHAR(200) NOT NULL,
  `summary` TEXT NOT NULL,
  `body_content` LONGTEXT NOT NULL,
  `image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/articles/{slug}/',
  `banner_image_file` VARCHAR(120) NOT NULL DEFAULT 'banner-articles.webp' COMMENT 'Filename inside assets/images/',
  `published_date` DATE NOT NULL,
  `author` VARCHAR(120) NOT NULL DEFAULT 'XDS Research Desk',
  `category` VARCHAR(80) NOT NULL,
  `read_time_minutes` TINYINT UNSIGNED NOT NULL DEFAULT 4,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `meta_title` VARCHAR(160) NOT NULL,
  `meta_description` VARCHAR(320) NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_articles_active_sort` (`is_active`, `sort_order`, `published_date`),
  INDEX `idx_articles_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_articles` (
  `slug`, `title`, `summary`, `body_content`, `image_file`, `banner_image_file`,
  `published_date`, `author`, `category`, `read_time_minutes`, `sort_order`,
  `is_active`, `meta_title`, `meta_description`
) VALUES
(
  'benefits-of-credit-scoring-to-banks',
  'Benefits of credit scoring to banks',
  'Lending to individuals and enterprises carries measurable risk, especially where credit providers lack standardised scoring frameworks. Automated credit scores bring speed, consistency, and sharper portfolio governance.',
  '<p>Extending credit to retail borrowers and commercial enterprises remains one of the core profit engines—and risk exposures—for banking institutions. In markets where manual underwriting dominates, loan approvals can be slow, subjective, and uneven across branches.</p><h3>Objective & Consistent Risk Pricing</h3><p>Bureau credit scoring transforms multi-provider repayment data into a statistically validated probability of default. Banks gain an objective baseline to calibrate credit limits, interest pricing, and collateral requirements uniformly across every applicant.</p><h3>Faster Turnaround & Lower Non-Performing Loans</h3><p>Automated scorecards shorten loan decisioning from days to minutes while flagging over-indebtedness early. Over time, institutions pairing XDS bureau scores with internal behavioural models experience measurable reductions in delinquency and provisioning costs.</p>',
  'cover.webp', 'banner-articles.webp', '2025-11-12', 'XDS Credit Analytics Team', 'Credit Scoring', 4, 1, 1,
  'Benefits of credit scoring to banks | XDS Zimbabwe',
  'Discover how automated bureau credit scoring helps Zimbabwean banks improve risk pricing, accelerate approvals, and reduce non-performing loans.'
),
(
  'basel-ii-the-role-of-credit-bureaus',
  'Basel II: The role of credit bureaus',
  'International banking supervision standards under the Basel Accords rely heavily on robust risk quantification. Credit bureaus supply the historical default and exposure depth required for sound capital allocation.',
  '<p>To promote resilience across global financial markets, the Basel Committee on Banking Supervision established capital adequacy frameworks commonly known as the Basel Accords. Under Basel II and subsequent enhancements, banks must align regulatory capital closely with actual credit risk.</p><h3>Supporting Internal Ratings & Supervisory Review</h3><p>Accurate estimation of Probability of Default (PD) and Exposure at Default (EAD) requires broad, longitudinal borrower history beyond what a single bank holds internally. Comprehensive credit bureaus bridge that gap by aggregating positive and adverse credit performance market-wide.</p><h3>Strengthening Market Discipline</h3><p>By enabling transparent risk measurement and portfolio stress-testing, XDS helps regulated financial institutions meet Reserve Bank supervisory expectations while deploying capital more efficiently.</p>',
  'cover.webp', 'banner-articles.webp', '2025-10-28', 'XDS Regulatory Advisory', 'Regulation & Compliance', 5, 2, 1,
  'Basel II and the Role of Credit Bureaus | XDS Zimbabwe',
  'Learn how comprehensive credit bureau data supports Basel II risk quantification, capital adequacy, and supervisory compliance for banks.'
),
(
  'why-credit-information-sharing-makes-sense',
  'Why credit information sharing makes sense',
  'Capital and reliable borrower information are the twin foundations of healthy lending. Sharing full-file credit data lowers information asymmetry and widens access to affordable finance.',
  '<p>Every sound lending decision depends on two essentials: available capital and trustworthy information about the borrower. When lenders operate in isolation, serial defaulters can migrate between institutions unnoticed while responsible borrowers struggle to prove their track record.</p><h3>Rewarding Good Repayment Behaviour</h3><p>Full-file credit information sharing captures both positive repayment milestones and arrears. Disciplined consumers and SMEs build reputational collateral—enabling faster approvals and more competitive credit terms without excessive physical security.</p><h3>Protecting Ecosystem Stability</h3><p>Across Zimbabwe\'s 125+ participating credit providers, structured data sharing curbs multiple borrowing, strengthens collection recoveries, and builds a healthier financial sector for all participants.</p>',
  'cover.webp', 'banner-articles.webp', '2025-10-05', 'XDS Research Desk', 'Bureau Insights', 4, 3, 1,
  'Why Credit Information Sharing Makes Sense | XDS Zimbabwe',
  'Explore why full-file credit information sharing reduces default risk, builds reputational collateral, and expands financial inclusion in Zimbabwe.'
),
(
  'profiling-credit-risk-for-smallholder-farmers',
  'Profiling credit risk for smallholder farmers',
  'Unlocking seasonal finance for Zimbabwean smallholder farmers is vital for food security and rural livelihoods. Tailored agro-credit profiling blends repayment records with yield and produce data.',
  '<p>Agriculture anchors Zimbabwe\'s employment, household incomes, and national food security. Yet smallholder and communal farmers have historically faced steep barriers to formal financing due to limited conventional collateral and sparse credit files.</p><h3>Beyond Traditional Credit Metrics</h3><p>XDS Agricultural Services bridges this divide by pairing farmer repayment history with agro-economist insights—evaluating crop types, historical productivity levels, seasonal cash-flow cycles, and input contracting records.</p><h3>Preventing Double-Dipping & Side-Marketing</h3><p>With shared credit exposure reports and proactive double-dip and side-marketing alerts, agro-financiers can fund productive farmers confidently while safeguarding portfolio sustainability.</p>',
  'cover.webp', 'banner-articles.webp', '2025-09-18', 'XDS Agro Business Unit', 'Agricultural Finance', 5, 4, 1,
  'Profiling Credit Risk for Smallholder Farmers | XDS Zimbabwe',
  'How XDS Agricultural Services combines farmer repayment history, productivity data, and exposure alerts to unlock smallholder agro-finance.'
),
(
  'credit-reports-vital-in-financial-system',
  'Credit reports vital in financial system',
  'Safe, accurate, and dependable credit reporting infrastructure underpins modern financial intermediation, giving lenders clarity and consumers transparent visibility into their financial standing.',
  '<p>Modern credit reporting systems serve as critical financial infrastructure—comparable in importance to payment clearing networks. When credit data is reliable, secure, and accessible in real time, credit flows smoothly to productive households and businesses.</p><h3>Reducing Friction Across the Credit Lifecycle</h3><p>From initial applicant KYC verification through ongoing account monitoring and debtor tracing, XDS Credit Profiles equip institutions with structured insights at every decision checkpoint.</p><h3>Empowering Informed Consumers</h3><p>Equally important, individuals can review their own credit reports, identify errors promptly, and cultivate stronger financial profiles over time—reinforcing trust across the entire financial system.</p>',
  'cover.webp', 'banner-articles.webp', '2025-08-29', 'XDS Research Desk', 'Financial Systems', 4, 5, 1,
  'Why Credit Reports Are Vital in the Financial System | XDS Zimbabwe',
  'Understand the vital role secure and reliable credit reporting systems play in maintaining stability and efficiency in Zimbabwe\'s financial system.'
),
(
  'guidelines-for-good-credit-behaviour',
  'Guidelines for good credit behaviour',
  'Responsible personal credit supports asset building, education, and household resilience. Practical habits—paying on time, managing utilisation, and checking your report—build a strong credit score.',
  '<p>Personal credit is often misunderstood as purely consumption-driven, yet when managed prudently it enables households to smooth cash flows, finance education, upgrade homes, and grow small ventures. Your credit profile reflects how consistently you honour those commitments.</p><h3>Core Habits for a Healthy Credit Profile</h3><ul><li><strong>Pay instalments on or before the due date:</strong> Payment timeliness is one of the strongest positive drivers in any credit score.</li><li><strong>Borrow within sustainable income limits:</strong> Avoid stacking multiple short-term facilities that strain monthly disposable income.</li><li><strong>Communicate early during hardship:</strong> Proactive restructuring with your lender prevents avoidable adverse listings.</li><li><strong>Review your XDS Credit Report regularly:</strong> Confirm that your personal details and closed accounts are accurately reflected.</li></ul>',
  'cover.webp', 'banner-articles.webp', '2025-08-10', 'XDS Consumer Education', 'Consumer Guide', 3, 6, 1,
  'Guidelines for Good Credit Behaviour | XDS Zimbabwe',
  'Practical guidelines for Zimbabwean consumers to build and maintain a healthy credit profile and strong credit score.'
) ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `summary` = VALUES(`summary`),
  `meta_title` = VALUES(`meta_title`),
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 8. Data Privacy (ISSP) Page (`page_data_privacy`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_data_privacy` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  `banner_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `page_title` VARCHAR(160) NOT NULL,
  `intro_copy` TEXT NOT NULL,
  `mission_title` VARCHAR(160) NOT NULL,
  `mission_intro` TEXT NOT NULL,
  `mission_bullets_json` JSON NOT NULL,
  `philosophy_title` VARCHAR(160) NOT NULL,
  `philosophy_body` TEXT NOT NULL,
  `objectives_title` VARCHAR(160) NOT NULL,
  `objectives_bullets_json` JSON NOT NULL,
  `scope_title` VARCHAR(160) NOT NULL,
  `scope_paragraphs_json` JSON NOT NULL,
  `policy_doc_purpose` VARCHAR(60) NOT NULL DEFAULT 'policy' COMMENT 'Subfolder inside assets/documents/',
  `policy_doc_folder` VARCHAR(120) NOT NULL DEFAULT 'xds-information-security-policy' COMMENT 'Document slug folder',
  `policy_doc_file` VARCHAR(120) NOT NULL DEFAULT 'xds-issp-2026.pdf' COMMENT 'Filename only',
  `meta_title` VARCHAR(160) NOT NULL,
  `meta_description` VARCHAR(320) NOT NULL,
  `og_image_file` VARCHAR(120) NOT NULL COMMENT 'Filename inside assets/images/',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_data_privacy` (
  `id`, `banner_image_file`, `page_title`, `intro_copy`,
  `mission_title`, `mission_intro`, `mission_bullets_json`,
  `philosophy_title`, `philosophy_body`,
  `objectives_title`, `objectives_bullets_json`,
  `scope_title`, `scope_paragraphs_json`,
  `policy_doc_purpose`, `policy_doc_folder`, `policy_doc_file`,
  `meta_title`, `meta_description`, `og_image_file`
) VALUES (
  1,
  'banner-data-privacy.webp',
  'Data Privacy',
  'XDS Zimbabwe aims to be recognised as a trusted custodian of credit and verification information across the Zimbabwean market. Our Information Security Policy (ISSP) establishes the governance controls that safeguard all subscriber and consumer data under our care.',
  'Information Security Mission',
  'XDS maintains a disciplined, industry-aligned information security posture complying with applicable legislation and regulatory directives to preserve the confidentiality, integrity, and availability of client and company assets. Specifically, XDS commits to:',
  JSON_ARRAY(
    'Authorising collection, processing, disclosure, and amendment of records strictly for legitimate business, legal, regulatory, and contractual purposes.',
    'Protecting the intellectual property and licensing rights of XDS, subscriber, and third-party software.',
    'Safeguarding the privacy of all personal, consumer, and corporate information.',
    'Verifying the identity and authorisation of every internal or external party requesting information access.',
    'Applying security safeguards proportionate to the sensitivity and value of the protected information.',
    'Continuously monitoring system access to detect policy breaches promptly and enforce disciplinary or legal remedies.',
    'Protecting information assets and bureau platforms against unauthorised destruction, tampering, or service disruption.'
  ),
  'Information Access Philosophy',
  'Access to information by employees, subscribers, and approved third parties is governed strictly on a "need to know" basis—permitted only when tied to an authorised business function and essential to fulfilling that responsibility.',
  'Information Security Policy Objectives',
  JSON_ARRAY(
    'Keep all data entrusted to XDS secure, resilient, and protected from internal or external misuse.',
    'Shield XDS and our clients from direct or indirect financial harm caused by security incidents.',
    'Protect XDS commercial integrity by restricting access strictly to approved channels and safeguarding proprietary systems.',
    'Uphold the highest professional and international credit-bureau security standards.',
    'Ensure every staff member understands and complies fully with their information security duties.',
    'Embed robust security and data-integrity controls across all XDS products and platforms.',
    'Maintain clear auditability and accountability for every action performed on information systems.'
  ),
  'Scope of Information Security Policy',
  JSON_ARRAY(
    'This Policy binds all XDS Zimbabwe employees and contractors across our operations. Its controls are mandatory and aligned with applicable Zimbabwean statutory and regulatory requirements.',
    'External subscribers and third parties requiring access to XDS Zimbabwe records must comply with these core principles and execute formal written subscriber agreements defining access scope and security obligations.',
    'Within this Policy, "information" encompasses all data and products generated, collected, stored, or transmitted on XDS Zimbabwe systems or those of authorised agents, including any translation of such data or software onto other media.'
  ),
  'policy',
  'xds-information-security-policy',
  'xds-issp-2026.pdf',
  'Data Privacy | XDS Zimbabwe',
  'Review the Expert Decision Systems Zimbabwe (XDS) Information Security Policy (ISSP), access philosophy, security objectives, and data protection scope.',
  'banner-data-privacy.webp'
) ON DUPLICATE KEY UPDATE
  `page_title` = VALUES(`page_title`),
  `intro_copy` = VALUES(`intro_copy`),
  `scope_paragraphs_json` = VALUES(`scope_paragraphs_json`),
  `meta_title` = VALUES(`meta_title`),
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 9. Frequently Asked Questions Page (`page_faq`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_faq` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `banner_image_file` VARCHAR(120) NOT NULL DEFAULT 'banner-faq.webp' COMMENT 'Filename inside assets/images/',
  `question` VARCHAR(255) NOT NULL,
  `answer` TEXT NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `icon_key` VARCHAR(60) NOT NULL DEFAULT 'help-circle',
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `meta_title` VARCHAR(160) NOT NULL DEFAULT 'FAQ | XDS Zimbabwe',
  `meta_description` VARCHAR(320) NOT NULL DEFAULT 'Find answers to common questions about XDS Zimbabwe credit bureau reports, data submission, regulation, data security, and accuracy.',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_faq_active_sort` (`is_active`, `sort_order`),
  INDEX `idx_faq_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_faq` (
  `id`, `banner_image_file`, `question`, `answer`, `category`, `icon_key`, `sort_order`, `is_active`
) VALUES
(
  1, 'banner-faq.webp',
  'What are the key benefits to a credit provider using XDS Reports?',
  'XDS Reports empower lenders with a comprehensive view of an applicant\'s repayment track record, total credit exposure, KYC verification, and risk score across 125+ Zimbabwean credit providers. This reduces default rates, curbs over-indebtedness, accelerates credit approvals, and improves collections recovery through up-to-date tracing information.',
  'Credit Providers', 'file-text', 1, 1
),
(
  2, 'banner-faq.webp',
  'How much am I charged for submitting my own data?',
  'Submitting your credit portfolio data to the XDS bureau is free of charge. Data submission is built on a reciprocity framework that enriches the bureau ecosystem and qualifies participating subscribers for preferential inquiry tariffs and richer bureau analytics.',
  'Data Submission', 'dollar-sign', 2, 1
),
(
  3, 'banner-faq.webp',
  'Where do you get your information from?',
  'XDS compiles information from over 125 participating Zimbabwean credit providers—including banks, microfinance institutions, retailers, utilities, telecoms, and agro-financiers—supplemented by verified public records such as company registries and court judgments.',
  'Data Sources', 'database', 3, 1
),
(
  4, 'banner-faq.webp',
  'Can I use your product without submitting data?',
  'XDS operates on a principle of mutual credit information sharing. While certain verification and screening services such as E Check employment checks or commercial due diligence do not require credit-book submission, credit bureau subscribers generally participate in structured data contribution under a signed subscriber agreement.',
  'Data Submission', 'layers', 4, 1
),
(
  5, 'banner-faq.webp',
  'In Zimbabwe, what regulations exist for submitting data to the bureau?',
  'Credit reporting in Zimbabwe operates under the oversight of the Reserve Bank of Zimbabwe (RBZ) Credit Registry and Credit Bureau regulatory framework, alongside Zimbabwe\'s Data Protection Act and Banking Act provisions. Credit providers include standard credit-bureau disclosure clauses in loan agreements to ensure lawful, transparent data sharing.',
  'Regulation & Compliance', 'scale', 5, 1
),
(
  6, 'banner-faq.webp',
  'I fear that competitors will poach my good customers, so why should I submit my data on good customers?',
  'Strict confidentiality rules prohibit any subscriber from using bureau data for marketing or customer solicitation. Credit reports display aggregated account behaviour without exposing confidential lender identities to unauthorised queries, and every inquiry requires a permissible credit or vetting purpose backed by an audit trail. Sharing positive data actually protects your good customers from becoming over-extended elsewhere.',
  'Data Security', 'shield', 6, 1
),
(
  7, 'banner-faq.webp',
  'How do I ensure my data is secure?',
  'XDS enforces end-to-end encrypted data transmission, strict role-based "need to know" access controls, multi-layered firewall and database security, and continuous access auditing under our Information Security Policy (ISSP). Every user query is logged and traceable.',
  'Data Security', 'lock', 7, 1
),
(
  8, 'banner-faq.webp',
  'What do I stand to benefit from submitting negative data?',
  'Submitting adverse or delinquent account data creates strong repayment discipline—borrowers have a clear incentive to settle arrears promptly to restore their credit standing. It also alerts the wider market to serial defaulters and helps your collections team prioritise recoverable accounts.',
  'Credit Providers', 'alert-circle', 8, 1
),
(
  9, 'banner-faq.webp',
  'Do you collect judgement data?',
  'Yes. XDS captures verified civil court judgment records and public legal notices from Zimbabwean courts and updates records promptly once proof of settlement or rescission is provided.',
  'Data Sources', 'gavel', 9, 1
),
(
  10, 'banner-faq.webp',
  'How do we ensure that our data is accurate?',
  'Every monthly and periodic data submission passes through automated format, identity-matching, and anomaly validation pipelines before loading into the bureau. In addition, consumers and credit providers have access to a structured dispute and verification channel to promptly investigate and rectify any discrepancy.',
  'Data Quality', 'check-circle-2', 10, 1
) ON DUPLICATE KEY UPDATE
  `question` = VALUES(`question`),
  `answer` = VALUES(`answer`),
  `icon_key` = VALUES(`icon_key`),
  `meta_title` = 'FAQ | XDS Zimbabwe',
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 10. Contact & Office Details Page (`page_contact`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `page_contact` (
  `id` TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
  `page_title` VARCHAR(120) NOT NULL,
  `heading` VARCHAR(140) NOT NULL,
  `intro_copy` TEXT NOT NULL,
  `office_title` VARCHAR(100) NOT NULL,
  `address_line1` VARCHAR(120) NOT NULL,
  `address_line2` VARCHAR(120) NOT NULL,
  `city_country` VARCHAR(120) NOT NULL,
  `phone_1` VARCHAR(40) NOT NULL,
  `phone_2` VARCHAR(40) NOT NULL,
  `phone_3` VARCHAR(40) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `hours_weekday` VARCHAR(120) NOT NULL,
  `hours_weekend` VARCHAR(120) NOT NULL,
  `map_lat` DECIMAL(10,6) NOT NULL,
  `map_lng` DECIMAL(10,6) NOT NULL,
  `map_embed_url` VARCHAR(512) NOT NULL,
  `inquiry_needs_json` JSON NOT NULL,
  `meta_title` VARCHAR(160) NOT NULL,
  `meta_description` VARCHAR(320) NOT NULL,
  `og_image_file` VARCHAR(120) NOT NULL DEFAULT 'hero-home.webp' COMMENT 'Filename inside assets/images/',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_contact` (
  `id`, `page_title`, `heading`, `intro_copy`, `office_title`,
  `address_line1`, `address_line2`, `city_country`,
  `phone_1`, `phone_2`, `phone_3`, `email`,
  `hours_weekday`, `hours_weekend`, `map_lat`, `map_lng`, `map_embed_url`,
  `inquiry_needs_json`, `meta_title`, `meta_description`, `og_image_file`
) VALUES (
  1,
  'Contact',
  'Get in Touch',
  'We would like to hear from you. For enquiries, subscriber onboarding, consumer credit report assistance, or consultation scheduling, complete the form below or give our Harare office a ring.',
  'Our Office Info',
  '8th Floor Pegasus House,',
  'Samora Machel Avenue',
  'Harare, Zimbabwe',
  '+263 242 779 344',
  '+263 242 779 341',
  '+263 242 779 392',
  'info@xds.co.zw',
  'Monday - Friday: 0800hrs - 1630hrs',
  'Saturday - Sunday: Closed',
  -17.826584,
  31.045716,
  'https://www.google.com/maps?q=Expert+Decision+Systems+Zimbabwe,+8th+Floor+Pegasus+House,+Samora+Machel+Ave,+Harare,+Zimbabwe&output=embed',
  JSON_ARRAY(
    'Credit Information Services / Bureau Subscription',
    'Request My Personal Credit Report',
    'Commercial Reports & Collections Tracing',
    'Agricultural Services & Agro Credit Scoring',
    'Authentication Services (E Check Screening)',
    'Data Dispute / Profile Query',
    'General Enquiry'
  ),
  'Contact Us | XDS Zimbabwe',
  'Contact Expert Decision Systems Zimbabwe at 8th Floor Pegasus House, Samora Machel, Harare. Call +263 242 779 344 or schedule a callback online.',
  'hero-home.webp'
) ON DUPLICATE KEY UPDATE
  `email` = VALUES(`email`),
  `meta_title` = VALUES(`meta_title`),
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 11. Contact & Inquiry Submissions (`contact_inquiries`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_inquiries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `reference_code` VARCHAR(24) NOT NULL UNIQUE,
  `firstname` VARCHAR(80) NOT NULL,
  `lastname` VARCHAR(80) NOT NULL,
  `email` VARCHAR(160) NOT NULL,
  `phone` VARCHAR(40) DEFAULT NULL,
  `company` VARCHAR(140) DEFAULT NULL,
  `need_category` VARCHAR(160) NOT NULL,
  `preferred_date` DATE DEFAULT NULL COMMENT 'Selected via Flatpickr',
  `preferred_time` VARCHAR(20) DEFAULT NULL COMMENT 'Selected via Flatpickr business hours',
  `message` TEXT NOT NULL,
  `source_page` VARCHAR(80) NOT NULL DEFAULT 'contact',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('new', 'in_review', 'responded', 'archived') NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_inquiries_status_created` (`status`, `created_at`),
  INDEX `idx_inquiries_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 12. Unified Portal & CMS User Accounts (`portal_users`)
--     role = 'admin' | 'editor' -> redirects to /cms/
--     role = 'credit_user'      -> redirects to /credit-portal/
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `portal_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(160) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'editor', 'credit_user') NOT NULL DEFAULT 'credit_user',
  `organization` VARCHAR(160) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_portal_users_role_active` (`role`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default seeded accounts (change passwords after initial setup):
--   Admin  : admin@xds.co.zw  / XdsAdmin@2026  -> /cms/
--   Client : client@xds.co.zw / XdsClient@2026 -> /credit-portal/
INSERT INTO `portal_users` (`full_name`, `email`, `password_hash`, `role`, `organization`, `is_active`) VALUES
(
  'XDS System Administrator',
  'admin@xds.co.zw',
  '$2y$10$Lz1w6JPMPeXGUx/y3hoYYu2lOv36vjsIK0hzFIhc7fXMDI9YkBHW2',
  'admin',
  'Expert Decision Systems Zimbabwe',
  1
),
(
  'Credit Subscriber Demo',
  'client@xds.co.zw',
  '$2y$10$it5AlwFrlhB2Bq/tnsYdRuuQ1WbVquIeWdZTD1H/eEpOlIBmqoQcm',
  'credit_user',
  'UNTU Microfinance',
  1
) ON DUPLICATE KEY UPDATE
  `full_name` = VALUES(`full_name`),
  `role` = VALUES(`role`),
  `updated_at` = CURRENT_TIMESTAMP;

-- ----------------------------------------------------------------------------
-- 13. CMS Audit & Activity Log (`cms_activity_log`)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cms_activity_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `user_name` VARCHAR(120) NOT NULL,
  `section` VARCHAR(80) NOT NULL,
  `action` VARCHAR(60) NOT NULL,
  `summary` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_cms_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
