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

    $result = [];
    foreach ($o['data'] as $c) {
        // if numeric indexes exist use them directly
        if (isset($c[0]) && isset($c[4])) {
            $result[] = [
                floatval($c[0]),
                floatval($c[1]),
                floatval($c[2]),
                floatval($c[3]),
                floatval($c[4])
            ];
            continue;
        }

        // otherwise try to map common field names
        if (isset($c['openTime']) || isset($c['time'])) {
            $openTime = $c['openTime'] ?? $c['time'];
            $open     = $c['open']  ?? $c['o'] ?? null;
            $high     = $c['high']  ?? $c['h'] ?? null;
            $low      = $c['low']   ?? $c['l'] ?? null;
            $close    = $c['close'] ?? $c['c'] ?? null;

            if ($openTime !== null && $open !== null && $high !== null && $low !== null && $close !== null) {
                $result[] = [
                    floatval($openTime),
                    floatval($open),
                    floatval($high),
                    floatval($low),
                    floatval($close)
                ];
            }
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
