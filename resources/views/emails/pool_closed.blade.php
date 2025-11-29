<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pool Closed: {{ $data['pool_name'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .pool-info {
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
        }
        .squares-info {
            background-color: #e8f5e9;
            border-left: 4px solid #27ae60;
            padding: 15px;
            margin: 20px 0;
        }
        .number-pair {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 5px 12px;
            border-radius: 4px;
            margin: 3px;
            font-weight: bold;
        }
        .rules-info {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #777;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ $data['logo_url'] ?? env('APP_URL') . '/img/v2_logo.png' }}" alt="OKRNG" class="logo">
        </div>

        <h1>Pool Closed Notification</h1>

        <p>Hello {{ $data['player_name'] }},</p>

        <div class="pool-info">
            <p>The square pool named <strong>"{{ $data['pool_name'] }}"</strong> has been closed by the pool manager whose username is <strong>"{{ $data['admin_username'] }}"</strong>.</p>
            <p>When the pool was closed, the website computer generated random numbers and no more squares can be picked.</p>
        </div>

        <div class="squares-info">
            <p><strong>You picked {{ $data['squares_count'] }} square(s)</strong> and the random number pairs drawn for you ({{ $data['home_team'] }}, {{ $data['visitor_team'] }}) are:</p>
            <p>
                @foreach($data['player_squares'] as $square)
                    <span class="number-pair">({{ $square['x_number'] }}, {{ $square['y_number'] }})</span>
                @endforeach
            </p>
        </div>

        <p>Poolwide, <strong>{{ $data['total_squares_filled'] }} squares</strong> were filled.</p>

        <div class="rules-info">
            <p><strong>How to Win:</strong></p>
            <p>Every pool has its own rules, so please check with your pool manager about the specifics for your pool.</p>
            <p>Square pools for football normally have winners after each of the first 3 quarters and on the final score. If your numbers match the last digit in the game score at those times, you win.</p>
            <p>For example, with your numbers <strong>({{ $data['example_x'] }}, {{ $data['example_y'] }})</strong>, you would win if the final score was <strong>{{ $data['example_x'] + 10 }}-{{ $data['example_y'] + 20 }}</strong> ({{ $data['home_team'] }}, {{ $data['visitor_team'] }}).</p>
            <p><em>Again, check with your pool manager about the specific rules for your pool.</em></p>
        </div>

        <p style="text-align: center;">
            <a href="{{ $data['pool_url'] }}" class="btn">View The Square</a>
        </p>

        <p>Good luck!</p>

        <div class="footer">
            <p>This email was sent from OKRNG Squares Pool</p>
            <p>&copy; {{ date('Y') }} OKRNG. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
