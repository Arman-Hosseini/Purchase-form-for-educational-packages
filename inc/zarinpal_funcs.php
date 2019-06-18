<?php
/**
 * Payment functions
 * Gateway: Zarinpal.com
 */

$GLOBALS["ZarinPal_PaymentRequestUrl"]      = "https://{$ZarinPal_Mode}.zarinpal.com/pg/rest/WebGate/PaymentRequest.json";
$GLOBALS["ZarinPal_PaymentVerificationUrl"] = "https://{$ZarinPal_Mode}.zarinpal.com/pg/rest/WebGate/PaymentVerification.json";
$GLOBALS["ZarinPal_StartPaymentUrl"]        = "https://{$ZarinPal_Mode}.zarinpal.com/pg/StartPay/";

function zarinpal_send($ZarinPal_MerchantID, $amount, $ZarinPal_CallbackURL, $ZarinPal_Description)
{
    return zarinpal_curl_post($GLOBALS["ZarinPal_PaymentRequestUrl"], [
        'MerchantID'  => $ZarinPal_MerchantID,
        'Amount'      => $amount,
        'CallbackURL' => $ZarinPal_CallbackURL,
        'Description' => $ZarinPal_Description
    ]);
}

function zarinpal_verify($ZarinPal_MerchantID, $amount, $Authority)
{
    return payir_curl_post($GLOBALS["ZarinPal_PaymentVerificationUrl"], [
        'MerchantID' => $ZarinPal_MerchantID,
        'Amount'     => $amount,
        'Authority'  => $Authority
    ]);
}

function zarinpal_curl_post($url, $params)
{
    $data = json_encode($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}