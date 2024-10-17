<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <title>Linkyi Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="Version" content="v4.3.0" />

    <!-- favicon -->
    <link rel="shortcut icon" href="/dist/assets/images/favicon.ico" />
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700&display=swap" rel="stylesheet">
    <!-- Main Css -->
    <!-- <link href="css/style.css" rel="stylesheet" type="text/css" /> -->
</head>

<body style="font-family: Nunito, sans-serif; font-size: 15px; font-weight: 400;">

    <!-- Hero Start -->
    <div style="margin-top: 50px;">
        <table cellpadding="0" cellspacing="0"
            style="font-family: Nunito, sans-serif; font-size: 15px; font-weight: 400; max-width: 600px; border: none; margin: 0 auto; border-radius: 6px; overflow: hidden; background-color: #fff; box-shadow: 0 0 3px rgba(60, 72, 88, 0.15);">
            <thead>
                <tr>
                    <td align="center"
                        style="font-size:0px;padding:10px 25px;padding-bottom:30px;word-break:break-word;">
                        <img style="margin-top:20px;margin-bottom: 5px;width:190px;"
                            src="https://storage.googleapis.com/linkyi-storage/assets/linkyshop.png" alt=""
                            srcset="">
                    </td>
                </tr>
                <tr>
                    <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <p style="border-top:solid 1px #C4C4C4;font-size:1px;margin:0px auto;width:100%;">
                        </p>
                    </td>
                </tr>
            </thead>

            <tbody>

                @yield('content')

                <tr>
                    <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
                        <br>
                    </td>
                </tr>
                <tr style="margin-top:20px;">
                    <td style="padding: 16px 8px; color: #8492a6; background-color: #f8f9fc; text-align: center;">
                        ©
                        <script>
                            document.write(new Date().getFullYear())
                        </script> Linkyi Shop.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Hero End -->
</body>

</html>
