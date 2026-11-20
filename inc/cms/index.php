<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/index.php';

if (!xds_is_admin()) {
    xds_json_response([
        'ok'      => false,
        'message' => 'Administrator authentication required.',
    ], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$pdo    = xds_db();

// -----------------------------------------------------------------------------
// Helper: Read JSON or POST Body
// -----------------------------------------------------------------------------
function xds_cms_input(): array
{
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
        $raw = json_decode(file_get_contents('php://input') ?: '{}', true);
        return is_array($raw) ? $raw : [];
    }
    return $_POST;
}

function xds_require_db_or_fail(?PDO $pdo): PDO
{
    if ($pdo === null) {
        xds_json_response([
            'ok'      => false,
            'message' => 'MySQL database (`inungutech_xds`) is not connected. Import `database/inungutech_xds.sql` and verify credentials in `inc/config/index.php` (or `inc/config/db.local.php`).',
        ], 503);
    }
    return $pdo;
}

function xds_sanitize_filename(string $name): string
{
    $base = basename(trim($name));
    $clean = preg_replace('/[^a-zA-Z0-9._-]/', '-', $base) ?: '';
    return ltrim($clean, '.-');
}

function xds_sanitize_slug(string $slug): string
{
    $s = strtolower(trim($slug));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?: '';
    return trim($s, '-');
}

/**
 * Extracts the first non-empty paragraph from rich-text HTML and truncates cleanly to <= $maxLen chars.
 */
function xds_extract_first_paragraph_summary(string $html, int $maxLen = 250): string
{
    $firstBlock = '';
    if (preg_match_all('/<(?:p|div|li|blockquote)\b[^>]*>(.*?)<\/(?:p|div|li|blockquote)>/is', $html, $matches)) {
        foreach ($matches[1] as $inner) {
            $text = html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = trim((string)preg_replace('/\s+/u', ' ', $text));
            if ($text !== '') {
                $firstBlock = $text;
                break;
            }
        }
    }

    if ($firstBlock === '') {
        $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $firstBlock = trim((string)preg_replace('/\s+/u', ' ', $plain));
    }

    if (mb_strlen($firstBlock, 'UTF-8') <= $maxLen) {
        return $firstBlock;
    }

    $cut = mb_substr($firstBlock, 0, $maxLen - 3, 'UTF-8');
    $lastSpace = mb_strrpos($cut, ' ', 0, 'UTF-8');
    if ($lastSpace !== false && $lastSpace > (int)($maxLen * 0.6)) {
        $cut = mb_substr($cut, 0, $lastSpace, 'UTF-8');
    }
    return rtrim($cut, " \t\n\r\0\x0B.,;:-") . '...';
}

// -----------------------------------------------------------------------------
// GET ?action=bootstrap -> Full CMS State for Dashboard & Editors
// -----------------------------------------------------------------------------
if ($method === 'GET') {
    $action = strtolower(trim((string)($_GET['action'] ?? 'bootstrap')));
    if ($action !== 'bootstrap') {
        xds_json_response(['ok' => false, 'message' => 'Unknown GET action.'], 400);
    }

    $system = xds_get_system();
    $home   = xds_fetch_single_table('page_home', 'page_home');
    $about  = xds_fetch_single_table('page_about', 'page_about');
    $credit = xds_fetch_single_table('page_credit_services', 'page_credit_services');
    $agri   = xds_fetch_single_table('page_agricultural_services', 'page_agricultural_services');
    $auth   = xds_fetch_single_table('page_authentication_services', 'page_authentication_services');
    $priv   = xds_fetch_single_table('page_data_privacy', 'page_data_privacy');
    $cont   = xds_fetch_single_table('page_contact', 'page_contact');

    $articles  = [];
    $faqs      = [];
    $inquiries = [];
    $users     = [];
    $activity  = [];

    if ($pdo !== null) {
        try {
            $articles  = $pdo->query('SELECT * FROM `page_articles` ORDER BY `sort_order` ASC, `published_date` DESC')->fetchAll();
            $faqs      = $pdo->query('SELECT * FROM `page_faq` ORDER BY `sort_order` ASC, `id` ASC')->fetchAll();
            $inquiries = $pdo->query('SELECT * FROM `contact_inquiries` ORDER BY `created_at` DESC LIMIT 100')->fetchAll();
            $users     = $pdo->query('SELECT `id`, `full_name`, `email`, `role`, `organization`, `is_active`, `last_login_at`, `created_at` FROM `portal_users` ORDER BY `id` ASC')->fetchAll();
            $activity  = $pdo->query('SELECT * FROM `cms_activity_log` ORDER BY `created_at` DESC LIMIT 20')->fetchAll();
        } catch (Throwable $e) {
            error_log('[XDS CMS Bootstrap] ' . $e->getMessage());
        }
    } else {
        $articles = xds_defaults('page_articles');
        foreach ($articles as &$a) {
            $a['is_active'] = 1;
        }
        unset($a);
        $faqs = xds_defaults('page_faq');
        foreach ($faqs as &$f) {
            $f['is_active'] = 1;
        }
        unset($f);
        $users = [
            ['id' => 1, 'full_name' => 'XDS System Administrator', 'email' => 'admin@xds.co.zw', 'role' => 'admin', 'organization' => 'Expert Decision Systems Zimbabwe', 'is_active' => 1, 'last_login_at' => null],
            ['id' => 2, 'full_name' => 'Credit Subscriber Demo', 'email' => 'client@xds.co.zw', 'role' => 'credit_user', 'organization' => 'UNTU Microfinance', 'is_active' => 1, 'last_login_at' => null],
        ];
    }

    $newInquiries = count(array_filter($inquiries, static fn($i) => ($i['status'] ?? '') === 'new'));
    $activeArticles = count(array_filter($articles, static fn($a) => (int)($a['is_active'] ?? 1) === 1));
    $activeFaqs = count(array_filter($faqs, static fn($f) => (int)($f['is_active'] ?? 1) === 1));

    xds_json_response([
        'ok'           => true,
        'db_connected' => $pdo !== null,
        'user'         => xds_current_user(),
        'asset_urls'   => [
            'system'    => ASSETS_SYSTEM_URL,
            'images'    => ASSETS_IMAGES_URL,
            'articles'  => ASSETS_ARTICLES_URL,
            'documents' => ASSETS_DOCUMENTS_URL,
        ],
        'kpis' => [
            'managed_pages'   => 9,
            'articles_total'  => count($articles),
            'articles_active' => $activeArticles,
            'faqs_total'      => count($faqs),
            'faqs_active'     => $activeFaqs,
            'inquiries_total' => count($inquiries),
            'inquiries_new'   => $newInquiries,
            'users_total'     => count($users),
        ],
        'pages' => [
            'system'         => $system,
            'home'           => $home,
            'about'          => $about,
            'credit'         => $credit,
            'agricultural'   => $agri,
            'authentication' => $auth,
            'privacy'        => $priv,
            'contact'        => $cont,
        ],
        'articles'  => $articles,
        'faqs'      => $faqs,
        'inquiries' => $inquiries,
        'users'     => $users,
        'activity'  => $activity,
    ]);
}

// -----------------------------------------------------------------------------
// POST Mutations (CSRF Protected)
// -----------------------------------------------------------------------------
if ($method !== 'POST') {
    xds_json_response(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$input     = xds_cms_input();
$csrfToken = $input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
if (!xds_verify_csrf(is_string($csrfToken) ? $csrfToken : null)) {
    xds_json_response(['ok' => false, 'message' => 'Invalid or expired CSRF token. Refresh the CMS page.'], 403);
}

$action = strtolower(trim((string)($input['action'] ?? ($_GET['action'] ?? ''))));

// -----------------------------------------------------------------------------
// Asset Upload (strict folder conventions, returns filename only)
// -----------------------------------------------------------------------------
if ($action === 'upload_asset') {
    if (empty($_FILES['file']) || ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        xds_json_response(['ok' => false, 'message' => 'No valid file uploaded.'], 400);
    }

    $target = strtolower(trim((string)($input['target'] ?? 'image')));
    $file   = $_FILES['file'];
    if (($file['size'] ?? 0) > 8 * 1024 * 1024) {
        xds_json_response(['ok' => false, 'message' => 'File exceeds 8 MB maximum size.'], 422);
    }

    $origName = (string)($file['name'] ?? 'upload');
    $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $baseStem = xds_sanitize_slug(pathinfo($origName, PATHINFO_FILENAME)) ?: 'asset';
    $filename = $baseStem . '.' . $ext;

    $allowedImages = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
    $allowedDocs   = ['pdf'];

    if ($target === 'document') {
        if (!in_array($ext, $allowedDocs, true)) {
            xds_json_response(['ok' => false, 'message' => 'Documents must be PDF files (.pdf).'], 422);
        }
        $purpose   = xds_sanitize_slug((string)($input['purpose'] ?? 'policy')) ?: 'policy';
        $docFolder = xds_sanitize_slug((string)($input['doc_folder'] ?? 'xds-information-security-policy')) ?: 'document';
        $destDir   = ROOT_DIR . '/assets/documents/' . $purpose . '/' . $docFolder;
        $previewUrl = xds_document_asset($purpose, $docFolder, $filename);
    } else {
        if (!in_array($ext, $allowedImages, true)) {
            xds_json_response(['ok' => false, 'message' => 'Images must be PNG, JPG, WEBP, or SVG.'], 422);
        }
        if ($target === 'system') {
            $destDir    = ROOT_DIR . '/assets/images/system';
            $previewUrl = xds_system_asset($filename);
        } elseif ($target === 'article') {
            $slug = xds_sanitize_slug((string)($input['slug'] ?? ''));
            if ($slug === '') {
                xds_json_response(['ok' => false, 'message' => 'Enter an article slug before uploading a cover image.'], 422);
            }
            $destDir    = ROOT_DIR . '/assets/images/articles/' . $slug;
            $previewUrl = xds_article_asset($slug, $filename);
        } else {
            $destDir    = ROOT_DIR . '/assets/images';
            $previewUrl = xds_image_asset($filename);
        }
    }

    if (!is_dir($destDir) && !mkdir($destDir, 0775, true) && !is_dir($destDir)) {
        xds_json_response(['ok' => false, 'message' => 'Could not create target asset folder on disk.'], 500);
    }

    $destPath = $destDir . '/' . $filename;
    if (!move_uploaded_file((string)$file['tmp_name'], $destPath)) {
        xds_json_response(['ok' => false, 'message' => 'Failed to save uploaded file. Check directory permissions.'], 500);
    }

    xds_log_cms_activity('Media', 'upload', "Uploaded {$filename} to {$target}");

    xds_json_response([
        'ok'          => true,
        'filename'    => $filename,
        'preview_url' => $previewUrl,
        'message'     => "Uploaded {$filename}. Save the form to persist the filename to MySQL.",
    ]);
}

// Every remaining action requires a live MySQL connection
$db = xds_require_db_or_fail($pdo);

try {
    switch ($action) {
        // ---------------------------------------------------------------------
        // 1. Site System Settings
        // ---------------------------------------------------------------------
        case 'save_system':
            $stmt = $db->prepare(
                'UPDATE `site_system` SET
                    `site_name` = :site_name,
                    `site_tagline` = :site_tagline,
                    `logo_file` = :logo_file,
                    `favicon_file` = :favicon_file,
                    `apple_touch_icon_file` = :apple_icon,
                    `primary_email` = :email,
                    `primary_phone` = :p1,
                    `secondary_phone` = :p2,
                    `tertiary_phone` = :p3,
                    `address_line` = :addr,
                    `operating_hours_weekday` = :hw,
                    `operating_hours_weekend` = :hwe,
                    `credit_portal_url` = :cportal,
                    `auth_portal_url` = :aportal,
                    `social_links_json` = :social,
                    `regional_links_json` = :regional,
                    `copyright_text` = :copy
                 WHERE `id` = 1'
            );
            $stmt->execute([
                ':site_name'    => trim((string)($input['site_name'] ?? 'Expert Decision Systems Zimbabwe (XDS)')),
                ':site_tagline' => trim((string)($input['site_tagline'] ?? '')),
                ':logo_file'    => xds_sanitize_filename((string)($input['logo_file'] ?? 'logo.png')) ?: 'logo.png',
                ':favicon_file' => xds_sanitize_filename((string)($input['favicon_file'] ?? 'favicon.png')) ?: 'favicon.png',
                ':apple_icon'   => xds_sanitize_filename((string)($input['apple_touch_icon_file'] ?? 'apple-touch-icon.png')) ?: 'apple-touch-icon.png',
                ':email'        => trim((string)($input['primary_email'] ?? 'info@xds.co.zw')),
                ':p1'           => trim((string)($input['primary_phone'] ?? '')),
                ':p2'           => trim((string)($input['secondary_phone'] ?? '')),
                ':p3'           => trim((string)($input['tertiary_phone'] ?? '')),
                ':addr'         => trim((string)($input['address_line'] ?? '')),
                ':hw'           => trim((string)($input['operating_hours_weekday'] ?? '')),
                ':hwe'          => trim((string)($input['operating_hours_weekend'] ?? '')),
                ':cportal'      => trim((string)($input['credit_portal_url'] ?? 'portal/')),
                ':aportal'      => trim((string)($input['auth_portal_url'] ?? 'https://www.xds.co.zw/echeck/')),
                ':social'       => json_encode($input['social_links'] ?? [], JSON_UNESCAPED_SLASHES),
                ':regional'     => json_encode($input['regional_links'] ?? [], JSON_UNESCAPED_SLASHES),
                ':copy'         => trim((string)($input['copyright_text'] ?? '')),
            ]);
            xds_log_cms_activity('Site Settings', 'update', 'Updated global brand, portal URLs & footer settings');
            xds_json_response(['ok' => true, 'message' => 'Site settings saved and live on the public website.']);

        // ---------------------------------------------------------------------
        // 2. Home Page (`page_home`)
        // ---------------------------------------------------------------------
        case 'save_home':
            $slides   = is_array($input['hero_slides'] ?? null) ? $input['hero_slides'] : [];
            $services = is_array($input['services'] ?? null) ? $input['services'] : [];
            // Ensure service badge filenames stay filename-only
            foreach ($services as &$svc) {
                $bf = trim((string)($svc['badge_file'] ?? ''));
                $svc['badge_file'] = $bf !== '' ? xds_sanitize_filename($bf) : null;
            }
            unset($svc);

            $stmt = $db->prepare(
                'UPDATE `page_home` SET
                    `hero_badge` = :hbadge,
                    `hero_headline` = :hhead,
                    `hero_subheadline` = :hsub,
                    `hero_image_file` = :himg,
                    `hero_slides_json` = :hslides,
                    `intro_title` = :ititle,
                    `intro_subtitle` = :isub,
                    `intro_body` = :ibody,
                    `services_heading` = :shead,
                    `services_subheading` = :ssub,
                    `services_json` = :sjson,
                    `cta_banner_title` = :ctatitle,
                    `cta_banner_copy` = :ctacopy,
                    `cta_banner_button` = :ctabtn,
                    `meta_title` = :mtitle,
                    `meta_description` = :mdesc,
                    `og_image_file` = :ogimg
                 WHERE `id` = 1'
            );
            $heroImg = xds_sanitize_filename((string)($input['hero_image_file'] ?? 'hero-home.webp')) ?: 'hero-home.webp';
            $stmt->execute([
                ':hbadge'   => trim((string)($input['hero_badge'] ?? '')),
                ':hhead'    => trim((string)($input['hero_headline'] ?? '')),
                ':hsub'     => trim((string)($input['hero_subheadline'] ?? '')),
                ':himg'     => $heroImg,
                ':hslides'  => json_encode($slides, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ':ititle'   => trim((string)($input['intro_title'] ?? '')),
                ':isub'     => trim((string)($input['intro_subtitle'] ?? '')),
                ':ibody'    => trim((string)($input['intro_body'] ?? '')),
                ':shead'    => trim((string)($input['services_heading'] ?? '')),
                ':ssub'     => trim((string)($input['services_subheading'] ?? '')),
                ':sjson'    => json_encode($services, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ':ctatitle' => trim((string)($input['cta_banner_title'] ?? '')),
                ':ctacopy'  => trim((string)($input['cta_banner_copy'] ?? '')),
                ':ctabtn'   => trim((string)($input['cta_banner_button'] ?? '')),
                ':mtitle'   => trim((string)($input['meta_title'] ?? '')),
                ':mdesc'    => trim((string)($input['meta_description'] ?? '')),
                ':ogimg'    => xds_sanitize_filename((string)($input['og_image_file'] ?? $heroImg)) ?: $heroImg,
            ]);
            xds_log_cms_activity('Home Page', 'update', 'Updated Home hero carousel, intro & services');
            xds_json_response(['ok' => true, 'message' => 'Home page updated live.']);

        // ---------------------------------------------------------------------
        // 3. About & Why XDS (`page_about`)
        // ---------------------------------------------------------------------
        case 'save_about':
            $banner = xds_sanitize_filename((string)($input['banner_image_file'] ?? 'banner-about.webp')) ?: 'banner-about.webp';
            $stmt = $db->prepare(
                'UPDATE `page_about` SET
                    `banner_image_file` = :banner,
                    `overview_title` = :otitle,
                    `overview_subtitle` = :osub,
                    `history_paragraphs_json` = :hist,
                    `footprint_stats_json` = :stats,
                    `why_title` = :wtitle,
                    `philosophy_title` = :ptitle,
                    `philosophy_body` = :pbody,
                    `people_title` = :petitle,
                    `people_body` = :pebody,
                    `value_drivers_title` = :vdtitle,
                    `value_drivers_json` = :vdjson,
                    `meta_title` = :mtitle,
                    `meta_description` = :mdesc,
                    `og_image_file` = :ogimg
                 WHERE `id` = 1'
            );
            $stmt->execute([
                ':banner'  => $banner,
                ':otitle'  => trim((string)($input['overview_title'] ?? '')),
                ':osub'    => trim((string)($input['overview_subtitle'] ?? '')),
                ':hist'    => json_encode($input['history_paragraphs'] ?? [], JSON_UNESCAPED_UNICODE),
                ':stats'   => json_encode($input['footprint_stats'] ?? [], JSON_UNESCAPED_UNICODE),
                ':wtitle'  => trim((string)($input['why_title'] ?? '')),
                ':ptitle'  => trim((string)($input['philosophy_title'] ?? '')),
                ':pbody'   => trim((string)($input['philosophy_body'] ?? '')),
                ':petitle' => trim((string)($input['people_title'] ?? '')),
                ':pebody'  => trim((string)($input['people_body'] ?? '')),
                ':vdtitle' => trim((string)($input['value_drivers_title'] ?? '')),
                ':vdjson'  => json_encode($input['value_drivers'] ?? [], JSON_UNESCAPED_UNICODE),
                ':mtitle'  => trim((string)($input['meta_title'] ?? '')),
                ':mdesc'   => trim((string)($input['meta_description'] ?? '')),
                ':ogimg'   => $banner,
            ]);
            xds_log_cms_activity('About & Why XDS', 'update', 'Updated About page history, philosophy & value drivers');
            xds_json_response(['ok' => true, 'message' => 'About & Why XDS page updated live.']);

        // ---------------------------------------------------------------------
        // 4. Credit Information Services (`page_credit_services`)
        // ---------------------------------------------------------------------
        case 'save_credit':
            $banner   = xds_sanitize_filename((string)($input['banner_image_file'] ?? 'banner-credit.webp')) ?: 'banner-credit.webp';
            $partners = is_array($input['partners'] ?? null) ? $input['partners'] : [];
            foreach ($partners as &$p) {
                $p['logo_file'] = xds_sanitize_filename((string)($p['logo_file'] ?? ''));
            }
            unset($p);

            $stmt = $db->prepare(
                'UPDATE `page_credit_services` SET
                    `banner_image_file` = :banner,
                    `page_title` = :ptitle,
                    `overview_copy` = :overview,
                    `offerings_json` = :offerings,
                    `signup_cta_label` = :slabel,
                    `report_cta_label` = :rlabel,
                    `partners_heading` = :phead,
                    `partners_json` = :partners,
                    `meta_title` = :mtitle,
                    `meta_description` = :mdesc,
                    `og_image_file` = :ogimg
                 WHERE `id` = 1'
            );
            $stmt->execute([
                ':banner'    => $banner,
                ':ptitle'    => trim((string)($input['page_title'] ?? '')),
                ':overview'  => trim((string)($input['overview_copy'] ?? '')),
                ':offerings' => json_encode($input['offerings'] ?? [], JSON_UNESCAPED_UNICODE),
                ':slabel'    => trim((string)($input['signup_cta_label'] ?? 'Sign up')),
                ':rlabel'    => trim((string)($input['report_cta_label'] ?? 'Request My Report')),
                ':phead'     => trim((string)($input['partners_heading'] ?? 'Our Partners')),
                ':partners'  => json_encode($partners, JSON_UNESCAPED_UNICODE),
                ':mtitle'    => trim((string)($input['meta_title'] ?? '')),
                ':mdesc'     => trim((string)($input['meta_description'] ?? '')),
                ':ogimg'     => $banner,
            ]);
            xds_log_cms_activity('Credit Services', 'update', 'Updated Credit Information Services offerings & partners');
            xds_json_response(['ok' => true, 'message' => 'Credit Information Services page updated live.']);

        // ---------------------------------------------------------------------
        // 5. Agricultural Services (`page_agricultural_services`)
        // ---------------------------------------------------------------------
        case 'save_agricultural':
            $banner  = xds_sanitize_filename((string)($input['banner_image_file'] ?? 'banner-agricultural.webp')) ?: 'banner-agricultural.webp';
            $diagram = xds_sanitize_filename((string)($input['diagram_image_file'] ?? 'diagram-agricultural.webp')) ?: 'diagram-agricultural.webp';

            $stmt = $db->prepare(
                'UPDATE `page_agricultural_services` SET
                    `banner_image_file` = :banner,
                    `diagram_image_file` = :diagram,
                    `page_title` = :ptitle,
                    `intro_paragraph` = :p1,
                    `scoring_paragraph` = :p2,
                    `risk_paragraph` = :p3,
                    `features_json` = :features,
                    `pillars_json` = :pillars,
                    `meta_title` = :mtitle,
                    `meta_description` = :mdesc,
                    `og_image_file` = :ogimg
                 WHERE `id` = 1'
            );
            $stmt->execute([
                ':banner'   => $banner,
                ':diagram'  => $diagram,
                ':ptitle'   => trim((string)($input['page_title'] ?? '')),
                ':p1'       => trim((string)($input['intro_paragraph'] ?? '')),
                ':p2'       => trim((string)($input['scoring_paragraph'] ?? '')),
                ':p3'       => trim((string)($input['risk_paragraph'] ?? '')),
                ':features' => json_encode($input['features'] ?? [], JSON_UNESCAPED_UNICODE),
                ':pillars'  => json_encode($input['pillars'] ?? [], JSON_UNESCAPED_UNICODE),
                ':mtitle'   => trim((string)($input['meta_title'] ?? '')),
                ':mdesc'    => trim((string)($input['meta_description'] ?? '')),
                ':ogimg'    => $banner,
            ]);
            xds_log_cms_activity('Agricultural Services', 'update', 'Updated Agricultural Services copy, pillars & capabilities');
            xds_json_response(['ok' => true, 'message' => 'Agricultural Services page updated live.']);

        // ---------------------------------------------------------------------
        // 6. Authentication Services (`page_authentication_services`)
        // ---------------------------------------------------------------------
        case 'save_authentication':
            $banner   = xds_sanitize_filename((string)($input['banner_image_file'] ?? 'banner-authentication.webp')) ?: 'banner-authentication.webp';
            $badge    = xds_sanitize_filename((string)($input['echeck_badge_file'] ?? 'badge-echeck.png')) ?: 'badge-echeck.png';
            $partners = is_array($input['partners'] ?? null) ? $input['partners'] : [];
            foreach ($partners as &$p) {
                $p['logo_file'] = xds_sanitize_filename((string)($p['logo_file'] ?? ''));
            }
            unset($p);

            $stmt = $db->prepare(
                'UPDATE `page_authentication_services` SET
                    `banner_image_file` = :banner,
                    `echeck_badge_file` = :badge,
                    `page_title` = :ptitle,
                    `overview_copy` = :overview,
                    `checks_json` = :checks,
                    `partners_heading` = :phead,
                    `partners_json` = :partners,
                    `meta_title` = :mtitle,
                    `meta_description` = :mdesc,
                    `og_image_file` = :ogimg
                 WHERE `id` = 1'
            );
            $stmt->execute([
                ':banner'   => $banner,
                ':badge'    => $badge,
                ':ptitle'   => trim((string)($input['page_title'] ?? '')),
                ':overview' => trim((string)($input['overview_copy'] ?? '')),
                ':checks'   => json_encode($input['checks'] ?? [], JSON_UNESCAPED_UNICODE),
                ':phead'    => trim((string)($input['partners_heading'] ?? 'Our Partners')),
                ':partners' => json_encode($partners, JSON_UNESCAPED_UNICODE),
                ':mtitle'   => trim((string)($input['meta_title'] ?? '')),
                ':mdesc'    => trim((string)($input['meta_description'] ?? '')),
                ':ogimg'    => $banner,
            ]);
            xds_log_cms_activity('Authentication Services', 'update', 'Updated E Check verification modules & partners');
            xds_json_response(['ok' => true, 'message' => 'Authentication Services page updated live.']);

        // ---------------------------------------------------------------------
        // 7. Data Privacy (ISSP) (`page_data_privacy`)
        // ---------------------------------------------------------------------
        case 'save_privacy':
            $banner    = xds_sanitize_filename((string)($input['banner_image_file'] ?? 'banner-data-privacy.webp')) ?: 'banner-data-privacy.webp';
            $docFile   = xds_sanitize_filename((string)($input['policy_doc_file'] ?? 'xds-issp-2026.pdf')) ?: 'xds-issp-2026.pdf';
            $docPurp   = xds_sanitize_slug((string)($input['policy_doc_purpose'] ?? 'policy')) ?: 'policy';
            $docFolder = xds_sanitize_slug((string)($input['policy_doc_folder'] ?? 'xds-information-security-policy')) ?: 'xds-information-security-policy';

            $stmt = $db->prepare(
                'UPDATE `page_data_privacy` SET
                    `banner_image_file` = :banner,
                    `page_title` = :ptitle,
                    `intro_copy` = :intro,
                    `mission_title` = :mtitle_s,
                    `mission_intro` = :mintro,
                    `mission_bullets_json` = :mbullets,
                    `philosophy_title` = :phtitle,
                    `philosophy_body` = :phbody,
                    `objectives_title` = :otitle,
                    `objectives_bullets_json` = :obullets,
                    `scope_title` = :stitle,
                    `scope_paragraphs_json` = :sparams,
                    `policy_doc_purpose` = :dpurp,
                    `policy_doc_folder` = :dfolder,
                    `policy_doc_file` = :dfile,
                    `meta_title` = :mtitle,
                    `meta_description` = :mdesc,
                    `og_image_file` = :ogimg
                 WHERE `id` = 1'
            );
            $stmt->execute([
                ':banner'   => $banner,
                ':ptitle'   => trim((string)($input['page_title'] ?? '')),
                ':intro'    => trim((string)($input['intro_copy'] ?? '')),
                ':mtitle_s' => trim((string)($input['mission_title'] ?? '')),
                ':mintro'   => trim((string)($input['mission_intro'] ?? '')),
                ':mbullets' => json_encode($input['mission_bullets'] ?? [], JSON_UNESCAPED_UNICODE),
                ':phtitle'  => trim((string)($input['philosophy_title'] ?? '')),
                ':phbody'   => trim((string)($input['philosophy_body'] ?? '')),
                ':otitle'   => trim((string)($input['objectives_title'] ?? '')),
                ':obullets' => json_encode($input['objectives_bullets'] ?? [], JSON_UNESCAPED_UNICODE),
                ':stitle'   => trim((string)($input['scope_title'] ?? '')),
                ':sparams'  => json_encode($input['scope_paragraphs'] ?? [], JSON_UNESCAPED_UNICODE),
                ':dpurp'    => $docPurp,
                ':dfolder'  => $docFolder,
                ':dfile'    => $docFile,
                ':mtitle'   => trim((string)($input['meta_title'] ?? '')),
                ':mdesc'    => trim((string)($input['meta_description'] ?? '')),
                ':ogimg'    => $banner,
            ]);
            xds_log_cms_activity('Data Privacy', 'update', 'Updated ISSP policy sections & downloadable document');
            xds_json_response(['ok' => true, 'message' => 'Data Privacy (ISSP) page updated live.']);

        // ---------------------------------------------------------------------
        // 8. Contact Page Settings (`page_contact`)
        // ---------------------------------------------------------------------
        case 'save_contact':
            $stmt = $db->prepare(
                'UPDATE `page_contact` SET
                    `page_title` = :ptitle,
                    `heading` = :heading,
                    `intro_copy` = :intro,
                    `office_title` = :otitle,
                    `address_line1` = :a1,
                    `address_line2` = :a2,
                    `city_country` = :cc,
                    `phone_1` = :p1,
                    `phone_2` = :p2,
                    `phone_3` = :p3,
                    `email` = :email,
                    `hours_weekday` = :hw,
                    `hours_weekend` = :hwe,
                    `map_embed_url` = :map,
                    `inquiry_needs_json` = :needs,
                    `meta_title` = :mtitle,
                    `meta_description` = :mdesc
                 WHERE `id` = 1'
            );
            $stmt->execute([
                ':ptitle'  => trim((string)($input['page_title'] ?? 'Contact')),
                ':heading' => trim((string)($input['heading'] ?? 'Get in Touch')),
                ':intro'   => trim((string)($input['intro_copy'] ?? '')),
                ':otitle'  => trim((string)($input['office_title'] ?? 'Our Office Info')),
                ':a1'      => trim((string)($input['address_line1'] ?? '')),
                ':a2'      => trim((string)($input['address_line2'] ?? '')),
                ':cc'      => trim((string)($input['city_country'] ?? '')),
                ':p1'      => trim((string)($input['phone_1'] ?? '')),
                ':p2'      => trim((string)($input['phone_2'] ?? '')),
                ':p3'      => trim((string)($input['phone_3'] ?? '')),
                ':email'   => trim((string)($input['email'] ?? 'info@xds.co.zw')),
                ':hw'      => trim((string)($input['hours_weekday'] ?? '')),
                ':hwe'     => trim((string)($input['hours_weekend'] ?? '')),
                ':map'     => trim((string)($input['map_embed_url'] ?? '')),
                ':needs'   => json_encode($input['inquiry_needs'] ?? [], JSON_UNESCAPED_UNICODE),
                ':mtitle'  => trim((string)($input['meta_title'] ?? '')),
                ':mdesc'   => trim((string)($input['meta_description'] ?? '')),
            ]);
            xds_log_cms_activity('Contact Page', 'update', 'Updated Harare office info, map & enquiry topics');
            xds_json_response(['ok' => true, 'message' => 'Contact page details updated live.']);

        // ---------------------------------------------------------------------
        // 9. Articles CRUD (`page_articles`)
        //    - Slug auto-generated from title
        //    - Meta Title always `{title} | XDS Zimbabwe`
        //    - Summary & Meta Description auto-generated from 1st paragraph (<= 250 chars)
        // ---------------------------------------------------------------------
        case 'save_article':
            $id    = (int)($input['id'] ?? 0);
            $title = trim((string)($input['title'] ?? ''));
            $slug  = xds_sanitize_slug($title);
            if ($title === '' || $slug === '') {
                xds_json_response(['ok' => false, 'message' => 'Article title is required.'], 422);
            }
            $body = trim((string)($input['body_content'] ?? ''));
            if (trim(strip_tags($body)) === '') {
                xds_json_response(['ok' => false, 'message' => 'Article content cannot be empty.'], 422);
            }

            $imgFile = xds_sanitize_filename((string)($input['image_file'] ?? 'cover.webp')) ?: 'cover.webp';
            $pubDate = trim((string)($input['published_date'] ?? date('Y-m-d')));
            $cat     = trim((string)($input['category'] ?? 'Bureau Insights'));
            $author  = trim((string)($input['author'] ?? 'XDS Research Desk'));
            $readMin = max(1, (int)($input['read_time_minutes'] ?? 4));
            $sort    = max(0, (int)($input['sort_order'] ?? 0));
            $active  = !empty($input['is_active']) ? 1 : 0;

            // Enforce auto-generated SEO & Summary rules
            $summary = xds_extract_first_paragraph_summary($body, 250);
            $mTitle  = mb_substr($title . ' | XDS Zimbabwe', 0, 160, 'UTF-8');
            $mDesc   = $summary;

            if ($id > 0) {
                // Preserve existing cover folder if title/slug changed
                $oldStmt = $db->prepare('SELECT `slug` FROM `page_articles` WHERE `id` = :id LIMIT 1');
                $oldStmt->execute([':id' => $id]);
                $oldSlug = (string)($oldStmt->fetchColumn() ?: '');

                $stmt = $db->prepare(
                    'UPDATE `page_articles` SET
                        `slug` = :slug, `title` = :title, `summary` = :summary, `body_content` = :body,
                        `image_file` = :img, `published_date` = :pdate, `author` = :author,
                        `category` = :cat, `read_time_minutes` = :rmin, `sort_order` = :sort,
                        `is_active` = :active, `meta_title` = :mtitle, `meta_description` = :mdesc
                     WHERE `id` = :id'
                );
                $stmt->execute([
                    ':slug' => $slug, ':title' => $title, ':summary' => $summary, ':body' => $body,
                    ':img' => $imgFile, ':pdate' => $pubDate, ':author' => $author,
                    ':cat' => $cat, ':rmin' => $readMin, ':sort' => $sort,
                    ':active' => $active, ':mtitle' => $mTitle, ':mdesc' => $mDesc, ':id' => $id,
                ]);

                if ($oldSlug !== '' && $oldSlug !== $slug) {
                    $oldDir = ROOT_DIR . '/assets/images/articles/' . $oldSlug;
                    $newDir = ROOT_DIR . '/assets/images/articles/' . $slug;
                    if (is_dir($oldDir) && !is_dir($newDir)) {
                        @rename($oldDir, $newDir);
                    }
                }

                xds_log_cms_activity('Articles', 'update', "Updated article: {$title}");
                xds_json_response([
                    'ok'               => true,
                    'id'               => $id,
                    'slug'             => $slug,
                    'summary'          => $summary,
                    'meta_title'       => $mTitle,
                    'meta_description' => $mDesc,
                    'message'          => 'Article updated and live on the website.',
                ]);
            } else {
                // Ensure unique slug on create if duplicate title exists
                $checkSlug = $slug;
                $suffix = 2;
                while (true) {
                    $chk = $db->prepare('SELECT COUNT(*) FROM `page_articles` WHERE `slug` = :s');
                    $chk->execute([':s' => $checkSlug]);
                    if ((int)$chk->fetchColumn() === 0) {
                        $slug = $checkSlug;
                        break;
                    }
                    $checkSlug = $slug . '-' . $suffix;
                    $suffix++;
                }

                $stmt = $db->prepare(
                    'INSERT INTO `page_articles`
                        (`slug`, `title`, `summary`, `body_content`, `image_file`, `banner_image_file`,
                         `published_date`, `author`, `category`, `read_time_minutes`, `sort_order`,
                         `is_active`, `meta_title`, `meta_description`)
                     VALUES
                        (:slug, :title, :summary, :body, :img, "banner-articles.webp",
                         :pdate, :author, :cat, :rmin, :sort, :active, :mtitle, :mdesc)'
                );
                $stmt->execute([
                    ':slug' => $slug, ':title' => $title, ':summary' => $summary, ':body' => $body,
                    ':img' => $imgFile, ':pdate' => $pubDate, ':author' => $author,
                    ':cat' => $cat, ':rmin' => $readMin, ':sort' => $sort,
                    ':active' => $active, ':mtitle' => $mTitle, ':mdesc' => $mDesc,
                ]);
                $newId = (int)$db->lastInsertId();
                xds_log_cms_activity('Articles', 'create', "Published new article: {$title}");
                xds_json_response([
                    'ok'               => true,
                    'id'               => $newId,
                    'slug'             => $slug,
                    'summary'          => $summary,
                    'meta_title'       => $mTitle,
                    'meta_description' => $mDesc,
                    'message'          => 'Article created and published live.',
                ]);
            }

        case 'delete_article':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) xds_json_response(['ok' => false, 'message' => 'Invalid article ID.'], 422);
            $db->prepare('DELETE FROM `page_articles` WHERE `id` = :id')->execute([':id' => $id]);
            xds_log_cms_activity('Articles', 'delete', "Deleted article #{$id}");
            xds_json_response(['ok' => true, 'message' => 'Article deleted.']);

        // ---------------------------------------------------------------------
        // 10. FAQs CRUD (`page_faq`)
        // ---------------------------------------------------------------------
        case 'save_faq':
            $id       = (int)($input['id'] ?? 0);
            $question = trim((string)($input['question'] ?? ''));
            $answer   = trim((string)($input['answer'] ?? ''));
            $cat      = trim((string)($input['category'] ?? 'General'));
            $icon     = preg_replace('/[^a-z0-9-]/i', '', (string)($input['icon_key'] ?? 'help-circle')) ?: 'help-circle';
            $sort     = max(0, (int)($input['sort_order'] ?? 0));
            $active   = !empty($input['is_active']) ? 1 : 0;

            if ($question === '' || $answer === '') {
                xds_json_response(['ok' => false, 'message' => 'Question and answer are required.'], 422);
            }

            if ($id > 0) {
                $stmt = $db->prepare(
                    'UPDATE `page_faq` SET `question` = :q, `answer` = :a, `category` = :cat,
                     `icon_key` = :icon, `sort_order` = :sort, `is_active` = :active WHERE `id` = :id'
                );
                $stmt->execute([':q' => $question, ':a' => $answer, ':cat' => $cat, ':icon' => $icon, ':sort' => $sort, ':active' => $active, ':id' => $id]);
                xds_log_cms_activity('FAQs', 'update', "Updated FAQ #{$id}");
                xds_json_response(['ok' => true, 'id' => $id, 'message' => 'FAQ updated live.']);
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO `page_faq` (`banner_image_file`, `question`, `answer`, `category`, `icon_key`, `sort_order`, `is_active`)
                     VALUES ("banner-faq.webp", :q, :a, :cat, :icon, :sort, :active)'
                );
                $stmt->execute([':q' => $question, ':a' => $answer, ':cat' => $cat, ':icon' => $icon, ':sort' => $sort, ':active' => $active]);
                $newId = (int)$db->lastInsertId();
                xds_log_cms_activity('FAQs', 'create', "Added FAQ: " . substr($question, 0, 60));
                xds_json_response(['ok' => true, 'id' => $newId, 'message' => 'FAQ added live.']);
            }

        case 'delete_faq':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) xds_json_response(['ok' => false, 'message' => 'Invalid FAQ ID.'], 422);
            $db->prepare('DELETE FROM `page_faq` WHERE `id` = :id')->execute([':id' => $id]);
            xds_log_cms_activity('FAQs', 'delete', "Deleted FAQ #{$id}");
            xds_json_response(['ok' => true, 'message' => 'FAQ deleted.']);

        // ---------------------------------------------------------------------
        // 11. Contact Inquiries Management (`contact_inquiries`)
        // ---------------------------------------------------------------------
        case 'update_inquiry_status':
            $id     = (int)($input['id'] ?? 0);
            $status = (string)($input['status'] ?? '');
            if ($id <= 0 || !in_array($status, ['new', 'in_review', 'responded', 'archived'], true)) {
                xds_json_response(['ok' => false, 'message' => 'Invalid inquiry ID or status.'], 422);
            }
            $db->prepare('UPDATE `contact_inquiries` SET `status` = :st WHERE `id` = :id')->execute([':st' => $status, ':id' => $id]);
            xds_log_cms_activity('Inquiries', 'status', "Marked inquiry #{$id} as {$status}");
            xds_json_response(['ok' => true, 'message' => "Inquiry status updated to {$status}."]);

        case 'delete_inquiry':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) xds_json_response(['ok' => false, 'message' => 'Invalid inquiry ID.'], 422);
            $db->prepare('DELETE FROM `contact_inquiries` WHERE `id` = :id')->execute([':id' => $id]);
            xds_log_cms_activity('Inquiries', 'delete', "Deleted inquiry #{$id}");
            xds_json_response(['ok' => true, 'message' => 'Inquiry deleted.']);

        // ---------------------------------------------------------------------
        // 12. Portal Users CRUD (`portal_users`)
        // ---------------------------------------------------------------------
        case 'save_user':
            $id       = (int)($input['id'] ?? 0);
            $fullName = trim((string)($input['full_name'] ?? ''));
            $email    = strtolower(trim((string)($input['email'] ?? '')));
            $role     = (string)($input['role'] ?? 'credit_user');
            $org      = trim((string)($input['organization'] ?? ''));
            $active   = !empty($input['is_active']) ? 1 : 0;
            $password = (string)($input['password'] ?? '');

            if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                xds_json_response(['ok' => false, 'message' => 'Valid full name and email address are required.'], 422);
            }
            if (!in_array($role, ['admin', 'editor', 'credit_user'], true)) {
                xds_json_response(['ok' => false, 'message' => 'Invalid user role.'], 422);
            }

            $me = xds_current_user();
            if ($id > 0 && $me && $id === (int)$me['id'] && ($active === 0 || $role === 'credit_user')) {
                xds_json_response(['ok' => false, 'message' => 'You cannot deactivate or demote your own active admin account.'], 422);
            }

            if ($id > 0) {
                if ($password !== '') {
                    if (strlen($password) < 8) {
                        xds_json_response(['ok' => false, 'message' => 'New password must be at least 8 characters.'], 422);
                    }
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare(
                        'UPDATE `portal_users` SET `full_name` = :fn, `email` = :em, `password_hash` = :pw,
                         `role` = :rl, `organization` = :org, `is_active` = :act WHERE `id` = :id'
                    );
                    $stmt->execute([':fn' => $fullName, ':em' => $email, ':pw' => $hash, ':rl' => $role, ':org' => $org ?: null, ':act' => $active, ':id' => $id]);
                } else {
                    $stmt = $db->prepare(
                        'UPDATE `portal_users` SET `full_name` = :fn, `email` = :em,
                         `role` = :rl, `organization` = :org, `is_active` = :act WHERE `id` = :id'
                    );
                    $stmt->execute([':fn' => $fullName, ':em' => $email, ':rl' => $role, ':org' => $org ?: null, ':act' => $active, ':id' => $id]);
                }
                xds_log_cms_activity('Users', 'update', "Updated account {$email} ({$role})");
                xds_json_response(['ok' => true, 'id' => $id, 'message' => 'Portal user updated.']);
            } else {
                if (strlen($password) < 8) {
                    xds_json_response(['ok' => false, 'message' => 'Password must be at least 8 characters for new users.'], 422);
                }
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare(
                    'INSERT INTO `portal_users` (`full_name`, `email`, `password_hash`, `role`, `organization`, `is_active`)
                     VALUES (:fn, :em, :pw, :rl, :org, :act)'
                );
                $stmt->execute([':fn' => $fullName, ':em' => $email, ':pw' => $hash, ':rl' => $role, ':org' => $org ?: null, ':act' => $active]);
                $newId = (int)$db->lastInsertId();
                xds_log_cms_activity('Users', 'create', "Created {$role} account: {$email}");
                xds_json_response(['ok' => true, 'id' => $newId, 'message' => 'Portal user created.']);
            }

        case 'delete_user':
            $id = (int)($input['id'] ?? 0);
            $me = xds_current_user();
            if ($id <= 0 || ($me && $id === (int)$me['id'])) {
                xds_json_response(['ok' => false, 'message' => 'Cannot delete this account.'], 422);
            }
            $db->prepare('DELETE FROM `portal_users` WHERE `id` = :id')->execute([':id' => $id]);
            xds_log_cms_activity('Users', 'delete', "Removed user account #{$id}");
            xds_json_response(['ok' => true, 'message' => 'Portal user removed.']);

        default:
            xds_json_response(['ok' => false, 'message' => 'Unknown CMS action.'], 400);
    }
} catch (PDOException $e) {
    if ((string)$e->getCode() === '23000') {
        xds_json_response(['ok' => false, 'message' => 'Duplicate entry (slug or email already exists).'], 409);
    }
    error_log('[XDS CMS Error] ' . $e->getMessage());
    xds_json_response(['ok' => false, 'message' => 'Database error while saving changes.'], 500);
}
