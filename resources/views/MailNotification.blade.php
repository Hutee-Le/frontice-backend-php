<!DOCTYPE html>
<html>

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Thông báo từ Frontice</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                color: #333;
                margin: 0;
                padding: 0;
            }

            .email-container {
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            .email-header {
                text-align: center;
                background-color: #4CAF50;
                color: #fff;
                padding: 10px;
                border-radius: 8px 8px 0 0;
            }

            .email-body {
                padding: 20px;
            }

            .email-body p {
                line-height: 1.6;
                margin: 15px 0;
            }

            .email-footer {
                text-align: center;
                margin-top: 30px;
                font-size: 12px;
                color: #888;
            }
        </style>
    </head>

    <body>
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <h1>Thông báo từ Frontice</h1>
            </div>

            <!-- Body -->
            <div class="email-body">
                <p>Xin chào, <b>{{ $details['username'] }}</b>!</p>

                <p>{{ $details['message'] }}</p>

                <p>Chúc bạn một ngày tốt lành!</p>
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <p>&copy; {{ date('Y') }} FRONTICE. Mọi quyền được bảo lưu.</p>
            </div>
        </div>
    </body>

</html>
