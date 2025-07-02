<?php
function getCandles($symbol = 'BTC-USDT', $interval = '4h', $limit = 300) {
    $url = "https://open-api.bingx.com/openApi/swap/v3/quote/klines"
         . "?symbol={$symbol}&interval={$interval}&limit={$limit}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    if (!$resp) return [];
    $o = json_decode($resp, true);
    if (!isset($o['data']) || !is_array($o['data'])) return [];
    // ACHTUNG: BingX gibt neueste Kerze zuletzt zurück (muss NICHT sortiert werden)
    $result = [];
    foreach ($o['data'] as $c) {
        // Nur die ersten 5 Spalten werden verwendet: [0]=openTime, [1]=open, [2]=high, [3]=low, [4]=close
        if (isset($c[0], $c[1], $c[2], $c[3], $c[4])) {
            $result[] = [
                floatval($c[0]),
                floatval($c[1]),
                floatval($c[2]),
                floatval($c[3]),
                floatval($c[4])
            ];
        }
    }
    return $result;
}

function getFuturesPrice($symbol = 'BTC-USDT') {
    $url = "https://open-api.bingx.com/openApi/swap/v2/quote/price?symbol={$symbol}";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    if (!$resp) return null;
    $o = json_decode($resp, true);
    return isset($o['data']['price']) ? floatval($o['data']['price']) : null;
}
