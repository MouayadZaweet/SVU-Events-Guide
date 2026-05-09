<?php
// =====================================
// Initialize System
// =====================================
require_once 'config/db.php';
session_start(); // Default session timeout: 24 minutes (1440 seconds) as per PHP settings

// Set timezone
date_default_timezone_set('Asia/Damascus');

// =====================================
// Language Detection
// =====================================
// Check URL parameter first, then cookie, default to Arabic
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'])) {
    $lang_code = $_GET['lang'];
    setcookie('site_lang', $lang_code, time() + (86400 * 30), '/'); // 30 days
} elseif (isset($_COOKIE['site_lang'])) {
    $lang_code = $_COOKIE['site_lang'];
} else {
    $lang_code = 'ar';
}
$direction = ($lang_code === 'ar') ? 'rtl' : 'ltr';

// Load language file
if ($lang_code === 'en') {
    $lang = require_once 'languages/en.php';
} else {
    $lang = require_once 'languages/ar.php';
}

// =====================================
// Fetch Featured Events from Database
// =====================================
try {
    $stmt = $pdo->query("
        SELECT e.*, 
               c.name_ar AS category_name_ar, c.name_en AS category_name_en,
               l.name_ar AS location_name_ar, l.name_en AS location_name_en
        FROM events e
        LEFT JOIN categories c ON e.category = c.id
        LEFT JOIN locations l ON e.location = l.id
        ORDER BY e.event_date ASC
        LIMIT 3
    ");
    $eventsData = $stmt->fetchAll();
} catch (PDOException $e) {
    $eventsData = [];
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>" dir="<?php echo $direction; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Meta Tags -->
    <meta name="description" content="<?php echo $lang['site_desc']; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- Title & Icon -->
    <title><?php echo $lang['nav_home']; ?> | <?php echo $lang['site_title']; ?></title>
    <link rel="icon" href="assets/img/SVU-Events-icon.png" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/styles.css" />

    <!-- Custom CSS (Page Specific) -->
    <style>
        /* Main Container */
        main {
            flex: 1;
        }

        /* Page Title */
        .page-title {
            text-align: center;
            font-size: 2.5rem;
            margin: 30px 0;
            position: relative;
            padding-bottom: 15px;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 50%;
            transform: translateX(50%);
            width: 150px;
            height: 3px;
            background-color: var(--accent-color);
        }

        /* Developer Section (Collapsible) */
        .collapsible-sections {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0;
        }

        .collapsible-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .collapsible-section:hover {
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .collapsible-header {
            background: linear-gradient(135deg, var(--primary-color), #0a264d);
            color: white;
            padding: 20px 25px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .collapsible-header:hover {
            background: linear-gradient(135deg, #0a264d, var(--primary-color));
        }

        .collapsible-title {
            color: azure;
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .collapsible-icon {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .collapsible-section.active .collapsible-icon {
            transform: rotate(180deg);
        }

        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease, padding 0.3s ease;
            background: var(--background-light);
        }

        .collapsible-section.active .collapsible-content {
            max-height: 1000px;
            padding: 25px;
        }

        /* Developer Card */
        .developer-container {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .developer-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
            justify-content: center;
        }

        .developer-row:last-child {
            margin-bottom: 0;
        }

        .developer-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid var(--accent-color);
            flex: 1;
            min-width: 300px;
            max-width: calc(50% - 10px);
            box-sizing: border-box;
        }

        .developer-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .dev-name {
            font-size: 1.3rem;
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 5px;
        }

        .dev-info {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .dev-role {
            display: inline-block;
            background: var(--accent-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Admin Access Section (Collapsible) */
        /* Red-themed header to indicate danger zone */
        .admin-access-section {
            border: 2px solid #e0c0c0;
            margin: 0 40px 0 40px;
        }

        .admin-access-header {
            background: linear-gradient(135deg, #8b0000, #b22222) !important;
            border-bottom: 3px solid #ff4444 !important;
        }

        .admin-access-header:hover {
            background: linear-gradient(135deg, #b22222, #8b0000) !important;
        }

        .admin-access-header .collapsible-title {
            color: #ffffff !important;
        }

        /* Warning banner */
        .admin-warning {
            background: #fff3f3;
            border: 1px solid #ff4444;
            color: #b22222;
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-warning i {
            font-size: 1.2rem;
        }

        /* Cards grid */
        .admin-cards-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }

        /* Individual card */
        .admin-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
        }

        .admin-card:hover {
            border-color: #b22222;
            box-shadow: 0 3px 12px rgba(178,34,34,0.1);
            transform: translateY(-2px);
        }

        .admin-card-icon {
            width: 45px;
            height: 45px;
            background: #fdf2f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #b22222;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .admin-card-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .admin-card-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .admin-card-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            word-break: break-all;
        }

        /* Security note */
        .admin-security-note {
            background: #f9f9f9;
            border-left: 4px solid #b22222;
            padding: 12px 16px;
            font-size: 0.85rem;
            color: #555;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 0 6px 6px 0;
        }

        .admin-security-note i {
            color: #b22222;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* Admin section starts COLLAPSED by default */
        .admin-access-section .collapsible-content {
            max-height: 0 !important;
            padding: 0 !important;
        }

        .admin-access-section.active .collapsible-content {
            max-height: 1000px !important;
            padding: 25px !important;
        }

        /* Logical Properties for security note in LTR */
        [dir="ltr"] .admin-security-note {
            border-left: none;
            border-right: 4px solid #b22222;
            border-radius: 6px 0 0 6px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-cards-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Hero Slider */
        .hero-slider {
            position: relative;
            height: 500px;
            width: 80%;
            overflow: hidden;
            margin: 20px auto;
            max-width: 1200px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease;
            cursor: pointer;
            z-index: 0;
        }

        .slide.active {
            opacity: 1;
            z-index: 1;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
        }

        .slide-content {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100%;
            padding: 20px;
            background: linear-gradient(to top, rgba(7,26,57,0.8), transparent);
            color: white;
        }

        .slide-date {
            display: table;
            padding: 0.3rem 0.6rem 0.1rem 0.6rem;
            font-size: 0.9rem;
            color: #ffffff;
            border-radius: 10px;
            background-color: rgba(7, 26, 57, 0.8);
            margin-bottom: 5px;
        }

        .slide-title {
            display: table;
            padding: 0.4rem 0.6rem 0.2rem 0.6rem;
            font-size: 1.5rem;
            color: #ffffff;
            border-radius: 10px;
            background-color: rgba(80, 99, 130, 0.8);
        }

        .slider-nav {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.5);
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .slider-dot.active {
            background-color: var(--accent-color);
        }

        /* Explore Button */
        .explore-btn-container {
            text-align: center;
            margin: 50px 0;
            position: relative;
        }

        .explore-btn {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            background: linear-gradient(45deg, var(--accent-color), #6a7f9e);
            color: white;
            padding: 18px 40px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.3rem;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(80,99,130,0.4), 0 0 0 1px rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }

        .explore-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s ease;
        }

        .explore-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 40px rgba(80,99,130,0.6), 0 0 0 1px rgba(255,255,255,0.2);
        }

        .explore-btn:hover::before {
            left: 100%;
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
                padding-bottom: 12px;
                margin: 25px 0;
            }

            .page-title::after { width: 8rem; }

            .hero-slider {
                height: 350px;
                width: 90%;
                margin: 30px auto;
                border-radius: 10px;
            }

            .slide img { border-radius: 10px; }
            .slide-content { padding: 15px; }
            .slide-date { font-size: 0.8rem; }
            .slide-title { font-size: 1.2rem; }
            .slider-dot { width: 10px; height: 10px; }
            .explore-btn { padding: 13px 28px; font-size: 1rem; }

            .developer-card {
                min-width: 100%;
                max-width: 100%;
            }

            .collapsible-header { padding: 15px 20px; }
            .collapsible-title { font-size: 1.2rem; }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.6rem;
                padding-bottom: 10px;
                margin: 20px 0;
            }

            .page-title::after { width: 6rem; }

            .hero-slider {
                height: 200px;
                width: 90%;
                margin: 20px auto;
            }

            .slide-title { font-size: 0.9rem; }
            .slider-dot { width: 8px; height: 8px; }
            .explore-btn-container { margin: 30px 0; }
            .explore-btn { padding: 11px 26px; font-size: 0.8rem; }
        }
    </style>
</head>

<body>
    <!-- ===================================== -->
    <!-- Landing Page Overlay                   -->
    <!-- ===================================== -->
    <?php include 'includes/landing.php'; ?>

    <!-- ===================================== -->
    <!-- Unified Header                        -->
    <!-- ===================================== -->
    <?php include 'includes/header.php'; ?>

    <!-- ===================================== -->
    <!-- Developer Section (Collapsible)       -->
    <!-- ===================================== -->
    <div class="collapsible-sections">
        <div class="collapsible-section">
            <div class="collapsible-header">
                <h3 class="collapsible-title">
                    <i class="fas fa-user-graduate"></i>
                    <?php echo $lang['team_section']; ?>
                </h3>
                <i class="fas fa-chevron-down collapsible-icon"></i>
            </div>
            <div class="collapsible-content">
                <div class="developer-container">
                    <div class="developer-row">
                        <div class="developer-card">
                            <h4 class="dev-name"><?php echo $lang['developer_name']; ?></h4>
                            <div class="dev-info"><?php echo $lang['developer_id']; ?></div>
                            <span class="dev-role"><?php echo $lang['developer_role']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================== -->
    <!-- Admin Access Section (Collapsible)    -->
    <!-- ===================================== -->
    <div class="collapsible-sections">
        <div class="collapsible-section admin-access-section">
            <div class="collapsible-header admin-access-header">
                <h3 class="collapsible-title">
                    <i class="fas fa-shield"></i>
                    <?php echo $lang['admin_access_title']; ?>
                </h3>
                <i class="fas fa-chevron-down collapsible-icon"></i>
            </div>
            <div class="collapsible-content">
                <!-- Warning Message -->
                <div class="admin-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $lang['admin_access_desc']; ?>
                </div>

                <!-- Access Cards Grid -->
                <div class="admin-cards-grid">
                    <!-- URL Card -->
                    <a href="<?php echo $base_url; ?>admin/login.php" target="_blank">
                        <div class="admin-card">
                            <div class="admin-card-icon">
                                <i class="fas fa-link"></i>
                            </div>
                            <div class="admin-card-info">
                                <span class="admin-card-label"><?php echo $lang['admin_access_url']; ?></span>
                                <span class="admin-card-value"><?php echo $base_url; ?>admin/login.php</span>
                            </div>
                        </div>
                    </a>

                    <!-- Username Card -->
                    <div class="admin-card">
                        <div class="admin-card-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="admin-card-info">
                            <span class="admin-card-label"><?php echo $lang['admin_access_user']; ?></span>
                            <span class="admin-card-value">admin</span>
                        </div>
                    </div>

                    <!-- Password Card -->
                    <div class="admin-card">
                        <div class="admin-card-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="admin-card-info">
                            <span class="admin-card-label"><?php echo $lang['admin_access_pass']; ?></span>
                            <span class="admin-card-value">admin</span>
                        </div>
                    </div>
                </div>

                <!-- Security Note -->
                <div class="admin-security-note">
                    <i class="fas fa-lock"></i>
                    <?php echo $lang['admin_access_warn']; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================== -->
    <!-- Main Content                          -->
    <!-- ===================================== -->
    <main>
        <div class="main-content" id="mainContent">
            <!-- Page Title -->
            <h1 class="page-title"><?php echo $lang['home_title']; ?></h1>

            <!-- Hero Slider -->
            <div class="hero-slider" id="heroSlider">
                <!-- Populated dynamically by JS with data from PHP -->
            </div>

            <!-- Explore Button -->
            <div class="explore-btn-container">
                <a href="<?php echo $base_url; ?>events.php" class="explore-btn">
                    <?php echo $lang['home_explore']; ?>
                    <i class="fas fa-arrow-<?php echo ($lang_code === 'ar') ? 'left' : 'right'; ?>"></i>
                </a>
            </div>
        </div>
    </main>

    <!-- ===================================== -->
    <!-- Back to Top Button                    -->
    <!-- ===================================== -->
    <div class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- ===================================== -->
    <!-- Unified Footer                        -->
    <!-- ===================================== -->
    <?php include 'includes/footer.php'; ?>

    <!-- JQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>

    <!-- Custom JS (Page Specific) -->
    <script>
    $(document).ready(function() {
        // =====================================
        // Pass events data from PHP to JS
        // =====================================
        var eventsData = <?php
            $localizedEvents = [];
            foreach ($eventsData as $event) {
                $event['title']       = ($lang_code === 'en' && !empty($event['title_en'])) ? $event['title_en'] : $event['title'];
                $event['description'] = ($lang_code === 'en' && !empty($event['description_en'])) ? $event['description_en'] : $event['description'];
                $event['category']    = ($lang_code === 'en' && !empty($event['category_name_en'])) ? $event['category_name_en'] : $event['category_name_ar'];
                $event['location']    = ($lang_code === 'en' && !empty($event['location_name_en'])) ? $event['location_name_en'] : $event['location_name_ar'];
                $localizedEvents[] = $event;
            }
            echo json_encode($localizedEvents);
        ?>;

        // =====================================
        // Cookie Consent Handler
        // =====================================
        $('#cookieAccept').click(function() {
            document.cookie = "cookie_consent=1; path=/; max-age=" + (60*60*24*30);
            $('#cookieConsent').fadeOut(400);
        });

        // =====================================
        // Collapsible Developer Section
        // =====================================
        $('.collapsible-header').click(function() {
            var $section = $(this).closest('.collapsible-section');
            $section.toggleClass('active');
        });

        // Open developer section by default, keep admin section collapsed
        setTimeout(function() {
            $('.collapsible-section').not('.admin-access-section').addClass('active');
        }, 800);

        // =====================================
        // Hero Slider (Dynamic from DB)
        // =====================================
        function initHeroSlider() {
            if (eventsData.length === 0) {
                $('#heroSlider').html('<p style="text-align:center;padding:100px;"><?php echo $lang['home_no_event_avl']; ?></p>');
                return;
            }

            var sliderHtml = '';
            var dotsHtml = '';

            eventsData.forEach(function(event, index) {
                var activeClass = index === 0 ? 'active' : '';
                var imgSrc = event.image ? 'assets/img/events/' + event.image : 'assets/img/events/default.webp';
                sliderHtml +=
                    '<div class="slide ' + activeClass + '" data-event-id="' + event.id + '">' +
                        '<img src="' + imgSrc + '" alt="' + event.title + '">' +
                        '<div class="slide-content">' +
                            '<div class="slide-date">' + event.event_date + '</div>' +
                            '<h3 class="slide-title">' + event.title + '</h3>' +
                        '</div>' +
                    '</div>';
                dotsHtml += '<div class="slider-dot ' + activeClass + '" data-slide="' + index + '"></div>';
            });

            $('#heroSlider').html(sliderHtml);
            $('#heroSlider').append('<div class="slider-nav">' + dotsHtml + '</div>');

            // Click slide → go to event detail
            $('.slide').click(function() {
                var eventId = $(this).data('event-id');
                window.location.href = '<?php echo $base_url; ?>event.php?id=' + eventId;
            });

            // Slider logic
            var currentSlide = 0;
            var slideCount = eventsData.length;

            function goToSlide(index) {
                $('.slide').removeClass('active');
                $('.slider-dot').removeClass('active');
                $('.slide').eq(index).addClass('active');
                $('.slider-dot[data-slide="' + index + '"]').addClass('active');
                currentSlide = index;
            }

            $('.slider-dot').click(function(e) {
                e.stopPropagation();
                var slideIndex = $(this).data('slide');
                goToSlide(slideIndex);
            });

            // Auto rotate every 5 seconds
            setInterval(function() {
                currentSlide = (currentSlide + 1) % slideCount;
                goToSlide(currentSlide);
            }, 5000);
        }

        initHeroSlider();
    });
    </script>
</body>
</html>