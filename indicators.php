<?php
// --- INDICATOR FUNCTIONS ---
function calculateSMA(array $data, int $period): array {
    $sma = [];
    $sum = 0;
    $queue = [];

    foreach ($data as $row) {
        $close = $row[3];
        $sum += $close;
        $queue[] = $close;

        if (count($queue) > $period) {
            $sum -= array_shift($queue);
        }

        $sma[] = (count($queue) === $period) ? ($sum / $period) : null;
    }

    return $sma;
}

function drawSMAline($image, array $sma, float $startX, float $candleWidth, float $gap, float $chartHeight, float $margin, float $min, float $scaleY, int $color): void {
    $prevX = $prevY = null;
    $x = $startX;

    foreach ($sma as $value) {
        if ($value !== null) {
            $y = (int)($chartHeight - $margin - ($value - $min) * $scaleY);
            $cx = (int)($x + $candleWidth / 2);
            if ($prevX !== null) {
                imageline($image, $prevX, $prevY, $cx, $y, $color);
            }
            $prevX = $cx;
            $prevY = $y;
        }
        $x += $candleWidth + $gap;
    }
}

function calculateEMA(array $data, int $period): array {
    $ema = [];
    $k = 2 / ($period + 1);
    $prevEma = null;

    foreach ($data as $i => $row) {
        $close = $row[3];

        if ($i < $period - 1) {
            $ema[] = null;
        } elseif ($i === $period - 1) {
            $slice = array_slice($data, 0, $period);
            $sum = array_sum(array_column($slice, 3));
            $prevEma = $sum / $period;
            $ema[] = $prevEma;
        } else {
            $prevEma = ($close - $prevEma) * $k + $prevEma;
            $ema[] = $prevEma;
        }
    }

    return $ema;
}

function drawEMAline($image, array $ema, float $startX, float $candleWidth, float $gap, float $chartHeight, float $margin, float $min, float $scaleY, int $color): void {
    $prevX = $prevY = null;
    $x = $startX;

    foreach ($ema as $value) {
        if ($value !== null) {
            $y = (int)($chartHeight - $margin - ($value - $min) * $scaleY);
            $cx = (int)($x + $candleWidth / 2);
            if ($prevX !== null) {
                imageline($image, $prevX, $prevY, $cx, $y, $color);
            }
            $prevX = $cx;
            $prevY = $y;
        }
        $x += $candleWidth + $gap;
    }
}

function calculateBollinger(array $data, int $period = 20, float $multiplier = 2.0): array {
    $result = [];
    for ($i = 0; $i < count($data); $i++) {
        if ($i < $period - 1) {
            $result[] = [null, null, null];
            continue;
        }
        $slice = array_slice($data, $i - $period + 1, $period);
        $closes = array_column($slice, 3); // Close price
        $avg = array_sum($closes) / $period;

        // Tính độ lệch chuẩn
        $sumSq = 0;
        foreach ($closes as $c) {
            $sumSq += ($c - $avg) ** 2;
        }
        $stdDev = sqrt($sumSq / $period);

        $upper = $avg + $multiplier * $stdDev;
        $lower = $avg - $multiplier * $stdDev;

        $result[] = [$avg, $upper, $lower];
    }
    return $result;
}

function drawBollinger(GdImage $img, array $bollinger, float $startX, float $candleWidth, float $gap, float $scaleY, float $min, float $height, int $margin, int $colorMid, int $colorEdge): void {
    $x = $startX;
    $prevMid = $prevUpper = $prevLower = null;

    foreach ($bollinger as $b) {
        [$mid, $upper, $lower] = $b;

        if ($mid !== null) {
            $yMid = $height - $margin - ($mid - $min) * $scaleY;
            $yUpper = $height - $margin - ($upper - $min) * $scaleY;
            $yLower = $height - $margin - ($lower - $min) * $scaleY;

            if ($prevMid !== null) {
                imageline($img, (int)($x - $candleWidth - $gap), (int)$prevMid, (int)$x, (int)$yMid, $colorMid);
                imageline($img, (int)($x - $candleWidth - $gap), (int)$prevUpper, (int)$x, (int)$yUpper, $colorEdge);
                imageline($img, (int)($x - $candleWidth - $gap), (int)$prevLower, (int)$x, (int)$yLower, $colorEdge);
            }

            $prevMid = $yMid;
            $prevUpper = $yUpper;
            $prevLower = $yLower;
        }
        $x += $candleWidth + $gap;
    }
}

function calculate_mfi(array $d, int $period = 14): array {
    $mfi    = array_fill(0, count($d), null);
    $typPr  = [];
    $mf     = [];
    foreach ($d as $i => $r) {
        $tp = ($r[1] + $r[2] + $r[3]) / 3;
        $typPr[] = $tp;
        $mf[]    = $tp * $r[4];
    }
    for ($i = $period; $i < count($d); $i++) {
        $pos = $neg = 0;
        for ($j = $i - $period + 1; $j <= $i; $j++) {
            if ($typPr[$j] > $typPr[$j-1]) $pos += $mf[$j];
            else                             $neg += $mf[$j];
        }
        $rmf = $neg == 0 ? INF : $pos/$neg;
        $mfi[$i] = 100 - (100 / (1 + $rmf));
    }
    return $mfi;
}

function calculate_cci(array $d, int $period = 14): array {
    $cci    = array_fill(0, count($d), null);
    $tpList = [];

    // build typical price list
    foreach ($d as $r) {
        $tpList[] = ($r[1] + $r[2] + $r[3]) / 3;
    }

    // calculate CCI
    for ($i = $period - 1; $i < count($d); $i++) {
        // slice out the last $period typical prices
        $slice = array_slice($tpList, $i - $period + 1, $period);
        // 1) SMA of typical price
        $sma = array_sum($slice) / $period;
        // 2) mean deviation
        $md = 0;
        foreach ($slice as $v) {
            $md += abs($v - $sma);
        }
        $md /= $period;
        // 3) CCI formula
        $cci[$i] = $md == 0
            ? 0
            : ($tpList[$i] - $sma) / (0.015 * $md);
    }

    return $cci;
}

function calculate_wr(array $d, int $period = 14): array {
    $wr = array_fill(0, count($d), null);
    for ($i = $period-1; $i < count($d); $i++) {
        $slice = array_slice($d, $i-$period+1, $period);
        $highs = array_column($slice,1);
        $lows  = array_column($slice,2);
        $hh = max($highs); $ll = min($lows);
        $cl = $d[$i][3];
        $wr[$i] = $hh - $cl == 0
            ? 0
            : (($hh - $cl)/($hh - $ll)) * -100;
    }
    return $wr;
}

function calculate_macd(array $d, int $fast = 12, int $slow = 26, int $sig = 9): array {
    // 1) fast & slow EMAs on the close prices
    $emaFast = calculateEMA($d, $fast);
    $emaSlow = calculateEMA($d, $slow);

    // 2) MACD line = fastEMA - slowEMA
    $macdLine = [];
    foreach ($d as $i => $_) {
        if (!is_numeric($emaFast[$i]) || !is_numeric($emaSlow[$i])) {
            $macdLine[] = null;
        } else {
            $macdLine[] = $emaFast[$i] - $emaSlow[$i];
        }
    }

    // 3) Build a synthetic data array for the signal EMA
    //    Only the close (index 3) is used by calculateEMA
    $signalSource = [];
    foreach ($macdLine as $v) {
        $signalSource[] = [
            'time'  => 0,
            0       => 0.0,
            1       => 0.0,
            2       => 0.0,
            3       => is_numeric($v) ? $v : 0.0,
            4       => 0.0,
        ];
    }

    // 4) Signal line = EMA of the MACD line
    $signalLine = calculateEMA($signalSource, $sig);

    // 5) Histogram = MACD line − signal line
    $hist = [];
    foreach ($macdLine as $i => $v) {
        if (is_numeric($v) && is_numeric($signalLine[$i])) {
            $hist[] = $v - $signalLine[$i];
        } else {
            $hist[] = null;
        }
    }

    return [
        'macd'   => $macdLine,
        'signal' => $signalLine,
        'hist'   => $hist,
    ];
}
?>