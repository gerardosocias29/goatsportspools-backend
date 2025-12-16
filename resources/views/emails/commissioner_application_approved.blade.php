<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #22C55E 0%, #16A34A 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-radius: 0 0 8px 8px; }
        .icon { font-size: 48px; margin-bottom: 10px; }
        .message { font-size: 16px; margin: 20px 0; }
        .highlight { background-color: #D4F8E8; padding: 15px; border-left: 4px solid #22C55E; margin: 20px 0; border-radius: 4px; }
        .button { display: inline-block; padding: 15px 40px; background-color: #D47A3E; color: white; text-decoration: none; border-radius: 8px; margin-top: 20px; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; color: #777; font-size: 12px; }
        ul { list-style: none; padding: 0; }
        ul li { padding: 8px 0; padding-left: 24px; position: relative; }
        ul li:before { content: ""; position: absolute; left: 0; color: #22C55E; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon"></div>
            <h1 style="margin: 0;">Congratulations!</h1>
            <p style="margin: 10px 0 0; font-size: 18px;">Your Commissioner Application Has Been Approved</p>
        </div>
        <div class="content">
            <p>Dear {{ $application->full_name }},</p>

            <p class="message">
                We're excited to inform you that your application to become a Squares Pool Commissioner has been <strong>approved</strong>!
            </p>

            <div class="highlight">
                <strong>What's Next?</strong>
                <p style="margin: 10px 0 0;">You now have commissioner privileges and can start creating and managing your own Squares Pools.</p>
            </div>

            <p><strong>As a Commissioner, you can now:</strong></p>
            <ul>
                <li>Create unlimited Squares Pools</li>
                <li>Manage pool settings and configurations</li>
                <li>Assign numbers to pools</li>
                <li>Calculate winners for each quarter</li>
                <li>Approve player credit requests</li>
                <li>View comprehensive pool statistics</li>
            </ul>

            @if($application->admin_note)
            <div style="margin: 20px 0; padding: 15px; background-color: white; border-left: 3px solid #D47A3E; border-radius: 4px;">
                <p style="margin: 0; color: #666; font-size: 14px;"><strong>Note from Admin:</strong></p>
                <p style="margin: 5px 0 0;">{{ $application->admin_note }}</p>
            </div>
            @endif

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ env('FRONTEND_URL') }}/v2/squares-admin" class="button">Access Commissioner Dashboard</a>
            </p>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                If you have any questions or need assistance, please don't hesitate to contact our support team.
            </p>
        </div>
        <div class="footer">
            <p>Welcome to the OKRNG Commissioner Community!</p>
            <p>This is an automated email from OKRNG Squares Pools</p>
        </div>
    </div>
</body>
</html>
