<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$page_title = "Weather Updates";
include '../includes/header.inc.php';

if ($role === 'Farmer') {
    include '../includes/navbar_farmer.inc.php';
} else {
    include '../includes/navbar_customer.inc.php';
}
?>

<?php
$city = $_GET['city'] ?? 'Nagpur';
$WEATHER_API_KEY = 'e0257658d3681b4d64fe09a5d00ee9c5';

// Current weather
$url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=$WEATHER_API_KEY&units=metric";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);
$weatherData = json_decode($response, true);

if (isset($weatherData['main'])) {
    $temp      = round($weatherData['main']['temp']);
    $humidity  = $weatherData['main']['humidity'];
    $wind      = round($weatherData['wind']['speed'] * 3.6);
    $condition = ucfirst($weatherData['weather'][0]['description']);
    
    // Lat Lon nikalo current weather se
    $lat = $weatherData['coord']['lat'];
    $lon = $weatherData['coord']['lon'];

    // UV Index API call
    $uvUrl = "https://api.openweathermap.org/data/2.5/uvi?lat=$lat&lon=$lon&appid=$WEATHER_API_KEY";
    $ch3 = curl_init($uvUrl);
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch3, CURLOPT_TIMEOUT, 10);
    $uvResponse = curl_exec($ch3);
    curl_close($ch3);
    $uvData = json_decode($uvResponse, true);
    
    // UV value set karo
    $uv = isset($uvData['value']) ? round($uvData['value']) : 5;

} else {
    $temp = $humidity = $wind = $uv = 'N/A';
    $condition = 'Data unavailable';
}

// 5-day forecast
$forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($city) . "&appid=$WEATHER_API_KEY&units=metric";
$ch2 = curl_init($forecastUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
$forecastResponse = curl_exec($ch2);
curl_close($ch2);
$forecastData = json_decode($forecastResponse, true);

// Har din ka ek record nikalo
$dailyForecast = [];
if (isset($forecastData['list'])) {
    $seenDays = [];
    foreach ($forecastData['list'] as $item) {
        // UTC time ko IST mein convert karo (+5:30)
        $istTimestamp = $item['dt'] + (5.5 * 3600);
        $day = gmdate('D', $istTimestamp); // IST day
        
        if (!in_array($day, $seenDays) && count($dailyForecast) < 5) {
            $seenDays[] = $day;
            $iconCode = $item['weather'][0]['icon'];

            $iconMap = [
                '01' => 'sun',
                '02' => 'cloud-sun',
                '03' => 'cloud',
                '04' => 'cloud',
                '09' => 'cloud-showers-heavy',
                '10' => 'cloud-rain',
                '11' => 'bolt',
                '13' => 'snowflake',
                '50' => 'smog'
            ];
            $iconKey = substr($iconCode, 0, 2);
            $faIcon = $iconMap[$iconKey] ?? 'cloud';

            $dailyForecast[] = [
                'day'  => $day,
                'max'  => round($item['main']['temp_max']),
                'min'  => round($item['main']['temp_min']),
                'icon' => $faIcon
            ];
        }
    }
}
?>
<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0" style="color: var(--primary-blue) !important;">Local Weather Forecast 🌦️</h2>
        <form action="" method="GET" class="d-flex">
            <input type="text" name="city" class="form-control rounded-pill me-2 px-4 shadow-sm" placeholder="Search City..." value="<?= htmlspecialchars($city) ?>" required>
            <button class="btn btn-primary rounded-pill px-4" type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="row g-5">
        <div class="col-lg-8 animate-fade-in-up">
            
            <div class="glass-card p-5 text-white mb-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important;">
                <div class="position-relative z-1 text-center py-4">
                    <h5 class="opacity-75 mb-1">Current Condition in <?= htmlspecialchars(ucfirst($city)) ?></h5>
                    <h1 class="display-1 fw-bold mb-0"><?= $temp ?>°C</h1>
                    <p class="fs-4"><?= $condition ?></p>
                    <div class="d-flex justify-content-center gap-4 mt-4">
                        <div class="text-center">
                            <i class="fas fa-wind d-block mb-1"></i>
                            <span class="small opacity-75">Wind</span>
                            <div class="fw-bold"><?= $wind ?> km/h</div>
                        </div>
                        <div class="text-center border-start border-end border-white border-opacity-25 px-4">
                            <i class="fas fa-tint d-block mb-1"></i>
                            <span class="small opacity-75">Humidity</span>
                            <div class="fw-bold"><?= $humidity ?>%</div>
                        </div>
                        <div class="text-center">
                            <i class="fas fa-sun d-block mb-1"></i>
                            <span class="small opacity-75">UV Index</span>
                            <div class="fw-bold"><?= $uv > 7 ? 'High' : ($uv > 4 ? 'Moderate' : 'Low') ?> (<?= $uv ?>)</div>
                        </div>
                    </div>
                </div>
                <i class="fas fa-cloud-sun position-absolute top-0 end-0 m-4 opacity-25" style="font-size: 8rem;"></i>
            </div>

            <div class="row g-3">
                <?php foreach ($dailyForecast as $day): ?>
                    <div class="col border-0">
                        <div class="glass-card p-3 text-center h-100">
                            <span class="small fw-bold text-muted"><?= $day['day'] ?></span>
                            <i class="fas fa-<?= $day['icon'] ?> d-block my-3 fs-4 text-primary"></i>
                            <div class="fw-bold"><?= $day['max'] ?>/<?= $day['min'] ?>°</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-4 animate-fade-in-up" style="animation-delay: 0.2s;">
            <div class="glass-card p-4 mb-4 border-start border-4 border-warning">
                <h5 class="fw-bold mb-3"><i class="fas fa-exclamation-circle me-2 text-warning"></i>Agricultural Alert</h5>
                <p class="text-muted small">Expect light rains on Wednesday afternoon. It's recommended to delay pesticide application until Thursday morning.</p>
                <button class="btn btn-outline-warning w-100 rounded-pill btn-sm fw-bold">View Full Advisory</button>
            </div>

            <div class="glass-card p-4">
                <h6 class="fw-bold mb-3">Farm Tips for Today</h6>
                <ul class="list-unstyled small text-muted">
                    <li class="mb-3 d-flex align-items-start">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <span>Check soil moisture in the northern field before 10 AM.</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <span>Start harvesting early to avoid the afternoon peak heat.</span>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="fas fa-check-circle text-success me-2 mt-1"></i>
                        <span>Clean irrigation filters to maintain water pressure.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.inc.php'; ?>
