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
    <title><?php echo $lang['nav_about']; ?> | <?php echo $lang['site_title']; ?></title>
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
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .page-title {
            text-align: center;
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
            width: 100px;
            height: 3px;
            background-color: var(--accent-color);
        }

        .about-section {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .about-section h2 {
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .about-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            inset-inline-start: 0;
            width: 50px;
            height: 2px;
            background-color: var(--accent-color);
        }

        .about-content { line-height: 1.8; }
        .about-content p { margin-bottom: 15px; }

        .mvp-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
        }

        .mvp-item {
            flex: 1;
            min-width: 250px;
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            position: relative;
        }

        .mvp-item::before {
            content: '?';
            position: absolute;
            top: -15px;
            right: 50%;
            transform: translateX(50%);
            background-color: var(--accent-color);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
        }

        .mvp-item h3 { color: var(--primary-color); margin-bottom: 15px; }

        .policies-list { list-style: none; padding: 0; }
        .policies-list li {
            margin-bottom: 15px;
            padding-inline-start: 20px;
            position: relative;
        }
        .policies-list li::before {
            content: '•';
            color: var(--accent-color);
            font-weight: bold;
            position: absolute;
            inset-inline-start: 0;
        }

        /* ===================================== */
        /* Special Thank-You Image Section      */
        /* ===================================== */
        .thankyou-section {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }

        .thankyou-image {
            width: 100%;
            display: block;
            max-height: 700px;
            object-fit: cover;
        }

        .thankyou-caption {
            text-align: center;
            padding: 20px 30px;
            background: linear-gradient(to bottom, #f9fafb, #ffffff);
        }

        .thankyou-caption h3 {
            color: var(--primary-color);
            font-size: 1.4rem;
            margin-bottom: 8px;
        }

        .thankyou-caption p {
            color: var(--text-secondary);
            font-size: 1rem;
            margin: 0;
        }

        @media (max-width: 768px) {
            .mvp-container { flex-direction: column; }
            .thankyou-image { max-height: 400px; }
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
    <!-- Main Content                          -->
    <!-- ===================================== -->
    <main>
        <div class="main-content" id="mainContent">
            <h1 class="page-title"><?php echo $lang['about_title']; ?></h1>

            <div class="about-section">
                <h2><?php echo $lang['about_welcome']; ?></h2>
                <div class="about-content">
                    <p><?php echo $lang['about_intro']; ?></p>
                    <p><?php echo $lang['about_belief']; ?></p>
                </div>
            </div>

            <div class="about-section">
                <h2><?php echo $lang['about_vision_title']; ?></h2>
                <div class="mvp-container">
                    <div class="mvp-item">
                        <h3><?php echo $lang['vision']; ?></h3>
                        <p><?php echo $lang['about_vision']; ?></p>
                    </div>
                    <div class="mvp-item">
                        <h3><?php echo $lang['mission']; ?></h3>
                        <p><?php echo $lang['about_mission']; ?></p>
                    </div>
                    <div class="mvp-item">
                        <h3><?php echo $lang['goal']; ?></h3>
                        <p><?php echo $lang['about_goal']; ?></p>
                    </div>
                </div>
            </div>

            <!-- ===================================== -->
            <!-- Special Thank-You Image               -->
            <!-- ===================================== -->
            <div class="thankyou-section">
                <img src="assets/img/Thank-You.webp" alt="Thank You" class="thankyou-image">
                <div class="thankyou-caption">
                    <h3>
                        <?php echo ($lang_code === 'ar') ? 'شكراً لكم على دعمكم المتواصل' : 'Thank You for Your Continued Support'; ?>
                    </h3>
                    <p>
                        <?php echo ($lang_code === 'ar') ? 'معاً نبني مستقبلاً أفضل لطلاب الجامعة الافتراضية السورية' : 'Together we build a better future for SVU students'; ?>
                    </p>
                </div>
            </div>

            <div class="about-section">
                <h2><?php echo $lang['about_policies_title']; ?></h2>
                <div class="about-content">
                    <p><?php echo $lang['about_policies_desc']; ?></p>
                    <ul class="policies-list">
                        <li><?php echo $lang['about_li_1']; ?></li>
                        <li><?php echo $lang['about_li_2']; ?></li>
                        <li><?php echo $lang['about_li_3']; ?></li>
                        <li><?php echo $lang['about_li_4']; ?></li>
                        <li><?php echo $lang['about_li_5']; ?></li>
                        <li><?php echo $lang['about_li_6']; ?></li>
                        <li><?php echo $lang['about_li_7']; ?></li>
                    </ul>
                </div>
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
    $(document).ready(function () {
        // Cookie Consent Handler
        $('#cookieAccept').click(function() {
            document.cookie = "cookie_consent=1; path=/; max-age=" + (60*60*24*30);
            $('#cookieConsent').fadeOut(400);
        });
    });
    </script>
</body>
</html>