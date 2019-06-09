<?php
/**
 * Payment config
 * Gateway: Zarinpal
 * Mode: [ 'sandbox', 'www' ]
 */

$mode                            = 'sandbox';
$ZarinPal_MerchantID             = 'YOUR-MERCHANT-ID-XXXXXXXXXXXXXXXXXXX'; // Should be 36 char
$ZarinPal_CallbackURL            = dirname("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"]) . "/index.php";
$ZarinPal_Description            = 'خرید پکیج آموزشی';

$ZarinPal_PaymentRequestUrl      = "https://{$mode}.zarinpal.com/pg/rest/WebGate/PaymentRequest.json";
$ZarinPal_PaymentVerificationUrl = "https://{$mode}.zarinpal.com/pg/rest/WebGate/PaymentVerification.json";
$ZarinPal_StartPaymentUrl        = "https://{$mode}.zarinpal.com/pg/StartPay/";