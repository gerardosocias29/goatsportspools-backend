<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #D47A3E; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background-color: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-radius: 0 0 8px 8px; }
        .info-row { margin: 15px 0; }
        .label { font-weight: bold; color: #555; }
        .value { margin-top: 5px; padding: 10px; background-color: white; border-left: 3px solid #D47A3E; }
        .button { display: inline-block; padding: 12px 30px; background-color: #D47A3E; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { text-align: center; margin-top: 20px; color: #777; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Commissioner Application</h1>
        </div>
        <div class="content">
            <p>A new user has applied to become a Squares Pool Commissioner.</p>

            <div class="info-row">
                <div class="label">Applicant Name:</div>
                <div class="value">{{ $application->full_name }}</div>
            </div>

            <div class="info-row">
                <div class="label">Email:</div>
                <div class="value">{{ $application->email }}</div>
            </div>

            <div class="info-row">
                <div class="label">User ID:</div>
                <div class="value">{{ $application->user_id }}</div>
            </div>

            <div class="info-row">
                <div class="label">Reason for Application:</div>
                <div class="value">{{ $application->reason }}</div>
            </div>

            @if($application->experience)
            <div class="info-row">
                <div class="label">Experience/Background:</div>
                <div class="value">{{ $application->experience }}</div>
            </div>
            @endif

            <div class="info-row">
                <div class="label">Submitted At:</div>
                <div class="value">{{ $application->created_at->format('F j, Y g:i A') }}</div>
            </div>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ env('FRONTEND_URL') }}/v2/squares-admin" class="button">Review Application in Dashboard</a>
            </p>
        </div>
        <div class="footer">
            <p>This is an automated notification from OKRNG Squares Pools</p>
        </div>
    </div>
</body>
</html>
