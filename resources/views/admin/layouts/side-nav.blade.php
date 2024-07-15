<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="{{ asset('microLingo.png') }}" alt="MicroLingo Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Micro Lingo</span>
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
          <a href="#" class="d-block">Admin 1</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Manajemen Data -->
      <nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Manajemen Data -->
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link active">
            <img src="managedata.png" class="nav-icon" alt="Manajemen Data Icon" style="width: 25px; height: 25px;">
                <p>
                    Manajemen Data
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="./index.html" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Kelola Data</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="./index2.html" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Hapus Data</p>
                    </a>
                </li>
            </ul>
        </li>
        <!-- Materi Pembelajaran dan Perkembangan -->
        <li class="nav-item has-treeview">
            <a href="#" class="nav-link active">
            <img src="materi.png" class="nav-icon" alt="Materi Icon" style="width: 25px; height: 25px;">
                <p>
                    Materi Pembelajaran & Perkembangan
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="./index.html" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Modifikasi Materi</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="./index2.html" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Perkembangan Pengguna</p>
                    </a>
                </li>
            </ul>
        </li>
          <li class="nav-header">Catatan Admin</li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-circle text-danger"></i>
              <p class="text">Catatan</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>