<?php
	//In the name of god
	//By: Arman Hosseini

    // Import config
	require_once( "config.php" );

    // Do Purchase
	if ( isset( $_POST["do_purchase"] ) )
    {
        $err = null;
        $err_type = 'danger';
        $amount = 0;

        if ( !isset( $_POST["firstname"] ) || empty( $_POST["firstname"] ) )
        {
            $err .= "نام خود را وارد نمایید !" . br;
        }

        if ( !isset( $_POST["lastname"] ) || empty( $_POST["lastname"] ) )
        {
            $err .= "نام خانوادگی خود را وارد نمایید !" . br;
        }

        if ( !isset( $_POST["email"] ) || empty( $_POST["email"] ) )
        {
            $err .= "رایانامه خود را وارد نمایید !" . br;
        }

        if ( !isset( $_POST["mobile"] ) || empty( $_POST["mobile"] ) )
        {
            $err .= "تلفن همراه خود را وارد نمایید !" . br;
        }

        if ( !isset( $_POST["education_base"] ) )
        {
            $err .= "پایه تحصیلی را انتخاب نمایید !" . br;
        }
        else
        {
            if ( !$education_base[ $_POST["education_base"] ])
                $err .= "پایه تحصیلی انتخاب شده صحیح نمی باشد!" . br;
        }

        if ( !isset( $_POST["grade"] ) )
        {
            $err .= "مقطع تحصیلی را انتخاب نمایید !" . br;
        }
        else
        {
            if ( !isset( $education_grade[ $_POST["grade"] ] ) )
                $err .= "مقطع تحصیلی انتخاب شده صحیح نمی باشد!" . br;
        }

        if ( !isset( $_POST["plan"] ) )
        {
            $err .= "لطفا یکی از طرح ها را انتخاب نمایید !" . br;
        }
        else
        {
            if ( !isset( $plans[ $_POST["education_base"] ][ $_POST["plan"] ] ) )
                $err .= "طرح انتخاب شده موجود نمی باشد!" . br;
            else
                $amount += $plans[ $_POST["education_base"] ][ $_POST["plan"] ];
        }

        if ( !isset( $_POST["addon"] ) || !is_array( $_POST["addon"] ) )
        {
            $err .= "لطفا یکی از افزودنی ها را انتخاب نمایید !" . br;
        }
        else
        {
            foreach ( $_POST["addon"] as $addon )
                if ( !array_key_exists( $addon, $addons ) )
                    $err .= "افزودنی انتخاب شده موجود نمی باشد!" . br;
                else
                    $amount += $addons[ $addon ]["price"];
        }

        // Check uploaded file when everything is right
        if ( is_null( $err ) )
        {
            $file_name = "";
            if (isset($_FILES["select_file"]) && !empty($_FILES["select_file"]["name"]))
            {
                $file_name = $_FILES["select_file"]["name"];
                $sep_dot = explode(".", $file_name);
                $file_ext = end($sep_dot);

                if (in_array($file_ext, ["txt", "pdf", "png", "jpg", "jpeg", "JPG"])) {
                    $file_name = md5(time() . $file_name) . "." . $file_ext;
                    if (!move_uploaded_file($_FILES["select_file"]["tmp_name"], "assets/img/upload/" . $file_name))
                        $err .= "مشکلی در پیوست فایل بوجود آمده است!" . br;
                } else
                    $err .= "پسوند فایل پیوست شده صحیح نمی باشد!" . br;
            }
            /*else //optional
                $err .= "شما هیچ فایلی برای پیوست انتخاب نکرده اید!" . br;*/
        }

        // Do this section if everything is right
        if ( is_null( $err ) )
        {
            $err_type = 'success';
            //$err = "ثبت نام موفقیت آمیز بود!";


            // start payment with ZARINPAL GATEWAY //
            $data = array(
                'MerchantID'  => $ZarinPal_MerchantID,
                'Amount'      => $amount,
                'CallbackURL' => $ZarinPal_CallbackURL,
                'Description' => $ZarinPal_Description
            );
            $jsonData = json_encode($data);
            $ch = curl_init($ZarinPal_PaymentRequestUrl);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ));

            $result = curl_exec($ch);
            $re = json_decode(
                $result,
                true
            );print_r(curl_error($ch));
            if ( isset( $re["Status"] ) && $re["Status"] == 100 )
            {
                $out = [ "ok" => true ];
                if ( isset( $re["Authority"] ) )
                {
                    $query = $conn->query( "INSERT INTO purchase_transaction 
VALUES( NULL, '".rand( 10000000, 99999999 )."', '".$amount."', '".$re["Authority"]."', NOW(), 0 )" );
                    $prepare = $conn->prepare("INSERT INTO `purchase_form` 
        (`id`, `firstname`, `lastname`, `email`, `mobile`, `educationBase`, `educationGrade`, `filePath`, `planId`, `addonIds`, `purchaseId`)
        VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?, ?, '".$conn->lastInsertId()."')");
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

                    // Redirect user to gateway
                    $out["payment_url"] = $ZarinPal_StartPaymentUrl . $re["Authority"];
                    header( "Location: " . $out["payment_url"] );
                    exit();
                }
            }
            ////////////////////////////////////////

        }
    }

	// Payment Callback
    if ( isset( $_GET["Authority"] ) && isset( $_GET["Status"] ) )
    {
        if ( $_GET["Status"] == "OK" ) {
            $Authority = $_GET["Authority"];
            $query = $conn->prepare("SELECT id, amount, trackingCode FROM purchase_transaction WHERE authority = ? AND status = 0");
            $query->execute(
                array($Authority)
            );

            if ($query->rowCount() == 1) {
                // Fetching purchase transaction information
                $purt         = $query->fetch(PDO::FETCH_ASSOC);
                $purchaseId   = $purt["id"];
                $amount       = $purt["amount"];
                $trackingCode = $purt["trackingCode"];

                // start payment with ZARINPAL GATEWAY //
                $data = array(
                        'MerchantID' => $ZarinPal_MerchantID,
                        'Amount'     => $amount,
                        'Authority'  => $Authority
                );
                $jsonData = json_encode($data);
                $ch = curl_init($ZarinPal_PaymentVerificationUrl);
                curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData)
                ));

                $result = curl_exec($ch);
                $re = json_decode(
                    $result,
                    true
                );

                // Payment is successful
                if (isset($re["Status"]) && $re["Status"] == 100)
                {
                    // Update purchase transaction status to successful
                    $query = $conn->query("UPDATE purchase_transaction SET status = 1 WHERE authority = " . $Authority );

                    // Fetching user information
                    $query = $conn->query("SELECT * FROM purchase_form WHERE purchaseId = " . $purchaseId );
                    $user = $query->fetch(PDO::FETCH_ASSOC);

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

                    // Send SMS to admin
                    $SMS_data = [
                            "Username" => $SMS_Username,
                            "Password" => $SMS_Password,
                            "From"     => $SMS_FromNumber,
                            "To"       => $SMS_AdminNumber,
                    ];
                    $SMS_data["Text"]  =
                        "---Admin SMS---"  . PHP_EOL .
                        "New registration" . PHP_EOL .
                        "Name: "  . $user["firstname"] . " " . $user["lastname"] . PHP_EOL .
                        "Email: " . $user["email"] . PHP_EOL .
                        "Mobile: " . $user["mobile"] . PHP_EOL
                    ;
                    file_get_contents( $SMS_URL ."?" . http_build_query( $SMS_data ) );

                    // Send SMS to user
                    $SMS_data["To"]    = $user["mobile"];
                    $SMS_data["Text"]  =
                        "---User SMS---"  . PHP_EOL .
                        "Name: "  . $user["firstname"] . " " . $user["lastname"] . PHP_EOL .
                        "Plan Name: " . $plans_name[ $user["planId"] ] . "-" . $plans[ $user["educationBase"] ][ $user["planId"] ] . " Tooman" . PHP_EOL .
                        "Tracking Code: " . $trackingCode . PHP_EOL
                    ;
                    file_get_contents( $SMS_URL . "?" . http_build_query( $SMS_data ) );


                    // Message
                    $err_type = "success";
                    $err =
                        "<h4>" . "پرداخت شما با موفقیت انجام شد!" . "</h4>" . br .
                        "<strong>" . "کد پیگیری: " . "<abbr>" . $trackingCode . "</abbr>" . "</strong>";
                } else {
                    // Message
                    $err_type = "danger";
                    $err =
                        "<h4>" . " متاسفانه پرداخت شما موفقیت آمیز نبود!" . "</h4>";
                }
            }
        }
        else
        {
            // Message
            $err_type = "danger";
            $err =
                "<h4>" . " متاسفانه پرداخت شما موفقیت آمیز نبود!" . "</h4>"
            ;
        }
    }
?>
<!DOCTYPE html>
<html dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?=$head?>

        <title>خرید طرح ویژه</title>
    </head>
    <body>
        <div class="container">
            <div class="row center-block">
                <div class="col-md-6 col-md-offset-3 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1">
                    <?=$header?>
                    <?php if ( isset($err) ): ?>
                    <div class="alert alert-<?=$err_type?>">
                        <?=$err?>
                    </div>
                    <?php endif ?>
                    <form action="" method="post" id="purchase_form" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="firstname">نام <code>*</code></label>
                            <input type="text" class="form-control" id="firstname" name="firstname">
                        </div>
                        <div class="form-group">
                            <label for="lastname">نام خانوادگی <code>*</code></label>
                            <input type="text" class="form-control" id="lastname" name="lastname">
                        </div>
                        <div class="form-group">
                            <label for="email">رایانامه <code>*</code></label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="form-group">
                            <label for="mobile">تلفن همراه <code>*</code></label>
                            <input type="text" class="form-control" id="mobile" name="mobile">
                        </div>
                        <div class="form-group">
                            <label for="education_base">پایه تحصیلی <code>*</code></label>
                            <select class="form-control" id="education_base" name="education_base">
                                <?php foreach( $education_base as $id => $base): ?>
                                    <option value="<?=$id?>"<?=(($id==0)?' selected':'')?>><?=$base?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="select_file">اننتخاب فایل</label>
                            <input type="file" class="form-control-file" id="select_file" name="select_file">
                        </div>
                        <div class="form-group">
                            <label for="grade">مقطع تحصیلی <code>*</code></label>
                            <?php foreach( $education_grade as $id => $grade): ?>
                                <div class="radio">
                                    <label><input type="radio" name="grade" value="<?=$id?>"<?=(($id==0)?' checked':'')?>><?=$grade?></label>
                                </div>
                            <?php endforeach ?>
                        </div>

                        <div class="form-group">
                            <label for="plans">قیمت طرح ها <code>*</code></label>
                            <div id="plans">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="addons">افزودنی ها</label>
                            <?php foreach( $addons as $id => $addon): ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="addon[]" price="<?=$addon['price']?>" value="<?=$id?>"><?=$addon['name']?></label>
                                </div>
                            <?php endforeach ?>
                        </div>
                        <div class="form-group">
                            <label for="price">مبلغ قابل پرداخت</label>
                            <div class="text-success">
                                <label id="price">0 تومان</label>
                                <input type="hidden" name="price" value="0" />
                            </div>
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="purchase_modal" role="dialog">
                            <div class="modal-dialog">

                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">تایید پرداخت</h4>
                                    </div>
                                    <div class="modal-body text-center">
                                        <div class="form-group">
                                            <label for="payment_method">پرداخت از طریق</label>
                                            <div>
                                                <img src="assets/img/zarinpal_logo.png" id="payment_method" class="img-thumbnail" style="max-width: 120px" alt="درگاه زرین پال">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="price">مبلغ قابل پرداخت</label>
                                            <div class="text-success">
                                                <label id="final_price">0 تومان</label>
                                                <input type="hidden" name="final_price" value="0" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer" style="text-align: right">
                                        <button type="submit" class="btn btn-success" name="do_purchase">پرداخت</button>
                                        <button type="button" class="btn btn-default" data-dismiss="modal">بازگشت</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <button type="button" id="submit" class="btn btn-info col-md-2" style="margin-left:7px">ثبت</button>
                            <button type="reset" class="btn btn-default col-md-2" >پاک کردن</button>
                        </div>
                </form>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            function formatNumber(num) {
                return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
            }

            var plans_name = <?=json_encode($plans_name)?>;
            var plans = <?=json_encode($plans)?>;

            $(function () {
                // Init plans
                $("#plans").html('');
                var i = 0; /*education base id*/
                for( j = 0;/*plan id*/ j < plans_name.length; j++ )
                    $("#plans").append(
                        '<div class="radio">\n' +
                        '<label><input type="radio" name="plan" price="'+plans[i][j]+'" value="'+j+'">'+plans_name[j]+' - '+formatNumber(plans[i][j])+' تومان </label>\n' +
                        '</div>'
                    );

                // Purchase submit
                /*$("#purchase_form").submit(function () {

                });*/
            });

            // Diffrent prices for each education base
            $("select[name='education_base']").change(function () {
                $("#plans").html('');
                var i = $(this).val();
                for( j = 0; j < plans_name.length; j++ )
                    $("#plans").append(
                        '<div class="radio">\n' +
                        '<label><input type="radio" name="plan" price="'+plans[i][j]+'" value="'+j+'">'+plans_name[j]+' - '+formatNumber(plans[i][j])+' تومان </label>\n' +
                        '</div>'
                    );
            });

            // Calculate final price
            $('body').on("click", "input[name='plan']", function () {
                var plan_price = $(this).attr("price");
                var addons_price = 0;
                $("input[name='addon[]']:checked:enabled").each(function() {
                    addons_price += parseInt( $(this).attr("price") );
                });
                var final_price = parseInt(plan_price) + addons_price;

                $("#price").text( formatNumber( final_price ) + " تومان" );
                $("input[name='price']").val( final_price  );
            });
            $('input[name="addon[]"]').change( function () {
                var addon_price = parseInt( $(this).attr("price") );
                var final_price = parseInt( $("input[name='price']").val() );

                if ( $(this).prop("checked") )
                    final_price += addon_price;
                else
                    final_price -= addon_price;

                $("#price").text( formatNumber( final_price ) + " تومان" );
                $("input[name='price']").val( final_price  );
            });

            // Submit button
            $("#submit").on("click", function () {
                var final_price = parseInt( $("input[name='price']").val() );
                if ( final_price > 0 )
                {
                    $("#final_price").text( formatNumber( final_price ) + " تومان" );
                    $("input[name='final_price']").val( final_price  );

                    $('#purchase_modal').modal('show');
                }
            });
        </script>

    </body>
</html>
