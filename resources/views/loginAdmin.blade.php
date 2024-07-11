<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #9BB0C1; /* Background color */
            font-family: Arial, sans-serif;
        }
        .login-container {
            text-align: center;
            background-color: #F0F0F0;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .login-container img {
            width: 180px;
            margin-bottom: 20px;
        }
        .login-container h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        .login-container input {
            display: block;
            width: 92%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .login-container button {
            background-color: #7C8CD1; /* Button color */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .login-container button:hover {
            background-color: #5C6BB5;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="{{ asset('microLingo.png') }}" alt="Micro Lingo Logo">
        <h1>Login Admin</h1>
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>