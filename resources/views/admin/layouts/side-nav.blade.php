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

        .nav-link {
            display: flex;
            align-items: center;
            color: #333; 
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
    <a href="index3.html" class="brand-link">
        <img src="{{ asset('microLingo.png') }}" alt="MicroLingo Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-poppins-semibold" style="color: #7288C7;">Micro Lingo</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('user.png') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <!-- ganti user sesuai siapa yang login -->
                <a href="#" class="brand-text font-poppins-regular" style="color: black;">Admin 1</a>
            </div>
        </div>

        <!-- Sidebar Manajemen Data -->
        <nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Manajemen Data -->
        <li class="nav-item menu-open">
            <a href="#" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'kelolaData') !== false|| strpos($_SERVER['REQUEST_URI'], 'hapusData') !== false) ? 'open-menu' : ''; ?>">
                <img src="managedata.png" class="nav-icon">
                <p class="brand-text font-poppins-regular" style="color: black;">
                    Manajemen Pengguna
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview" style="display: block;">
                <li class="nav-item">
                    <a href="/kelolaPengguna" class="nav-link ">
                    <i class="circle <?php echo (strpos($_SERVER['REQUEST_URI'], 'kelolaPengguna') !== false) ? 'active-circle' : ''; ?>"></i>
                    <p class="brand-text font-poppins-small" style="color: black;">Kelola Pengguna</p>
                    </a>
                </li>
            </ul>
        </li>
                <!-- Materi Pembelajaran dan Perkembangan -->
                <li class="nav-item has-treeview <?php echo (strpos($_SERVER['REQUEST_URI'], 'modifikasiMateri') !== false || strpos($_SERVER['REQUEST_URI'], 'perkembanganPengguna') !== false) ? 'menu-open' : ''; ?>">
            <a href="#" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], 'modifikasiMateri') !== false || strpos($_SERVER['REQUEST_URI'], 'perkembanganPengguna') !== false) ? 'open-menu' : ''; ?>">
                <img src="materi.png" class="nav-icon" alt="Materi Icon" style="width: 25px; height: 25px;">
                <p class="brand-text font-poppins-regular" style="color: black;">
                    Materi & Perkembangan
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview" style="display: block;">
                <li class="nav-item">
                    <a href="/modifikasiMateri" class="nav-link ">
                        <i class="circle <?php echo (strpos($_SERVER['REQUEST_URI'], 'modifikasiMateri') !== false) ? 'active-circle' : ''; ?>"></i>
                        <p class="brand-text font-poppins-small" style="color: black;">Modifikasi Materi</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/perkembanganPengguna" class="nav-link">
                        <i class="circle <?php echo (strpos($_SERVER['REQUEST_URI'], 'perkembanganPengguna') !== false) ? 'active-circle' : ''; ?>"></i>
                        <p class="brand-text font-poppins-small" style="color: black;">Perkembangan Pengguna</p>
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <a href="/catatanAdmin" class="nav-link">
                <img src="notes.png" class="nav-icon" alt="Notes Icon" style="width: 25px; height: 25px;">
                <p class="brand-text font-poppins-regular" style="color: black;">
                    Catatan Admin
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
        </li>
    </ul>
</nav>
    </div>
</aside>
