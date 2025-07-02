<?php
require_once 'bingx.php';
require_once 'rsi.php';
require_once 'fvg.php';

$symbol = 'BTC-USDT';
$interval = '4h';
$limit = 300;

// === 1. Preis und RSI(14) auf 4h holen ===
$price = getFuturesPrice($symbol);
$candles_4h = getCandles($symbol, $interval, $limit);

$closes = array_map(fn($c) => floatval($c[4]), $candles_4h);
$rsi = calculateRSI($closes, 14);

// === 2. SMC/FVG/Sweep-Analyse ===
$h4_fvgs = findFVGs(array_slice($candles_4h, -100));
$m15_candles = getCandles($symbol, '15m', 150);

// Debug: Zeige die letzten 5 abgeschlossenen M15-Kerzen mit Zeitstempel und Close
echo "<b>Letzte 5 M15-Kerzen (UTC, openTime & Close):</b><br>";
for ($i = count($m15_candles)-6; $i < count($m15_candles)-1; $i++) {
    if (!isset($m15_candles[$i])) continue;
    $c = $m15_candles[$i];
    $timestamp = date('Y-m-d H:i:s', intval($c[0]) / 1000);
    echo "[$timestamp] Close: {$c[4]}<br>";
}
// Die letzte abgeschlossene Kerze ist die vorletzte im Array:
$current_m15 = floatval($m15_candles[count($m15_candles)-2][4]);
echo "<br>Aktueller M15-Close (letzte abgeschlossene Kerze): <b>$current_m15</b><br><br>";

echo "<h2>BTC/USDT – Analyse</h2>";
echo "Aktueller Preis (4h): <b>$price</b> USD<br>";
echo "RSI(14) auf 4h: <b>$rsi</b><br><br>";

echo "<h3>SMC/FVG/Sweep-Strategie (Kollege Miki Montana)</h3>";

if ($h4_fvgs) {
    // Prüfe auf Tap in eine H4-FVG-Zone
    $tap = null;
    foreach ($h4_fvgs as $fvg) {
        if (isTapped($current_m15, $fvg)) {
            $tap = $fvg;
            break;
        }
    }
    echo "Aktueller M15-Close: <b>$current_m15</b><br>";
    if ($tap) {
        echo "H4-FVG erkannt! Bereich: <b>{$tap['from']} - {$tap['to']}</b> (" . strtoupper($tap['type']) . ")<br>";
        echo "→ Der aktuelle Preis tappt in diese FVG-Zone.<br>";

        $sweep_detected = detectSweepAndMomentum($m15_candles, $tap['type'] === 'bullish' ? 'long' : 'short', 10);
        if ($sweep_detected) {
            echo "<b>SWEEP & MOMENTUM erkannt im M15! Potenzieller ENTRY!</b><br>";

            $lastCandle = $m15_candles[count($m15_candles)-2];
            if ($tap['type'] === 'bullish') {
                $sl = floatval($lastCandle[3]) - 5;
                $tp = $current_m15 + ($current_m15 - $sl) * 2;
                echo "Long Entry: <b>$current_m15</b><br>";
                echo "Stop-Loss (SL): <b>$sl</b><br>";
                echo "Take-Profit (TP): <b>$tp</b><br>";
            } else {
                $sl = floatval($lastCandle[2]) + 5;
                $tp = $current_m15 - ($sl - $current_m15) * 2;
                echo "Short Entry: <b>$current_m15</b><br>";
                echo "Stop-Loss (SL): <b>$sl</b><br>";
                echo "Take-Profit (TP): <b>$tp</b><br>";
            }
        } else {
            echo "Noch KEIN Sweep+Momentum im M15 erkannt.<br>";
        }
    } else {
        echo "Kein aktiver Tap in einer H4-FVG-Zone.<br>";
    }
} else {
    echo "Keine H4-FVG-Zonen gefunden.<br>";
}
