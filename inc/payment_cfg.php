<?php
/**
 * Payment config
 * Gateway: Zarinpal
 * Mode:   [ 'sandbox', 'www' ]
 * Status: [ 0 => disable, 1 => enable ]
 */

$ZarinPal_Mode                   = 'www'; // Use sandbox for test purposes

// Zarinpal funcs file
require_once( "zarinpal_funcs.php" );

$ZarinPal_Status                 = 1;
$ZarinPal_MerchantID             = 'YOUR-MERCHANT-ID-XXXXXXXXXXXXXXXXXXX'; // Should be 36 char
$ZarinPal_CallbackURL            = dirname("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"]) . "/index.php";
$ZarinPal_Description            = 'خرید پکیج آموزشی';


/**
 * Payment config
 * Gateway: Pay.ir
 * Mode:   [ 'test', 'www' ]
 * Status: [ 0 => disable, 1 => enable ]
 */

// Pay.ir funcs file
require_once( "pay_ir_funcs.php" );

$PayIr_Status       = 1;
$PayIr_Api          = 'YOUR-API-KEY'; // Use test statement for test purposes
$PayIr_FactorNumber = time();
$PayIr_CallbackURL  = dirname("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["SCRIPT_NAME"]) . "/index.php";
$PayIr_Description  = "توضیحات";