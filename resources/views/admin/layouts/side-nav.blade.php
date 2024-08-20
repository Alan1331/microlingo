<!DOCTYPE html>
 <head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;400;600&display=swap">
    <style>
        .font-poppins-semibold {
            font-family: 'Poppins', sans-serif;
            font-weight: 800;
            font-size: large;
        }
        .font-poppins-regular {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: small;
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .font-poppins-small {
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: smaller;
        }
        .circle {
            width: 16px;
            height: 16px; 
            background-color: white; 
            border: 2px solid black; 
            border-radius: 50%; 
            display: inline-block; 
            color: black; 
            cursor: pointer; 
            transition: background-color 0.3s, color 0.3s; 
            line-height: 16px;
            margin-left: 20px;
            margin-right: 10px;
        }
        .active-circle {
            background-color: #7288C7;
            border-radius: 50%;
        }
        .open-menu {
            background-color: #7288C7;
            border-color: black;
        }

         .nav-header {
        display: flex;
        align-items: center;
        font-size: 16px;
        margin-bottom: 10px;
        }

        .notes-logo {
        width: 24px; 
        height: auto;
        margin-right: 8px; 
        }

        .nav-item {
        margin-bottom: 5px; 
        }

        .nav-link:hover {
            display: flex;
            align-items: center;
            background-color: #7288C7; 
            text-decoration: none; 
        }

        .nav-icon {
            margin-right: 8px; 
        }

        .text {
            font-size: 14px; 
        }
    </style>
</head> 
<aside class="main-sidebar sidebar-light elevation-4">
    <!-- Brand Logo -->
    <a href="/admin-page" class="brand-link">
        <img src="{{ asset('microLingo.png') }}" alt="MicroLingo Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-poppins-semibold" style="color: #7288C7;">Micro Lingo</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ $admin->photoUrl }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <!-- ganti user sesuai siapa yang login -->
                <a class="brand-text font-poppins-regular" style="color: black;">{{explode('(', $admin->displayName)[0]}}</a>
            </div>
        </div>

        <!-- Sidebar Manajemen Pengguna -->
        <nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column">
        <!-- Kelola Pengguna -->
        <li>
            <a href="/kelolaPengguna" class="nav-link">
                <img src="{{ asset('managedata.png') }}" class="nav-icon" alt="kelola Icon" style="width: 25px; height: 25px;">
                <p class="brand-text font-poppins-regular" style="color: black;">
                    Data Pengguna
                </p>
            </a>
        </li>
        <!-- Materi Pembelajaran dan Perkembangan -->
        <li style="margin-top: 20px;">
            <a href="/materiPembelajaran" class="nav-link">
                <img src="{{ asset('materi.png') }}" class="nav-icon" alt="Materi Icon" style="width: 25px; height: 25px;">
                <p class="brand-text font-poppins-regular" style="color: black;">
                   Materi Pembelajaran
                </p>
            </a>
        </li>
    </ul>
</nav>
    </div>

</aside>
