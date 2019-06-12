<?php
/**
 * Payment config
 * Gateway: Zarinpal
 * Mode:   [ 'sandbox', 'www' ]
 * Status: [ 0 => disable, 1 => enable ]
 */

$ZarinPal_Status                 = 0;
$ZarinPal_Mode                   = 'www'; // Use sandbox for test purposes
$ZarinPal_MerchantID             = 'YOUR-MERCHANT-ID-XXXXXXXXXXXXXXXXXXX'; // Should be 36 char
$ZarinPal_CallbackURL            = dirname("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"]) . "/index.php";
$ZarinPal_Description            = 'خرید پکیج آموزشی';

$ZarinPal_PaymentRequestUrl      = "https://{$ZarinPal_Mode}.zarinpal.com/pg/rest/WebGate/PaymentRequest.json";
$ZarinPal_PaymentVerificationUrl = "https://{$ZarinPal_Mode}.zarinpal.com/pg/rest/WebGate/PaymentVerification.json";
$ZarinPal_StartPaymentUrl        = "https://{$ZarinPal_Mode}.zarinpal.com/pg/StartPay/";

/**
 * Payment config
 * Gateway: Pay.ir
 * Mode: [ 'test', 'www' ]
 * Status: [ 0 => disable, 1 => enable ]
 */

// Pay.ir funcs file
require_once( "pay_ir_funcs.php" );

$PayIr_Status       = 1;
$PayIr_Api          = 'YOUR-API-KEY'; // Use test statement for test purposes
$PayIr_FactorNumber = time();
$PayIr_CallbackURL  = dirname("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"]) . "/index.php";
$PayIr_Description  = "توضیحات";

$PayIr_Url = "https://pay.ir/pg/";