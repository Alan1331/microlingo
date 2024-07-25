<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;600&display=swap">
    <title>Unauthorized Access</title>
    <style>
        .font-poppins-semibold {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 30px;
            color: #134B70;
        }

        .font-poppins-small {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 20px;
            color: #134B70;
        }
        
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f0f0f0; /* Background color (optional) */
        }

        .container {
            text-align: center; /* Center text if needed */
        }

        .container img {
            max-width: 60%;
            height: auto; 
        }

        .return-button {
            background-color: #4535C1; 
            color: white;
            padding: 10px 20px; /* Padding atas/bawah 10px, kiri/kanan 20px */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: inline-flex; /* Mengubah dari flex ke inline-flex agar button menyesuaikan ukuran konten */
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            text-decoration: none;
            white-space: nowrap; /* Menghindari pemotongan teks */
        }

    </style>
</head>
<body>
    <div class="container">
        <img src="error.png" alt="Error">
        <h1 class="brand-text font-poppins-semibold">You are not allowed to acces this site.</h1>
        <h2 class="brand-text font-poppins-small">This page is not publically available</h2>
        <form action="" method="POST">
            @csrf
            <a href="{{ url('/loginAdmin') }}" class="return-button">
                RETURN HOME
            </a>
        </form>
    </div>
</body>
</html>