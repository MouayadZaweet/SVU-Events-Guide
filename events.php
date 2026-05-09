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
// Fetch All Events from Database
// =====================================
try {
    $stmt = $pdo->query("
        SELECT e.*, 
               c.name_ar AS category_name_ar, c.name_en AS category_name_en,
               l.name_ar AS location_name_ar, l.name_en AS location_name_en
        FROM events e
        LEFT JOIN categories c ON e.category = c.id
        LEFT JOIN locations l ON e.location = l.id
        ORDER BY e.event_date DESC
    ");
    $eventsData = $stmt->fetchAll();
} catch (PDOException $e) {
    $eventsData = [];
}

// Extract categories and cities based on current language
$categories = [];
$cities = [];
foreach ($eventsData as $event) {
    $cat = ($lang_code === 'en' && !empty($event['category_name_en'])) ? $event['category_name_en'] : $event['category_name_ar'];
    $loc = ($lang_code === 'en' && !empty($event['location_name_en'])) ? $event['location_name_en'] : $event['location_name_ar'];
    if (!in_array($cat, $categories)) $categories[] = $cat;
    if (!in_array($loc, $cities)) $cities[] = $loc;
}
sort($categories);
sort($cities);
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
    <title><?php echo $lang['nav_events']; ?> | <?php echo $lang['site_title']; ?></title>
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

        /* Page Title */
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
            width: 150px;
            height: 3px;
            background-color: var(--accent-color);
        }

        /* Filters */
        .filters {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--primary-color);
        }

        .filter-group select, .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .filter-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-apply {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-apply:hover { background-color: var(--accent-color); }

        .btn-clear {
            background-color: var(--card-bg);
            color: var(--text-primary);
            border: 1px solid #ddd;
        }

        .btn-clear:hover { background-color: #e5e7eb; }

        /* Events Grid */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .event-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .event-image {
            height: 200px;
            overflow: hidden;
        }

        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .event-card:hover .event-image img { transform: scale(1.05); }

        .event-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .event-date {
            color: var(--accent-color);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .event-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
            flex: 1;
        }

        .event-location {
            color: var(--text-secondary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .event-category {
            display: inline-block;
            background-color: var(--card-bg);
            color: var(--primary-color);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 15px;
        }

        .event-btn {
            align-self: flex-start;
            padding: 8px 20px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin-top: auto;
        }

        .event-btn:hover { background-color: var(--accent-color); }

        .no-events {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            background-color: var(--card-bg);
            border-radius: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .events-grid { grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
            .filter-row { flex-direction: column; align-items: stretch; }
            .filter-group { min-width: 100%; }
            .filter-buttons { justify-content: center; }
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
            <!-- Page Title -->
            <h1 class="page-title"><?php echo $lang['events_title']; ?></h1>

            <!-- Filters -->
            <div class="filters">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="category"><?php echo $lang['events_all_cats']; ?></label>
                        <select id="categoryFilter">
                            <option value=""><?php echo $lang['events_all_cats']; ?></option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="city"><?php echo $lang['events_all_cities']; ?></label>
                        <select id="cityFilter">
                            <option value=""><?php echo $lang['events_all_cities']; ?></option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?php echo htmlspecialchars($city); ?>"><?php echo htmlspecialchars($city); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="date"><?php echo $lang['event_date']; ?></label>
                        <input type="date" id="dateFilter">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button class="filter-btn btn-apply" id="applyFilters"><?php echo $lang['events_filter']; ?></button>
                    <button class="filter-btn btn-clear" id="clearFilters"><?php echo $lang['events_clear']; ?></button>
                </div>
            </div>

            <!-- Events Grid -->
            <div class="events-grid" id="eventsGrid">
                <!-- Dynamically populated by JS -->
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
        // Display Events
        // =====================================
        function displayEvents(events) {
            var $grid = $('#eventsGrid');

            if (events.length === 0) {
                $grid.html('<div class="no-events"><?php echo $lang['events_no_results']; ?></div>');
                return;
            }

            var html = '';
            events.forEach(function(event) {
                var imgSrc = event.image ? 'assets/img/events/' + event.image : 'assets/img/events/default.webp';
                html += '' +
                    '<div class="event-card">' +
                        '<div class="event-image">' +
                            '<img src="' + imgSrc + '" alt="' + event.title + '">' +
                        '</div>' +
                        '<div class="event-content">' +
                            '<div class="event-date">' + event.event_date + '</div>' +
                            '<h3 class="event-title">' + event.title + '</h3>' +
                            '<div class="event-location"><i class="fas fa-map-marker-alt"></i>' + event.location + '</div>' +
                            '<span class="event-category">' + event.category + '</span>' +
                            '<a href="<?php echo $base_url; ?>event.php?id=' + event.id + '" class="event-btn"><?php echo $lang['events_details']; ?></a>' +
                        '</div>' +
                    '</div>';
            });

            $grid.html(html);
        }

        // Show all initially
        displayEvents(eventsData);

        // =====================================
        // Apply Filters
        // =====================================
        $('#applyFilters').click(function() {
            var cat = $('#categoryFilter').val();
            var city = $('#cityFilter').val();
            var date = $('#dateFilter').val();

            var filtered = eventsData.filter(function(e) {
                return (!cat || e.category === cat) &&
                       (!city || e.location === city) &&
                       (!date || e.event_date === date);
            });

            displayEvents(filtered);
        });

        // Clear Filters
        $('#clearFilters').click(function() {
            $('#categoryFilter').val('');
            $('#cityFilter').val('');
            $('#dateFilter').val('');
            displayEvents(eventsData);
        });
    });
    </script>
</body>
</html>