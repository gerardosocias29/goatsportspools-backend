<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us Message</title>
</head>
<body>
    <h1>New Contact Us Message</h1>
    <p><strong>Form Name:</strong> {{ $data['name'] }}</p>
    <p><strong>Form Email:</strong> {{ $data['email'] }}</p>

    <p><strong>User Name:</strong> {{ $data['username'] }}</p>
    <p><strong>User email:</strong> {{ $data['useremail'] }}</p>

    <p><strong>Message:</strong></p>
    <p>{{ $data['message'] }}</p>
</body>
</html>