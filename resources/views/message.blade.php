<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8faf7;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8faf7;
            padding-bottom: 40px;
        }
        .container {
            max-width: 600px;
            background-color: #ffffff;
            margin: 20px auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: 1px solid #e1e8df;
        }
        .header {
            background-color: #2e7d32; /* Deep Agricultural Green */
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        .content {
            padding: 40px 30px;
            color: #444444;
            line-height: 1.6;
        }
        .greeting {
            font-size: 18px;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 20px;
        }
        .message-card {
            background-color: #f1f8e9; /* Soft leaf green */
            border-left: 4px solid #4caf50;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .message-text {
            color: #2d3436;
            font-style: italic;
            font-size: 16px;
        }
        .btn-wrapper {
            text-align: center;
            margin-top: 30px;
        }
        .btn {
            background-color: #4caf50;
            color: #ffffff !important;
            padding: 14px 35px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            display: inline-block;
            transition: background 0.3s ease;
        }
        .footer {
            text-align: center;
            padding: 25px;
            font-size: 12px;
            color: #888888;
            background-color: #fafafa;
            border-top: 1px solid #eeeeee;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="icon">🌱</div>
                <h1>Mazr3tk</h1>
            </div>

            <div class="content">
                <div class="greeting">Hi {{ $data['receiver'] }},</div>
                
                <p>You have received a new message from <strong>{{ $data['sender'] }}</strong> regarding your agricultural updates.</p>

                <div class="message-card">
                    <div class="message-text">
                        "{{ $data['content'] }}"
                    </div>
                </div>

                <p>To reply or view more details, please head over to your Messages.</p>
            </div>

            <div class="footer">
                <p>Sent via Mazr3tk</p>
                <p>&copy; {{ date('Y') }} All Rights Reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>