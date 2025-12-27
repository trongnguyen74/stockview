<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once('indicators.php');  // your existing indicator helpers

// --- INPUTS & SETTINGS ---
$stockName = filter_input(INPUT_GET, 's', FILTER_SANITIZE_STRING);
$setting = [
    'volume'    => filter_var($_GET['volume']    ?? false, FILTER_VALIDATE_BOOLEAN),
    'sma1'       => filter_var($_GET['sma1']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'sma2'       => filter_var($_GET['sma2']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'sma3'       => filter_var($_GET['sma3']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'sma4'       => filter_var($_GET['sma4']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'ema1'       => filter_var($_GET['ema1']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'ema2'       => filter_var($_GET['ema2']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'ema3'       => filter_var($_GET['ema3']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'ema4'       => filter_var($_GET['ema4']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'ema'       => filter_var($_GET['ema']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'rsi'       => filter_var($_GET['rsi']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'mfi'       => filter_var($_GET['mfi']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'cci'       => filter_var($_GET['cci']       ?? false, FILTER_VALIDATE_BOOLEAN),
    'william'   => filter_var($_GET['william']   ?? false, FILTER_VALIDATE_BOOLEAN),
    'macd'      => filter_var($_GET['macd']      ?? false, FILTER_VALIDATE_BOOLEAN),

    'sma1_period' => isset($_GET['smap1']) ? max(1, (int)$_GET['smap1']) : 10,
    'sma2_period' => isset($_GET['smap2']) ? max(1, (int)$_GET['smap2']) : 20,
    'sma3_period' => isset($_GET['smap3']) ? max(1, (int)$_GET['smap3']) : 50,
    'sma4_period' => isset($_GET['smap4']) ? max(1, (int)$_GET['smap4']) : 100,

    'ema1_period' => isset($_GET['emap1']) ? max(1, (int)$_GET['emap1']) : 10,
    'ema2_period' => isset($_GET['emap2']) ? max(1, (int)$_GET['emap2']) : 20,
    'ema3_period' => isset($_GET['emap3']) ? max(1, (int)$_GET['emap3']) : 50,
    'ema4_period' => isset($_GET['emap4']) ? max(1, (int)$_GET['emap4']) : 60,

    'rsi_period' => isset($_GET['rsip']) ? max(1, (int)$_GET['rsip']) : 14,
    'mfi_period' => isset($_GET['mfip']) ? max(1, (int)$_GET['mfip']) : 14,
    'cci_period' => isset($_GET['ccip']) ? max(1, (int)$_GET['ccip']) : 14,
    'william_period' => isset($_GET['williamp']) ? max(1, (int)$_GET['williamp']) : 14,
];

if (!$stockName) {
    http_response_code(400);
    exit(json_encode(['error' => 'Missing or invalid stockname parameter']));
}

// --- COMPANY NAME LOOKUP ---
$companyName = 'Unknown Company';
$companyList  = json_decode(file_get_contents(__DIR__ . '/data/company_search.json'), true);
foreach ($companyList as $e) {
    if (strtoupper($e['stockname'] ?? '') === strtoupper($stockName)) {
        $companyName = $e['company_name'];
        break;
    }
}

// --- FETCH & PREP DATA ---
function getStockChart(string $stock): array {
    $url   = "https://cophieu68.vn/datax123456/chart/daily/{$stock}.txt";
    $chart = [];

    if ($h = @fopen($url, 'r')) {
        fgetcsv($h);
        while ($r = fgetcsv($h)) {
            if (empty($r[1])) continue;
            $chart[] = [
                'time'   => strtotime($r[1]),
                0        => (float)$r[3], // open
                1        => (float)$r[4], // high
                2        => (float)$r[5], // low
                3        => (float)$r[6], // close
                4        => (float)$r[7], // volume
            ];
        }
        fclose($h);
    }

    // today EOD
    $now = new DateTimeImmutable();
    if ( (int)$now->format('G') >= 9
      && (int)$now->format('G') <= 15
      && (int)$now->format('N') <= 5
    ) {
        $eodFile = __DIR__ . '/../data/eod.json';
        if (is_readable($eodFile)) {
            $eod = json_decode(file_get_contents($eodFile), true)[$stock] ?? null;
            if ($eod) {
                $chart[] = [
                    'time'   => time(),
                    0        => (float)$eod['open'],
                    1        => (float)$eod['price_highest'],
                    2        => (float)$eod['price_lowest'],
                    3        => (float)$eod['close'],
                    4        => (float)$eod['volume'],
                ];
            }
        }
    }

    return $chart;
}

$allData = getStockChart($stockName);
if (empty($allData)) {
    http_response_code(500);
    exit(json_encode(['error' => 'No chart data available']));
}
$data = array_slice($allData, -200);
$total = count($data);

// --- INDICATOR FUNCTIONS (including corrected RSI) ---
function calculate_rsi(array $data, int $period = 14): array {
    $rsi        = array_fill(0, count($data), null);
    $gains      = $losses = 0.0;
    // first period
    for ($i = 1; $i <= $period; $i++) {
        $chg = $data[$i][3] - $data[$i-1][3];
        if ($chg > 0) $gains  += $chg;
        else           $losses += -$chg;
    }
    $avgGain = $gains / $period;
    $avgLoss = $losses / $period;
    $rsi[$period] = $avgLoss == 0
        ? 100
        : 100 - (100 / (1 + $avgGain / $avgLoss));

    // Wilder smoothing
    for ($i = $period+1; $i < count($data); $i++) {
        $chg = $data[$i][3] - $data[$i-1][3];
        $gain = $chg > 0 ? $chg : 0;
        $loss = $chg < 0 ? -$chg : 0;
        $avgGain = ( ($avgGain * ($period - 1)) + $gain ) / $period;
        $avgLoss = ( ($avgLoss * ($period - 1)) + $loss ) / $period;
        $rsi[$i] = $avgLoss == 0
            ? 100
            : 100 - (100 / (1 + $avgGain / $avgLoss));
    }

    return $rsi;
}

// other indicator functions: calculateSMA(), calculateEMA(), calculateBollinger(), drawSMAline(), etc.
// assume they’re in indicators.php

// --- CHART DIMENSIONS & COLORS ---
$margin         = 50;
$width          = 1920;
$candleH        = 600;
$candleW        = floor(($width - 2 * $margin) / ($total * 1.5));
$volumeH        = $setting['volume'] ? 100 : 0;
$rsiH           = $setting['rsi']    ? 150 : 0;
$mfiH           = $setting['mfi']    ? 150 : 0;
$cciH           = $setting['cci']    ? 150 : 0;
$wrH            = $setting['william']? 150 : 0;
$macdH      = $setting['macd']   ? 150 : 0;
$dateAxisH      = 20;
$height     = $candleH + $volumeH + $rsiH + $mfiH + $cciH + $wrH + $macdH + $dateAxisH;

$image = imagecreatetruecolor($width, $height);
$bg       = imagecolorallocate($image,   0,   0,   0);
$white = imagecolorallocate($image, 255, 255, 255);
$green = imagecolorallocate($image, 0, 255, 0);
$red = imagecolorallocate($image, 255, 0, 0);
$gray = imagecolorallocate($image, 160, 160, 160);
$darkGray = imagecolorallocate($image, 80, 80, 80);
$yellow = imagecolorallocate($image, 255, 255, 0);
$orange = imagecolorallocate($image, 255, 165, 0);
$cyan   = imagecolorallocate($image, 0, 255, 255);
$violet = imagecolorallocate($image, 238, 130, 238);
$blue = imagecolorallocate($image, 100, 100, 255);
$purple = imagecolorallocate($image, 180, 80, 255);
$black = imagecolorallocate($image, 0, 0, 0);

$font = __DIR__ . '/DejaVuSans.ttf';

imagefill($image, 0, 0, $bg);

// sections Y-offsets
$volTop   = $candleH;
$rsiTop   = $candleH + $volumeH;
$mfiTop   = $rsiTop   + $rsiH;
$cciTop   = $mfiTop   + $mfiH;
$wrTop    = $cciTop   + $cciH;
$macdTop    = $wrTop + $wrH;
$xAxisTop   = $macdTop + $macdH;

// price Y‑scale
$maxPrice = ceil(max(array_column($data,1)));
$minPrice = floor(min(array_column($data,2)));
$range    = $maxPrice - $minPrice;
$scaleP   = ($candleH - 2*$margin) / $range;

// volume scale
$maxVol   = max(array_column($data,4));

// draw title
imagettftext($image, 16, 0, $margin, 30, $white, $font, strtoupper($stockName)." - $companyName");

// --- GRID & PRICE AXIS ---
for ($p = $minPrice; $p <= $maxPrice; $p += max(1,round($range/10))) {
    $y = (int)($candleH - $margin - ($p - $minPrice)*$scaleP);
    imageline($image, $margin, $y, $width-$margin, $y, $darkGray);
    imagestring($image, 2, 5, $y-7, (string)$p, $white);
}
imageline($image, $margin, $margin, $margin, $candleH-$margin, $gray);
imageline($image, $margin, $candleH-$margin, $width-$margin, $candleH-$margin, $gray);

// --- CANDLES & VOLUME & RSI & DATES ---
$cw   = floor(($width-2*$margin)/($total*1.5));
$gap  = floor($cw*0.5);
$startX = $margin + ((($width-2*$margin)-(($cw+$gap)*$total-$gap))/2);

// 1) Candles
$x = $startX;
foreach ($data as $row) {
    [$o,$h,$l,$c] = $row;
    $col = $c >= $o ? $green : $red;
    $x1 = (int)$x; $x2 = $x1+$cw;
    $yH = (int)($candleH-$margin-($h-$minPrice)*$scaleP);
    $yL = (int)($candleH-$margin-($l-$minPrice)*$scaleP);
    $yO = (int)($candleH-$margin-($o-$minPrice)*$scaleP);
    $yC = (int)($candleH-$margin-($c-$minPrice)*$scaleP);
    imageline($image, (int)($x1+$x2)/2, $yH, (int)($x1+$x2)/2, $yL, $white);
    imagefilledrectangle($image, $x1, min($yO,$yC), (int)$x2, max($yO,$yC), $col);
    $x += $cw + $gap;
}

// 2) Volume
if ($setting['volume']) {
    $x = $startX;
    foreach ($data as $r) {
        [$o,,,,$v] = $r;
        $col = $r[3] >= $o ? $green : $red;
        $h = (int)(($volumeH-20)*($v/$maxVol));
        imagefilledrectangle($image,
            (int)$x,
            $candleH-10,
            (int)($x+$cw),
            $candleH-10-$h,
            $col
        );
        $x += $cw + $gap;
    }
}

$sma_color = [$yellow, $orange, $cyan, $violet];
for($i=1; $i<5; $i++){
    $showSMA = $setting['sma'.$i];
    $SMAperiod = $setting['sma'.$i.'_period'];
    if($showSMA){
        $sma = array_slice(calculateSMA($allData, $SMAperiod), -$total);
        drawSMAline($image, $sma, $startX, $candleW, $gap, $candleH, $margin, $minPrice, $scaleP, $sma_color[$i-1]);
    }
}

$ema_color = [$red, $gray, $blue, $green];
for($i=1; $i<5; $i++){
    $showEMA = $setting['ema'.$i];
    $EMAperiod = $setting['ema'.$i.'_period'];
    if($showEMA){
        $ema = array_slice(calculateEMA($allData, $EMAperiod), -$total);
        drawEMAline($image, $ema, $startX, $candleW, $gap, $candleH, $margin, $minPrice, $scaleP, $ema_color[$i-1]);
    }
}

if ($setting['ema']) {
    $ema10 = array_slice(calculateEMA($allData, 10), -$total);
    $ema20 = array_slice(calculateEMA($allData, 20), -$total);
    $ema50 = array_slice(calculateEMA($allData, 50), -$total);
    $ema60 = array_slice(calculateEMA($allData, 60), -$total);

    drawEMAline($image, $ema10, $startX, $candleW, $gap, $candleH, $margin, $minPrice, $scaleP, $red);
    drawEMAline($image, $ema20, $startX, $candleW, $gap, $candleH, $margin, $minPrice, $scaleP, $gray);
    drawEMAline($image, $ema50, $startX, $candleW, $gap, $candleH, $margin, $minPrice, $scaleP, $blue);
    drawEMAline($image, $ema60, $startX, $candleW, $gap, $candleH, $margin, $minPrice, $scaleP, $green);
}

// 3) RSI
if ($setting['rsi']) {
    // grid box
    imageline($image, $margin, $rsiTop,         $width-$margin, $rsiTop,         $gray);
    imageline($image, $margin, $rsiTop+$rsiH,  $width-$margin, $rsiTop+$rsiH,  $gray);
    // levels
    foreach ([30,50,70] as $lvl) {
        $y = (int)($rsiTop + $rsiH - $lvl*($rsiH/100));
        imageline($image, $margin, $y, $width-$margin, $y, $darkGray);
        imagestring($image,2,5,$y-7,(string)$lvl,$white);
    }
    imagettftext($image, 10, 0, $margin, $rsiTop + 15, $white, $font, "RSI(".$setting['rsi_period'].")");
    // line
    $rsiArr = calculate_rsi($allData, $setting['rsi_period']);
    $rsiArr = array_slice($rsiArr, -$total);
    $x = $startX; $prev = null;
    foreach ($rsiArr as $val) {
        if (is_numeric($val)) {
            $cx = (int)($x+$cw/2);
            $cy = (int)($rsiTop + $rsiH - $val*($rsiH/100));
            if ($prev) {
                imageline($image, $prev[0], $prev[1], $cx, $cy, $white);
            }
            $prev = [$cx,$cy];
        }
        $x += $cw + $gap;
    }
}

if ($setting['mfi']) {    
    // draw box & levels
    imageline($image, $margin, $mfiTop,       $width-$margin, $mfiTop,       $gray);
    imageline($image, $margin, $mfiTop+$mfiH, $width-$margin, $mfiTop+$mfiH, $gray);
    foreach ([20,50,80] as $lvl) {
        $y = (int)($mfiTop + $mfiH - $lvl*($mfiH/100));
        imageline($image, $margin, $y, $width-$margin, $y, $darkGray);
        imagestring($image,2,5,$y-7,(string)$lvl,$white);
    }
    // title
    imagettftext($image, 10, 0, $margin, $mfiTop + 15, $white, $font, "MFI(".$setting['mfi_period'].")");

    // plot
    $arr = array_slice(calculate_mfi($allData, $setting['mfi_period']), -$total);
    $x   = $startX; $prev = null;
    foreach ($arr as $v) {
        if (is_numeric($v)) {
            $cx = (int)($x + $cw/2);
            $cy = (int)($mfiTop + $mfiH - $v*($mfiH/100));
            if ($prev) imageline($image, $prev[0], $prev[1], $cx, $cy, $white);
            $prev = [$cx,$cy];
        }
        $x += $cw + $gap;
    }
}

// --- 5) CCI PANEL ---
if ($setting['cci']) {
    $cciFull  = calculate_cci($allData, $setting['cci_period']);
    $cciSlice = array_slice($cciFull, -$total);
    // 2) determine min/max for dynamic scale
    $numeric = array_filter($cciSlice, 'is_numeric');
    $minCCI  = min($numeric);
    $maxCCI  = max($numeric);
    $rangeCCI = $maxCCI - $minCCI ?: 1;
    $scaleCCI = $cciH / $rangeCCI;

    // draw panel box
    imageline($image, $margin, $cciTop,       $width - $margin, $cciTop,       $gray);
    imageline($image, $margin, $cciTop+$cciH, $width - $margin, $cciTop+$cciH, $gray);

    // draw level lines at -100, 0, +100 if they fall inside your data range
    foreach ([-100, 0, 100] as $lvl) {
        if ($lvl >= $minCCI && $lvl <= $maxCCI) {
            $y = (int)($cciTop + ($maxCCI - $lvl) * $scaleCCI);
            imageline($image, $margin, $y, $width - $margin, $y, $darkGray);
            imagestring($image, 2, 5, $y - 7, (string)$lvl, $white);
        }
    }

    // draw title
    imagettftext($image, 10, 0, $margin, $cciTop + 15, $white, $font, "CCI(".$setting['cci_period'].")");

    // plot the CCI line
    $x = $startX;
    $prev = null;
    foreach ($cciSlice as $v) {
        if (is_numeric($v)) {
            $cx = (int)($x + $cw/2);
            // map value into panel
            $cy = (int)($cciTop + ($maxCCI - $v) * $scaleCCI);
            if ($prev) {
                imageline($image, $prev[0], $prev[1], $cx, $cy, $white);
            }
            $prev = [$cx, $cy];
        }
        $x += $cw + $gap;
    }
}

// --- 6) WILLIAMS %R PANEL ---
if ($setting['william']) {
    imageline($image, $margin, $wrTop,        $width-$margin, $wrTop,        $gray);
    imageline($image, $margin, $wrTop+$wrH,   $width-$margin, $wrTop+$wrH,   $gray);
    foreach ([-80,-50,-20] as $lvl) {
        $y = (int)($wrTop + $wrH - (($lvl + 100)/100)*$wrH);
        imageline($image, $margin, $y, $width-$margin, $y, $darkGray);
        imagestring($image,2,5,$y-7,(string)$lvl,$white);
    }
    imagettftext($image, 10, 0, $margin, $wrTop + 15, $white, $font, "Williams %R(".$setting['william_period'].")");

    $arr = array_slice(calculate_wr($allData, $setting['william_period']), -$total);
    $x   = $startX; $prev = null;
    foreach ($arr as $v) {
        if (is_numeric($v)) {
            $cx = (int)($x + $cw/2);
            $cy = (int)($wrTop + $wrH - (($v+100)/100)*$wrH);
            if ($prev) imageline($image, $prev[0], $prev[1], $cx, $cy, $white);
            $prev = [$cx,$cy];
        }
        $x += $cw + $gap;
    }
}

// --- MACD PANEL ---
if ($setting['macd']) {
    // 1) compute full MACD arrays, then slice to last $total
    $m = calculate_macd($allData, 12, 26, 9);
    $macdArr   = array_slice($m['macd'],   -$total);
    $signalArr = array_slice($m['signal'], -$total);
    $histArr   = array_slice($m['hist'],   -$total);

    // 2) auto‑scale panel to min/max of (macd + signal)
    $values = array_filter(
        array_merge($macdArr, $signalArr),
        'is_numeric'
    );
    $minM = min($values);
    $maxM = max($values);
    $rangeM = $maxM - $minM ?: 1;
    $scaleM = $macdH / $rangeM;

    // 3) draw panel box and zero‐line
    imageline($image, $margin, $macdTop,      $width-$margin, $macdTop,      $gray);
    imageline($image, $margin, $macdTop+$macdH,$width-$margin, $macdTop+$macdH,$gray);
    // zero reference
    $y0 = (int)($macdTop + ($maxM - 0)*$scaleM);
    imageline($image, $margin, $y0, $width-$margin, $y0, $darkGray);

    // 4) title
    imagettftext($image, 10, 0, $margin, $macdTop + 15, $white, $font, "MACD(12,26,9)");

    // 5) histogram bars (lighter gray)
    $x = $startX;
    foreach ($histArr as $v) {
        if (is_numeric($v)) {
            $y1 = (int)($macdTop + ($maxM - 0)*$scaleM);
            $y2 = (int)($macdTop + ($maxM - $v)*$scaleM);
            imagefilledrectangle(
                $image,
                (int)$x,
                $y1,
                (int)($x+$cw),
                $y2,
                $darkGray
            );
        }
        $x += $cw + $gap;
    }

    // 6) MACD & signal lines
    //    MACD line in white, signal in yellow
    $x = $startX; $prevM = $prevS = null;
    foreach (range(0, $total-1) as $i) {
        if (is_numeric($macdArr[$i])) {
            $cx = (int)($x + $cw/2);
            $cM = (int)($macdTop + ($maxM - $macdArr[$i])*$scaleM);
            if ($prevM) {
                imageline($image, $prevM[0], $prevM[1], $cx, $cM, $white);
            }
            $prevM = [$cx, $cM];
        }
        if (is_numeric($signalArr[$i])) {
            $cS = (int)($macdTop + ($maxM - $signalArr[$i])*$scaleM);
            if ($prevS) {
                imageline($image, $prevS[0], $prevS[1], $cx, $cS, $yellow);
            }
            $prevS = [$cx, $cS];
        }
        $x += $cw + $gap;
    }
}

// 4) X‑axis dates
$x = $startX;
foreach ($data as $i => $r) {
    if ($i % 10 === 0) {
        $label = date('m/d', $r['time']);
        $tx = (int)($x + $cw/2 - strlen($label)*3);
        imagestring($image,2,$tx,$xAxisTop,$label,$gray);
    }
    $x += $cw + $gap;
}

// output
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>