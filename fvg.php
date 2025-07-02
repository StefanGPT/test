<?php
function findFVGs($candles) {
    $fvgs = [];
    for ($i = 2; $i < count($candles); $i++) {
        $low = floatval($candles[$i][3]);
        $high_prev2 = floatval($candles[$i-2][2]);
        $high = floatval($candles[$i][2]);
        $low_prev2 = floatval($candles[$i-2][3]);

        if ($low > $high_prev2) {
            $fvgs[] = [
                'type' => 'bullish',
                'from' => $high_prev2,
                'to' => $low,
                'index' => $i
            ];
        }
        if ($high < $low_prev2) {
            $fvgs[] = [
                'type' => 'bearish',
                'from' => $high,
                'to' => $low_prev2,
                'index' => $i
            ];
        }
    }
    return $fvgs;
}

function isTapped($price, $fvg) {
    return $price <= $fvg['to'] && $price >= $fvg['from'];
}

function detectSweepAndMomentum($candles, $side = 'long', $lookback = 10) {
    $n = count($candles);
    if ($n < $lookback + 2) return false;

    $recent = array_slice($candles, -($lookback+1));
    $last = end($recent);

    if ($side === 'long') {
        $lows = array_map(fn($c) => floatval($c[3]), $recent);
        $minLow = min($lows);
        $lastLow = floatval($last[3]);
        $lastClose = floatval($last[4]);
        $lastOpen = floatval($last[1]);
        $bodies = array_map(fn($c) => abs(floatval($c[4]) - floatval($c[1])), $recent);
        $avgBody = array_sum($bodies) / count($bodies);
        $lastBody = abs($lastClose - $lastOpen);

        if ($lastLow <= $minLow && $lastClose > $lastOpen && $lastBody > $avgBody*1.2) {
            return true;
        }
    }
    if ($side === 'short') {
        $highs = array_map(fn($c) => floatval($c[2]), $recent);
        $maxHigh = max($highs);
        $lastHigh = floatval($last[2]);
        $lastClose = floatval($last[4]);
        $lastOpen = floatval($last[1]);
        $bodies = array_map(fn($c) => abs(floatval($c[4]) - floatval($c[1])), $recent);
        $avgBody = array_sum($bodies) / count($bodies);
        $lastBody = abs($lastClose - $lastOpen);

        if ($lastHigh >= $maxHigh && $lastClose < $lastOpen && $lastBody > $avgBody*1.2) {
            return true;
        }
    }
    return false;
}
