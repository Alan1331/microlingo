<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;600&display=swap">
    <title>Admin Login</title>
    <style>
        .font-poppins-semibold {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 20px;
        }
        .font-poppins-regular {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: small;
        }
        .font-poppins-small {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: smaller;
        }
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #134B70; /* Background color */
            font-family: Arial, sans-serif;
        }
        .header {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin-bottom: 20px;
        }
        h1 {
            color: black;
            margin-top: auto;
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
            
        }
        .login-button {
            background-color: #4285F4; /* Google Blue color */
            color: white;
            padding: 10px 15px; /* Vertikal dan horizontal padding */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            text-decoration: none; /* Menghilangkan garis bawah */
            gap: 10px; /* Jarak antara logo dan teks */
        }
        .login-button .logo-container {
            background-color: white;
            padding: 5px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            height: 20px;
        }
        .login-button img {
            width: 20px;
            height: 20px;
            margin-top: auto;
        }
        .login-button:hover {
            background-color: #357ae8;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="{{ asset('microLingo.png') }}" alt="Micro Lingo Logo">
        <h1 class="brand-text font-poppins-semibold">Login Admin</h1>
        <form action="" method="POST">
            @csrf
            <!-- Google Login Button -->
            <a href="{{ url('login/google') }}" class="login-button">
                <span class="logo-container">
                    <img src="{{ asset('googleColor.png') }}" alt="Google Logo">
                </span>
                Masuk dengan Google
            </a>
        </form>
    </div>
</body>
</html>
