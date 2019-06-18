<?php
/**
 * Payment functions
 * Gateway: Pay.ir
 */

$GLOBALS["PayIr_Url"] = "https://pay.ir/pg/";

function payir_send($api, $amount, $redirect, $mobile = null, $factorNumber = null, $description = null) {
    return payir_curl_post($GLOBALS["PayIr_Url"] . 'send', [
        'api'          => $api,
        'amount'       => $amount,
        'redirect'     => $redirect,
        'mobile'       => $mobile,
        'factorNumber' => $factorNumber,
        'description'  => $description,
    ]);
}

function payir_verify($api, $token) {
    return payir_curl_post($GLOBALS["PayIr_Url"] . 'verify', [
        'api' 	=> $api,
        'token' => $token,
    ]);
}

function payir_curl_post($url, $params)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}