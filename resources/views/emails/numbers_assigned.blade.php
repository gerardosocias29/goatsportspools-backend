<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Numbers Assigned: {{ $data['pool_name'] }}</title>
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
                            <img src="{{ $data['logo_url'] ?? 'https://test.goatsportspools.com/img/v2_logo.png' }}" alt="OKRNG" width="60" style="display: block; margin-bottom: 16px;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: 800; color: #101826; letter-spacing: -0.5px;">OKRNG</h1>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding: 32px 40px 16px 40px;">
                            <h2 style="margin: 0 0 8px 0; font-size: 26px; font-weight: 700; color: #101826;">Numbers Assigned!</h2>
                            @if(($data['squares_count'] ?? 0) > 0)
                            <p style="margin: 0; font-size: 16px; color: #666666;">Your squares now have their winning numbers</p>
                            @else
                            <p style="margin: 0; font-size: 16px; color: #666666;">The pool numbers have been assigned</p>
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
                                            Numbers have been assigned for pool <strong style="color: #FFD5B3;">"{{ $data['pool_name'] }}"</strong>!
                                        </p>
                                        <p style="margin: 0; font-size: 15px; color: #CCCCCC; line-height: 1.6;">
                                            Pool managed by <strong style="color: #FFD5B3;">{{ $data['admin_username'] }}</strong>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if(($data['squares_count'] ?? 0) > 0)
                    <!-- Your Numbers Card (Player has squares) -->
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #FAF6F2; border: 2px solid #E97A2E; border-radius: 12px;">
                                <tr>
                                    <td align="center" style="padding: 28px 24px;">
                                        <p style="margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; color: #E97A2E; font-weight: 700;">Your Winning Numbers</p>
                                        <p style="margin: 0 0 16px 0; font-size: 52px; font-weight: 800; color: #101826; line-height: 1;">{{ $data['squares_count'] }} {{ $data['squares_count'] == 1 ? 'Square' : 'Squares' }}</p>
                                        <p style="margin: 0 0 20px 0; font-size: 14px; color: #555555;">
                                            <strong style="color: #101826;">{{ $data['home_team'] }} vs {{ $data['visitor_team'] }}</strong>
                                        </p>

                                        <!-- Number Pairs -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                            @foreach(array_chunk($data['player_squares'], 5) as $row)
                                            <tr>
                                                @foreach($row as $square)
                                                <td style="padding: 4px;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                        <tr>
                                                            <td style="background-color: #E97A2E; color: #FFFFFF; padding: 10px 14px; border-radius: 8px; font-weight: 700; font-size: 14px; white-space: nowrap;">
                                                                ({{ $square['x_number'] }}, {{ $square['y_number'] }})
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </table>

                                        <p style="margin: 16px 0 0 0; font-size: 13px; color: #888888; font-style: italic;">
                                            Format: ({{ $data['home_team'] }} Score, {{ $data['visitor_team'] }} Score)
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @else
                    <!-- No Squares Card (Player hasn't claimed any) -->
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #FEF3C7; border: 2px solid #F59E0B; border-radius: 12px;">
                                <tr>
                                    <td align="center" style="padding: 28px 24px;">
                                        <p style="margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; color: #B45309; font-weight: 700;">No Squares Claimed</p>
                                        <p style="margin: 0 0 16px 0; font-size: 24px; font-weight: 800; color: #92400E; line-height: 1.3;">You haven't claimed any squares yet!</p>
                                        <p style="margin: 0 0 20px 0; font-size: 14px; color: #92400E;">
                                            <strong style="color: #101826;">{{ $data['home_team'] }} vs {{ $data['visitor_team'] }}</strong>
                                        </p>
                                        <p style="margin: 0; font-size: 15px; color: #78350F; line-height: 1.6;">
                                            Claim your squares now before the pool closes!
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif

                    @if(($data['squares_count'] ?? 0) > 0)
                    <!-- 11x11 Grid (only show if player has squares) -->
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F5F1ED; border-radius: 12px;">
                                <tr>
                                    <td align="center" style="padding: 24px;">
                                        <p style="margin: 0 0 16px 0; font-size: 14px; font-weight: 700; color: #101826;">Your Squares Grid</p>

                                        <!-- Grid Table -->
                                        <table role="presentation" cellspacing="1" cellpadding="0" border="0" style="background-color: #D3C9C2;">
                                            <!-- Header row with X numbers -->
                                            <tr>
                                                <td style="background-color: #101826; width: 28px; height: 28px;"></td>
                                                @foreach($data['x_numbers'] as $xNum)
                                                <td style="background-color: #101826; width: 28px; height: 28px; text-align: center; color: #FFFFFF; font-weight: 700; font-size: 12px;">{{ $xNum }}</td>
                                                @endforeach
                                            </tr>
                                            <!-- Data rows -->
                                            @for($y = 0; $y < 10; $y++)
                                            <tr>
                                                <!-- Y number header -->
                                                <td style="background-color: #101826; width: 28px; height: 28px; text-align: center; color: #FFFFFF; font-weight: 700; font-size: 12px;">{{ $data['y_numbers'][$y] }}</td>
                                                <!-- Grid cells -->
                                                @for($x = 0; $x < 10; $x++)
                                                @php
                                                    $isPlayerSquare = false;
                                                    foreach ($data['player_squares'] as $sq) {
                                                        if ($sq['x_coordinate'] == $x && $sq['y_coordinate'] == $y) {
                                                            $isPlayerSquare = true;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                @if($isPlayerSquare)
                                                <td style="background-color: #E97A2E; width: 28px; height: 28px; text-align: center; color: #FFFFFF; font-weight: 700; font-size: 14px;">&#9733;</td>
                                                @else
                                                <td style="background-color: #FFFFFF; width: 28px; height: 28px;"></td>
                                                @endif
                                                @endfor
                                            </tr>
                                            @endfor
                                        </table>

                                        <p style="margin: 12px 0 0 0; font-size: 12px; color: #888888;">
                                            <span style="color: #E97A2E;">&#9733;</span> = Your squares
                                        </p>
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
                                        <p style="margin: 0 0 4px 0; font-size: 32px; font-weight: 800; color: #E97A2E;">{{ $data['total_squares_filled'] }}</p>
                                        <p style="margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #666666; font-weight: 600;">Total Filled</p>
                                    </td>
                                    <td width="50%" align="center" style="padding: 24px 16px;">
                                        <p style="margin: 0 0 4px 0; font-size: 32px; font-weight: 800; color: #E97A2E;">{{ $data['squares_count'] }}</p>
                                        <p style="margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #666666; font-weight: 600;">Your Squares</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- How To Win Info -->
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F5F1ED; border-left: 4px solid #E97A2E; border-radius: 0 12px 12px 0;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 700; color: #101826;">How To Win</h3>
                                        <p style="margin: 0 0 16px 0; font-size: 14px; color: #444444; line-height: 1.7;">
                                            At the end of each quarter, check the <strong>last digit</strong> of each team's score. If your numbers match, you win!
                                        </p>
                                        <p style="margin: 0; font-size: 14px; color: #444444; line-height: 1.7;">
                                            <strong>Example:</strong> If you have <strong>({{ $data['example_x'] }}, {{ $data['example_y'] }})</strong> and the score is
                                            {{ $data['home_team'] }} {{ $data['example_x'] + 10 }} - {{ $data['visitor_team'] }} {{ $data['example_y'] + 20 }},
                                            you win because the last digits are {{ $data['example_x'] }} and {{ $data['example_y'] }}!
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif

                    <!-- CTA Button -->
                    <tr>
                        <td align="center" style="padding: 8px 40px 32px 40px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="background-color: #E97A2E; border-radius: 12px;">
                                        @if(($data['squares_count'] ?? 0) > 0)
                                        <a href="{{ $data['pool_url'] }}" target="_blank" style="display: inline-block; padding: 16px 48px; font-size: 16px; font-weight: 700; color: #FFFFFF; text-decoration: none;">View Your Pool</a>
                                        @else
                                        <a href="{{ $data['pool_url'] }}" target="_blank" style="display: inline-block; padding: 16px 48px; font-size: 16px; font-weight: 700; color: #FFFFFF; text-decoration: none;">Claim Squares Now!</a>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Closing -->
                    <tr>
                        <td align="center" style="padding: 0 40px 32px 40px;">
                            @if(($data['squares_count'] ?? 0) > 0)
                            <p style="margin: 0; font-size: 20px; font-weight: 700; color: #101826;">Good luck! May the odds be in your favor! 🍀</p>
                            @else
                            <p style="margin: 0; font-size: 20px; font-weight: 700; color: #101826;">Don't miss out! 🎯</p>
                            @endif
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 24px 40px; border-top: 1px solid #E5E0DB; background-color: #FAFAFA; border-radius: 0 0 16px 16px;">
                            <img src="{{ $data['logo_url'] ?? 'https://test.goatsportspools.com/img/v2_logo.png' }}" alt="OKRNG" width="32" style="display: block; margin: 0 auto 12px auto; opacity: 0.6;">
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
