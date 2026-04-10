<!DOCTYPE html>
<html>
<head>
    <title>Código de Login OTP</title>
    <style>

        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 0;
            display: flex;
            justify-content: center;     
        }

        .container {
            box-shadow: rgba(38, 88, 109, 0.699) 0px 2px 4px 0px, rgba(50, 110, 136, 0.651) 0px 2px 16px 0px;
            background: linear-gradient(90deg, rgb(255, 255, 255), rgb(255, 255, 236));
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
        }

        .otp-box {
            box-shadow: rgba(14, 30, 37, 0.12) 0px 2px 4px 0px, rgba(14, 30, 37, 0.32) 0px 2px 16px 0px;
            display: flex;
            justify-content: center;
            align-items: center;        
            text-align: center;
            border: 3px solid #9ef5ef;
            border-radius: 10px;
            font-size: 24px;
            font-weight: bold;            
            background: linear-gradient(90deg, rgb(71, 206, 199), rgb(47, 116, 172)) !important;
            text-shadow: 2px rgb(255, 255, 255);
            letter-spacing: 5px;
            width: 150px;  
            padding-left: 5px;
            padding-top: 10px;
            padding-bottom: 10px;
            margin: 20px auto; /* Center the box horizontally */
        }

        .info {
            font-size: 14px;
            color: #555;
        }

    </style>
</head>
<body>

    <div class="container">

        <p>Olá, <strong>{{ $data['name'] }}!</strong></p> 

        <p><strong>Aqui está seu código de login:</strong></p>        

        <div class="otp-box">
            {{ $data['otp'] }}
        </div>

        <p class="info"><u>O código expira em 30 minutos após a solicitação.</u></p>

        <p><strong>AgriFlow</strong> - <span style="font-style: italic; font-size: 12px;">(Não responda a esse e-mail)</span></p>
        
    </div>

</body>
</html>
