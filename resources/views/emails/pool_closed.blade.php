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
                            <img src="{{ $data['logo_url'] ?? 'https://test.goatsportspools.com/img/v2_logo.png' }}" alt="OKRNG" width="60" style="display: block; margin-bottom: 16px;">
                            <h1 style="margin: 0; font-size: 28px; font-weight: 800; color: #101826; letter-spacing: -0.5px;">OKRNG</h1>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td align="center" style="padding: 32px 40px 16px 40px;">
                            <h2 style="margin: 0 0 8px 0; font-size: 26px; font-weight: 700; color: #101826;">Pool Closed!</h2>
                            <p style="margin: 0; font-size: 16px; color: #666666;">No more squares can be selected</p>
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
                                        <p style="margin: 0; font-size: 15px; color: #CCCCCC; line-height: 1.6;">
                                            No more squares can be picked.
                                            @if(!($data['numbers_assigned'] ?? false))
                                            Numbers will be assigned before the game starts.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Your Squares Card -->
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #FAF6F2; border: 2px solid #6B7280; border-radius: 12px;">
                                <tr>
                                    <td align="center" style="padding: 28px 24px;">
                                        <p style="margin: 0 0 8px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; color: #6B7280; font-weight: 700;">Your Claimed Squares</p>
                                        <p style="margin: 0 0 16px 0; font-size: 52px; font-weight: 800; color: #101826; line-height: 1;">{{ $data['squares_count'] }}</p>
                                        <p style="margin: 0 0 20px 0; font-size: 14px; color: #555555;">
                                            Grid positions for<br>
                                            <strong style="color: #101826;">{{ $data['visitor_team'] }} vs {{ $data['home_team'] }}</strong>
                                        </p>

                                        <!-- Grid Positions -->
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center">
                                            @foreach(array_chunk($data['player_squares'], 5) as $row)
                                            <tr>
                                                @foreach($row as $square)
                                                <td style="padding: 4px;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                        <tr>
                                                            @if($data['numbers_assigned'] ?? false)
                                                            {{-- Numbers are assigned - show actual numbers --}}
                                                            <td style="background-color: #E97A2E; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 14px; white-space: nowrap;">
                                                                ({{ $square['x_number'] ?? '?' }}, {{ $square['y_number'] ?? '?' }})
                                                            </td>
                                                            @else
                                                            {{-- Numbers not assigned - show question marks --}}
                                                            <td style="background-color: #6B7280; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-weight: 700; font-size: 14px; white-space: nowrap;">
                                                                [?, ?]
                                                            </td>
                                                            @endif
                                                        </tr>
                                                    </table>
                                                </td>
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </table>

                                        <p style="margin: 16px 0 0 0; font-size: 13px; color: #888888; font-style: italic;">
                                            @if($data['numbers_assigned'] ?? false)
                                            These are your assigned numbers. Good luck!
                                            @else
                                            Numbers will be assigned before the game starts.
                                            @endif
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
                                        <p style="margin: 0 0 4px 0; font-size: 32px; font-weight: 800; color: #6B7280;">{{ $data['total_squares_filled'] }}</p>
                                        <p style="margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #666666; font-weight: 600;">Total Filled</p>
                                    </td>
                                    <td width="50%" align="center" style="padding: 24px 16px;">
                                        <p style="margin: 0 0 4px 0; font-size: 32px; font-weight: 800; color: #6B7280;">{{ $data['squares_count'] }}</p>
                                        <p style="margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #666666; font-weight: 600;">Your Squares</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Your Squares Grid -->
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
                                                @for($i = 0; $i < 10; $i++)
                                                @if($data['numbers_assigned'] ?? false)
                                                <td style="background-color: #101826; width: 28px; height: 28px; text-align: center; color: #FFFFFF; font-weight: 700; font-size: 12px;">{{ $data['x_numbers'][$i] ?? '?' }}</td>
                                                @else
                                                <td style="background-color: #6B7280; width: 28px; height: 28px; text-align: center; color: #FFFFFF; font-weight: 700; font-size: 12px;">?</td>
                                                @endif
                                                @endfor
                                            </tr>
                                            <!-- Data rows -->
                                            @for($y = 0; $y < 10; $y++)
                                            <tr>
                                                <!-- Y number header -->
                                                @if($data['numbers_assigned'] ?? false)
                                                <td style="background-color: #101826; width: 28px; height: 28px; text-align: center; color: #FFFFFF; font-weight: 700; font-size: 12px;">{{ $data['y_numbers'][$y] ?? '?' }}</td>
                                                @else
                                                <td style="background-color: #6B7280; width: 28px; height: 28px; text-align: center; color: #FFFFFF; font-weight: 700; font-size: 12px;">?</td>
                                                @endif
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
                                                <td style="background-color: #6B7280; width: 28px; height: 28px; text-align: center; color: #FFFFFF; font-weight: 700; font-size: 14px;">&#9733;</td>
                                                @else
                                                <td style="background-color: #FFFFFF; width: 28px; height: 28px;"></td>
                                                @endif
                                                @endfor
                                            </tr>
                                            @endfor
                                        </table>

                                        <p style="margin: 12px 0 0 0; font-size: 12px; color: #888888;">
                                            <span style="color: #6B7280;">&#9733;</span> = Your squares
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- What's Next Info -->
                    <tr>
                        <td style="padding: 0 40px 24px 40px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #F5F1ED; border-left: 4px solid #6B7280; border-radius: 0 12px 12px 0;">
                                <tr>
                                    <td style="padding: 20px 24px;">
                                        @if($data['numbers_assigned'] ?? false)
                                        <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 700; color: #101826;">How To Win</h3>
                                        <p style="margin: 0; font-size: 14px; color: #444444; line-height: 1.7;">
                                            <strong>How to win:</strong> If your numbers match the last digit of the game score at the end of each quarter, you win!
                                        </p>
                                        @else
                                        <h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 700; color: #101826;">What's Next?</h3>
                                        <p style="margin: 0 0 16px 0; font-size: 14px; color: #444444; line-height: 1.7;">
                                            The pool manager will assign random numbers (0-9) to each row and column before the game starts. You'll receive another email with your assigned numbers once they're ready.
                                        </p>
                                        <p style="margin: 0; font-size: 14px; color: #444444; line-height: 1.7;">
                                            <strong>How to win:</strong> If your numbers match the last digit of the game score at the end of each quarter, you win!
                                        </p>
                                        @endif
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
                                    <td style="background-color: #6B7280; border-radius: 12px;">
                                        <a href="{{ $data['pool_url'] }}" target="_blank" style="display: inline-block; padding: 16px 48px; font-size: 16px; font-weight: 700; color: #FFFFFF; text-decoration: none;">View Your Pool</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Closing -->
                    <tr>
                        <td align="center" style="padding: 0 40px 32px 40px;">
                            @if($data['numbers_assigned'] ?? false)
                            <p style="margin: 0; font-size: 20px; font-weight: 700; color: #101826;">Good luck! 🍀</p>
                            @else
                            <p style="margin: 0; font-size: 20px; font-weight: 700; color: #101826;">Stay tuned for your numbers! 🎲</p>
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
