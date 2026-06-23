<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $purposeLabel }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                    
                    <tr>
                        <td style="background-color: #422168; color: #ffffff; padding: 30px; text-align: center;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: 900; color: #CAF204;">Овощная база</h1>
                            <p style="margin: 10px 0 0; font-size: 14px; color: #00F3B5;">{{ $purposeLabel }}</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px; font-size: 16px; color: #422168; font-weight: bold;">
                                Здравствуйте, {{ $userName }}!
                            </p>
                            
                            <p style="margin: 0 0 20px; font-size: 14px; color: #555555; line-height: 1.6;">
                                Для подтверждения операции введите следующий 6-значный код:
                            </p>
                            
                            <table role="presentation" style="width: 100%; margin: 30px 0;">
                                <tr>
                                    <td align="center" style="background-color: #E8FC8C; border: 2px dashed #CAF204; border-radius: 12px; padding: 25px;">
                                        <p style="margin: 0; font-size: 36px; font-weight: bold; letter-spacing: 10px; color: #422168; font-family: 'Courier New', monospace;">
                                            {{ $verificationCode->code }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 0 0 10px; font-size: 14px; color: #555555;">
                                <strong>Код действителен до:</strong> {{ $verificationCode->expires_at->format('d.m.Y H:i') }}
                            </p>
                            <p style="margin: 0 0 20px; font-size: 14px; color: #555555;">
                                <strong>Осталось попыток:</strong> {{ 5 - $verificationCode->attempts }}
                            </p>
                            
                            <p style="margin: 30px 0 0; font-size: 13px; color: #888888; line-height: 1.6;">
                                Если вы не запрашивали эту операцию — проигнорируйте письмо.
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style="background-color: #f9f9f9; padding: 20px; text-align: center;">
                            <p style="margin: 0; font-size: 12px; color: #999999;">
                                © {{ date('Y') }} Овощная база. Все права защищены.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>