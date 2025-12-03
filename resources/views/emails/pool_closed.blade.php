<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Pool Closed: {{ $data['pool_name'] }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #FAF6F2; -webkit-font-smoothing: antialiased;">

    <!-- Main Wrapper -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #FAF6F2;">
        <tr>
            <td align="center" style="padding: 40px 20px;">

                <!-- Email Container -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #FFFFFF; border-radius: 16px; border: 1px solid #D3C9C2; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 24px 40px; border-bottom: 1px solid #E5E0DB;">
                            <img src="{{ $data['logo_url'] ?? 'https://okrng.com/img/v2_logo.png' }}" alt="OKRNG" width="60" style="display: block; margin-bottom: 16px;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: 800; color: #101826; letter-spacing: -0.5px;">OKRNG</h1>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding: 32px 40px 16px 40px;">
                            <h2 style="margin: 0 0 8px 0; font-size: 26px; font-weight: 700; color: #101826;">Pool Closed!</h2>
                            @if(isset($data['numbers_assigned']) && $data['numbers_assigned'])
                            <p style="margin: 0; font-size: 16px; color: #666666;">Numbers have been assigned - Good luck!</p>
                            @else
                            <p style="margin: 0; font-size: 16px; color: #666666;">No more squares can be selected</p>
                            @endif
                        </td>
                    </tr>

                    <!-- Greeting -->
                    <tr>
                        <td style="padding: 16px 40px;">
                            <p style="margin: 0; font-size: 16px; color: #333333;">Hello <strong style="color: #101826;">{{ $data['player_name'] }}</strong>,</p>
                        </td>
                    </tr>

                    <!-- Pool Info Card (Dark) -->
                    <tr>
                        <td style="padding: 8px 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #101826; border-radius: 12px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <p style="margin: 0 0 12px 0; font-size: 15px; color: #FFFFFF; line-height: 1.6;">
                                            The square pool <strong style="color: #FFD5B3;">"{{ $data['pool_name'] }}"</strong> has been closed by pool manager <strong style="color: #FFD5B3;">"{{ $data['admin_username'] }}"</strong>.
                                        </p>
                                        @if(isset($data['numbers_assigned']) && $data['numbers_assigned'])
                                        <p style="margin: 0; font-size: 15px; color: #CCCCCC; line-height: 1.6;">
                                            Random numbers have been generated and no more squares can be picked.
                                        </p>
                                        @else
                                        <p style="margin: 0; font-size: 15px; color: #CCCCCC; line-height: 1.6;">
                                            No more squares can be picked. Numbers will be assigned before the game starts.
                                        </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Your Squares Card -->
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #FAF6F2; border: 2px solid #D47A3E; border-radius: 12px;">
                                <tr>
                                    <td align="center" style="padding: 28px 24px;">
                                        <p style="margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; color: #D47A3E; font-weight: 700;">Your Squares</p>
                                        <p style="margin: 0 0 16px 0; font-size: 52px; font-weight: 800; color: #101826; line-height: 1;">{{ $data['squares_count'] }}</p>
                                        <p style="margin: 0 0 20px 0; font-size: 14px; color: #555555;">
                                            Number pairs for<br>
                                            <strong style="color: #101826;">{{ $data['home_team'] }} vs {{ $data['visitor_team'] }}</strong>
                                        </p>

                                        <!-- Number Pairs -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                            <tr>
                                                @foreach($data['player_squares'] as $square)
                                                <td style="padding: 4px;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                        <tr>
                                                            <td style="background-color: #D47A3E; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 16px;">
                                                                ({{ $square['x_number'] }}, {{ $square['y_number'] }})
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                @endforeach
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Stats Row -->
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F5F1ED; border-radius: 12px;">
                                <tr>
                                    <td width="50%" align="center" style="padding: 24px 16px; border-right: 1px solid #E5E0DB;">
                                        <p style="margin: 0 0 4px 0; font-size: 32px; font-weight: 800; color: #D47A3E;">{{ $data['total_squares_filled'] }}</p>
                                        <p style="margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #666666; font-weight: 600;">Total Filled</p>
                                    </td>
                                    <td width="50%" align="center" style="padding: 24px 16px;">
                                        <p style="margin: 0 0 4px 0; font-size: 32px; font-weight: 800; color: #D47A3E;">{{ $data['squares_count'] }}</p>
                                        <p style="margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #666666; font-weight: 600;">Your Squares</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Rules Info -->
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F5F1ED; border-left: 4px solid #D47A3E; border-radius: 0 12px 12px 0;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 700; color: #101826;">How to Win</h3>
                                        <p style="margin: 0 0 16px 0; font-size: 14px; color: #444444; line-height: 1.7;">
                                            Square pools for football normally have winners after each of the first 3 quarters and on the final score. If your numbers match the last digit in the game score at those times, you win!
                                        </p>

                                        <!-- Example Box -->
                                        @if(isset($data['numbers_assigned']) && $data['numbers_assigned'])
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #FFFFFF; border: 1px solid #E5E0DB; border-radius: 8px;">
                                            <tr>
                                                <td style="padding: 14px 16px;">
                                                    <p style="margin: 0; font-size: 14px; color: #333333; line-height: 1.6;">
                                                        <strong style="color: #D47A3E;">Example:</strong> With your numbers <strong style="color: #101826;">({{ $data['example_x'] }}, {{ $data['example_y'] }})</strong>, you would win if the score was <strong style="color: #101826;">{{ $data['example_x'] + 10 }}-{{ $data['example_y'] + 20 }}</strong>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                        @else
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #FFFFFF; border: 1px solid #E5E0DB; border-radius: 8px;">
                                            <tr>
                                                <td style="padding: 14px 16px;">
                                                    <p style="margin: 0; font-size: 14px; color: #333333; line-height: 1.6;">
                                                        <strong style="color: #D47A3E;">Example:</strong> If your assigned numbers are <strong style="color: #101826;">(3, 7)</strong>, you would win if the score was <strong style="color: #101826;">13-27</strong>
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                        @endif

                                        <p style="margin: 16px 0 0 0; font-size: 13px; color: #777777; font-style: italic;">
                                            Check with your pool manager about specific rules for your pool.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- CTA Button -->
                    <tr>
                        <td align="center" style="padding: 8px 40px 32px 40px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="background-color: #D47A3E; border-radius: 12px;">
                                        <a href="{{ $data['pool_url'] }}" target="_blank" style="display: inline-block; padding: 16px 48px; font-size: 16px; font-weight: 700; color: #FFFFFF; text-decoration: none;">View The Square</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Closing -->
                    <tr>
                        <td align="center" style="padding: 0 40px 32px 40px;">
                            <p style="margin: 0; font-size: 20px; font-weight: 700; color: #101826;">Good luck! 🍀</p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 24px 40px; border-top: 1px solid #E5E0DB; background-color: #FAFAFA; border-radius: 0 0 16px 16px;">
                            <img src="{{ $data['logo_url'] ?? 'https://okrng.com/img/v2_logo.png' }}" alt="OKRNG" width="32" style="display: block; margin: 0 auto 12px auto; opacity: 0.6;">
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: #888888;">This email was sent from OKRNG Squares Pool</p>
                            <p style="margin: 0; font-size: 13px; color: #888888;">&copy; {{ date('Y') }} OKRNG. All rights reserved.</p>
                        </td>
                    </tr>

                </table>
                <!-- End Email Container -->

            </td>
        </tr>
    </table>
    <!-- End Main Wrapper -->

</body>
</html>
