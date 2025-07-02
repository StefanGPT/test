<?php
function calculateRSI(array $closes, int $period = 14): ?float {
    $n = count($closes);
    if ($n < $period + 1) return null;

    $avgGain = $avgLoss = 0;
    for ($i = 1; $i <= $period; $i++) {
        $change = $closes[$i] - $closes[$i - 1];
        $avgGain += max($change, 0);
        $avgLoss += max(-$change, 0);
    }
    $avgGain /= $period;
    $avgLoss /= $period;

    for ($i = $period + 1; $i < $n; $i++) {
        $change = $closes[$i] - $closes[$i - 1];
        $gain = max($change, 0);
        $loss = max(-$change, 0);
        $avgGain = (($avgGain * ($period - 1)) + $gain) / $period;
        $avgLoss = (($avgLoss * ($period - 1)) + $loss) / $period;
    }

    if ($avgLoss == 0) return 100.0;
    $rs = $avgGain / $avgLoss;
    $rsi = 100 - (100 / (1 + $rs));
    return round($rsi, 2);
}
