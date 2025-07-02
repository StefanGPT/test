<?php
function calculateRSI(array $closes, int $period = 14): ?float {
    $n = count($closes);
    if ($n < $period + 1) return null;

    // **Korrekte Berechnung nach Wilder**
    $gains = [];
    $losses = [];
    for ($i = 1; $i <= $period; $i++) {
        $change = $closes[$i] - $closes[$i-1];
        $gains[] = $change > 0 ? $change : 0;
        $losses[] = $change < 0 ? abs($change) : 0;
    }
    $avgGain = array_sum($gains) / $period;
    $avgLoss = array_sum($losses) / $period;

    // Glättung nach Wilder
    for ($i = $period + 1; $i < $n; $i++) {
        $change = $closes[$i] - $closes[$i-1];
        $gain = $change > 0 ? $change : 0;
        $loss = $change < 0 ? abs($change) : 0;
        $avgGain = (($avgGain * ($period - 1)) + $gain) / $period;
        $avgLoss = (($avgLoss * ($period - 1)) + $loss) / $period;
    }
    if ($avgLoss == 0) return 100.0;
    $rs = $avgGain / $avgLoss;
    $rsi = 100 - (100 / (1 + $rs));
    return round($rsi, 2);
}
