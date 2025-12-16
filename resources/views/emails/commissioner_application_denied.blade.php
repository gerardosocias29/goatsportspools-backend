<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-radius: 0 0 8px 8px; }
        .icon { font-size: 48px; margin-bottom: 10px; }
        .message { font-size: 16px; margin: 20px 0; }
        .info-box { background-color: #FEE2E2; padding: 15px; border-left: 4px solid #EF4444; margin: 20px 0; border-radius: 4px; }
        .button { display: inline-block; padding: 15px 40px; background-color: #D47A3E; color: white; text-decoration: none; border-radius: 8px; margin-top: 20px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; color: #777; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon"></div>
            <h1 style="margin: 0;">Commissioner Application Update</h1>
        </div>
        <div class="content">
            <p>Dear {{ $application->full_name }},</p>

            <p class="message">
                Thank you for your interest in becoming a Squares Pool Commissioner. After careful review, we regret to inform you that your application has not been approved at this time.
            </p>

            @if($application->admin_note)
            <div class="info-box">
                <p style="margin: 0; font-weight: bold;">Reason:</p>
                <p style="margin: 5px 0 0;">{{ $application->admin_note }}</p>
            </div>
            @endif

            <p>
                We appreciate the time and effort you put into your application. While we're unable to approve your request at this moment, we encourage you to continue participating in Squares Pools as a player.
            </p>

            <p style="margin-top: 20px;">
                <strong>You can still:</strong><br>
                 Join and play in existing pools<br>
                 Enjoy the full player experience<br>
                 Stay updated on future opportunities
            </p>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ env('FRONTEND_URL') }}/v2/pools" class="button">Browse Available Pools</a>
            </p>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                If you have any questions or would like more information, please feel free to contact our support team.
            </p>
        </div>
        <div class="footer">
            <p>Thank you for being part of OKRNG Squares Pools</p>
            <p>This is an automated email from OKRNG Squares Pools</p>
        </div>
    </div>
</body>
</html>
