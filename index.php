<?php
	//In the name of god
	//By: Arman Hosseini
	require_once( "config.php" );
	print_R($_POST);
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
                    <form action="" method="post">
                        <div class="form-group">
                            <label for="firstname">نام </label>
                            <input type="text" class="form-control" id="firstname" name="firstname">
                        </div>
                        <div class="form-group">
                            <label for="lastname">نام خانوادگی </label>
                            <input type="text" class="form-control" id="lastname" name="lastname">
                        </div>
                        <div class="form-group">
                            <label for="education_base">پایه تحصیلی</label>
                            <select class="form-control" id="education_base" name="education_base">
                                <option value="1">نهم</option>
                                <option value="2">دهم</option>
                                <option value="3">یازدهم</option>
                                <option value="4">دوازدهم</option>
                                <option value="5">فارغ التحصیل</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="grade">مقطع تحصیلی</label>
                            <div class="radio">
                                <label><input type="radio" name="grade" value="1" checked>دبیرستان</label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="plans">قیمت طرح ها</label>
                            <div id="plans">

                            </div>
                        </div>
                        <div class="form-group">
                            <label for="addons">افزودنی ها</label>
                            <div class="checkbox">
                                <label><input type="checkbox" name="addon[]" value="700">افزودنی ۱</label>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="addon[]" value="500">افزودنی ۲</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="price">مبلغ قابل پرداخت</label>
                            <div class="text-success">
                                <label id="price">0 تومان</label>
                                <input type="hidden" name="price" value="0" />
                            </div>

                        </div>
                        <div class="row">
                            <button type="submit" class="btn btn-success col-md-2" style="margin-left:7px">خرید</button>
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

            var plans_name = [
                'وی آی پی',
                'طلایی',
                'نقره ای',
                'برنزی'
            ];
            var plans = {
                1: [
                    900,
                    700,
                    600,
                    500
                ],
                2: [
                    800,
                    600,
                    500,
                    400,
                ],
                3: [
                    700,
                    500,
                    400,
                    300,
                ],
                4: [
                    600,
                    400,
                    300,
                    200,
                ],
                5: [
                    9579000,
                    300,
                    200,
                    100,
                ],
            };

            $(function () {
                // Init plans
                $("#plans").html('');
                var i = 1;
                for( j = 0; j < plans_name.length; j++ )
                    $("#plans").append(
                        '<div class="radio">\n' +
                        '<label><input type="radio" name="plan" value="'+plans[i][j]+'">'+plans_name[j]+' - '+formatNumber(plans[i][j])+' تومان </label>\n' +
                        '</div>'
                    );

            });

            // Diffrent prices for each education base
            $("select[name='education_base']").change(function () {
                $("#plans").html('');
                var i = $(this).val();
                for( j = 0; j < plans_name.length; j++ )
                    $("#plans").append(
                        '<div class="radio">\n' +
                        '<label><input type="radio" name="plan" value="'+plans[i][j]+'">'+plans_name[j]+' - '+formatNumber(plans[i][j])+' تومان </label>\n' +
                        '</div>'
                    );
            });

            // Calculate final price
            $('body').on("click", "input[name='plan']", function () {
                var plan_price = $(this).val();
                var addons_price = 0;
                $("input[name='addon[]']:checked:enabled").each(function() {
                    addons_price += parseInt( $(this).val() );
                });
                var final_price = parseInt(plan_price) + addons_price;

                $("#price").text( formatNumber( final_price ) + " تومان" );
                $("input[name='price']").val( final_price  );
            });
            $('input[name="addon[]"]').change( function () {
                var addon_price = parseInt( $(this).val() );
                var final_price = parseInt( $("input[name='price']").val() );

                if ( $(this).prop("checked") )
                    final_price += addon_price;
                else
                    final_price -= addon_price;

                $("#price").text( formatNumber( final_price ) + " تومان" );
                $("input[name='price']").val( final_price  );
            });
        </script>

    </body>
</html>
