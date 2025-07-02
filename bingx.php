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
    return isset($o['data']) && is_array($o['data']) ? $o['data'] : [];
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
