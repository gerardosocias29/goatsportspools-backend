<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Application Update</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #FAF6F2; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #FAF6F2;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #FFFFFF; border-radius: 16px; border: 1px solid #D3C9C2; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td align="center" style="padding: 40px 40px 24px 40px; border-bottom: 1px solid #E5E0DB;">
                            <img src="{{ env('APP_FRONTEND_URL') }}/img/v2_logo.png" alt="OKRNG" width="60" style="display: block; margin-bottom: 16px;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: 800; color: #101826; letter-spacing: -0.5px;">OKRNG</h1>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 32px 40px 16px 40px;">
                            <h2 style="margin: 0 0 8px 0; font-size: 26px; font-weight: 700; color: #101826;">Application Update</h2>
                            <p style="margin: 0; font-size: 16px; color: #666666;">Your commissioner application has been reviewed</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 40px;">
                            <p style="margin: 0; font-size: 16px; color: #333333;">Hello <strong style="color: #101826;">{{ $application->full_name }}</strong>,</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #101826; border-radius: 12px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <p style="margin: 0 0 12px 0; font-size: 15px; color: #FFFFFF; line-height: 1.6;">
                                            Thank you for your interest in becoming a Squares Pool Commissioner.
                                        </p>
                                        <p style="margin: 0; font-size: 15px; color: #CCCCCC; line-height: 1.6;">
                                            After careful review, we are unable to approve your application at this time.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($application->admin_note)
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F5F1ED; border-left: 4px solid #D47A3E; border-radius: 0 12px 12px 0;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 700; color: #101826;">Admin Note</h3>
                                        <p style="margin: 0; font-size: 14px; color: #444444; line-height: 1.7;">{{ $application->admin_note }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #FAF6F2; border: 2px solid #D47A3E; border-radius: 12px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 700; color: #101826;">You Can Still Enjoy OKRNG</h3>
                                        <p style="margin: 0; font-size: 14px; color: #444444; line-height: 1.7;">
                                            While you won't be able to create pools, you can still join and participate in Squares Pools created by other commissioners. Browse available pools and join the fun!
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 8px 40px 32px 40px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="background-color: #D47A3E; border-radius: 12px;">
                                        <a href="{{ env('APP_FRONTEND_URL') }}/squares" target="_blank" style="display: inline-block; padding: 16px 48px; font-size: 16px; font-weight: 700; color: #FFFFFF; text-decoration: none;">Browse Pools</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 0 40px 32px 40px;">
                            <p style="margin: 0; font-size: 14px; color: #666666;">Thank you for your understanding</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 24px 40px; border-top: 1px solid #E5E0DB; background-color: #FAFAFA; border-radius: 0 0 16px 16px;">
                            <img src="{{ env('APP_FRONTEND_URL') }}/img/v2_logo.png" alt="OKRNG" width="32" style="display: block; margin: 0 auto 12px auto; opacity: 0.6;">
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: #888888;">This email was sent from OKRNG Squares Pool</p>
                            <p style="margin: 0; font-size: 13px; color: #888888;">&copy; {{ date('Y') }} OKRNG. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
