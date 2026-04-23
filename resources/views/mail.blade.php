<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>Email verifie</title>
  <style>
    body {
      background-color: #f2f2f2;
      font-family: Arial, sans-serif;
      padding: 20px;
    }

    .email-box {
      max-width: 400px;
      margin: auto;
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      text-align: center;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      color: #4CAF50;
    }

    .code {
      font-size: 24px;
      font-weight: bold;
      background-color: #eee;
      padding: 10px 20px;
      border-radius: 5px;
      display: inline-block;
      margin-top: 15px;
      letter-spacing: 2px;
    }

    p {
      color: #333;
      font-size: 15px;
    }
  </style>
</head>
<body>
  <div class="email-box">
    <h2>Hello {{$data['name']}}</h2>
    <p> Thank you for join us , use this code to verifie your email</p>
    <div class="code">{{$data['code']}}</div>
    <p>code will expired after 2 mintues</p>
  </div>
</body>
</html>