<?php
/**
 * Functional functions for purchase process
 */

function createTrackingCode()
{
    global $conn;

    $trackingCode = rand(10000000, 99999999);
    // Avoid creating duplicate tracking code.
    $query = $conn->query("SELECT `id` FROM `purchase_transaction` WHERE `trackingCode` = '{$trackingCode}'");
    while ($query->rowCount() == 1)
    {
        $trackingCode = rand(10000000, 99999999);
        $query = $conn->query("SELECT `id` FROM `purchase_transaction` WHERE `trackingCode` = '{$trackingCode}'");
    }

    return $trackingCode;
}

function newPurchaseTransaction($authority)
{
    global $conn, $trackingCode, $amount, $_POST, $file_name;

    $query = $conn->query("INSERT INTO `purchase_transaction` 
VALUES( NULL, '" . $trackingCode . "', '" . $amount . "', '" . $authority . "', NOW(), 0 )");
    $prepare = $conn->prepare("INSERT INTO `purchase_form` 
        (`id`, `firstname`, `lastname`, `email`, `mobile`, `educationBase`, `educationGrade`, `filePath`, `planId`, `addonIds`, `purchaseId`)
        VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?, ?, '" . $conn->lastInsertId() . "')");
    $prepare->execute(
        [
            $_POST["firstname"],
            $_POST["lastname"],
            $_POST["email"],
            $_POST["mobile"],
            $_POST["education_base"],
            $_POST["grade"],
            $file_name,
            $_POST["plan"],
            json_encode($_POST["addon"]),

        ]
    );
}

function sendEmail()
{
    global $user, $plans_name, $trackingCode, $plans;

    // Send E-Mail
    $to      = 'any-email@mail.com';
    $subject = 'New registration';
    $message =
        "---Registration Email---" . PHP_EOL .
        "Name: "  . $user["firstname"] . " " . $user["lastname"] . PHP_EOL .
        "Email: " . $user["email"] . PHP_EOL .
        "Mobile: " . $user["mobile"] . PHP_EOL .
        "Plan Name: " . $plans_name[ $user["planId"] ] . "-" . $plans[ $user["educationBase"] ][ $user["planId"] ] . " Tooman" . PHP_EOL .
        "Tracking Code: " . $trackingCode . PHP_EOL
    ;
    $headers = 'From: no-reply@yoursite.com' . "\r\n" .
        'Reply-To: ' . $to . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    mail($to, $subject, $message, $headers);
}

function sendSMSTo($who)
{
    global $SMS_Username, $SMS_Password, $SMS_FromNumber, $SMS_AdminNumber, $user, $SMS_URL, $plans_name, $trackingCode, $plans;

    // Send SMS to admin
    $SMS_data = [
        "Username" => $SMS_Username,
        "Password" => $SMS_Password,
        "From"     => $SMS_FromNumber,
        "To"       => $SMS_AdminNumber,
    ];

    if ( $who == "admin" )
    {
        $SMS_data["Text"] =
            "---Admin SMS---" . PHP_EOL .
            "New registration" . PHP_EOL .
            "Name: " . $user["firstname"] . " " . $user["lastname"] . PHP_EOL .
            "Email: " . $user["email"] . PHP_EOL .
            "Mobile: " . $user["mobile"] . PHP_EOL;
    }
    if ( $who == "user" )
    {
        // Send SMS to user
        $SMS_data["To"]    = $user["mobile"];
        $SMS_data["Text"]  =
            "---User SMS---"  . PHP_EOL .
            "Name: "  . $user["firstname"] . " " . $user["lastname"] . PHP_EOL .
            "Plan Name: " . $plans_name[ $user["planId"] ] . "-" . $plans[ $user["educationBase"] ][ $user["planId"] ] . " Tooman" . PHP_EOL .
            "Tracking Code: " . $trackingCode . PHP_EOL
        ;
    }

    file_get_contents( $SMS_URL ."?" . http_build_query( $SMS_data ) );
}

function sendNotifications()
{
    sendEmail();
    sendSMSTo("admin");
    sendSMSTo("user");
}

function successfulPurchase($Authority)
{
    global $conn, $purchaseId;

    // Update purchase transaction status to successful
    $query = $conn->query("UPDATE purchase_transaction SET status = 1 WHERE authority = '" . $Authority . "'" );

    // Fetching user information
    $query = $conn->query("SELECT * FROM purchase_form WHERE purchaseId = " . $purchaseId );
    $user = $query->fetch(PDO::FETCH_ASSOC);

    return $user;
}