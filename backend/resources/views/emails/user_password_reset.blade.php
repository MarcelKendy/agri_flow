<!DOCTYPE html>
<html>
<head>
    <title>Redefinição de Senha</title>
</head>
<body>
    <p>Olá, <strong>{{ $data['name'] }}!</strong></p>
    <p>
        <a href="http://localhost:5173/TemplateApp/login/{{ $data['uid'] }}/{{ $data['token'] }}" 
           style="display: inline-block; padding: 10px 20px; margin-right: 10px; font-size: 16px; color: white; text-decoration: none; background-color:rgb(25, 121, 47); border-radius: 5px; font-weight: bolder;">
            Clique aqui 
        </a>e redefina sua senha 😉
    </p>
    <p style="text-decoration: underline;">O link expira em 30 minutos após a solicitação.</p>
    <p><strong>I.T Sicoob Credisg</strong> - <span style="font-style: italic; font-size: 12px;">(Não responda à esse e-mail)</span></p>
</body>
</html>
