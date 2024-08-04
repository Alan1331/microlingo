@extends('admin.layouts.app')

@section('content')

<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="brand-text font-poppins-regular">Hi, {{ explode('(', $admin->displayName)[0] }}!</h1>
        <p>Selamat datang kembali di dashboard admin. Berikut adalah ringkasan dari aktivitas terbaru.</p>
      </div><!-- /.col -->
      
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
  <!-- Info boxes -->
  <div class="row">
    <div class="col-12 col-sm-6 col-md-6">
      <div class="info-box">
        <span class="info-box-icon bg-info "><i class="fas fa-comments"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Interaksi</span>
          <span class="info-box-number">
            4,210
          </span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-6">
      <div class="info-box mb-3">
        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-envelope"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Pelajaran yang diselesaikan</span>
          <span class="info-box-number">12,300</span>
        </div>
      </div>
    </div>
  </div>
  <!-- /.row -->

  

  <!-- Recent Activity Table -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Aktivitas Pengguna</h3>
    </div>
    <div class="card-body p-0">
      <table class="table table-striped">
        <thead>
          <tr>

            <th>Nomor WhatsApp</th>
            <th>Nama</th>
            <th>Level</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>2123456</td>
            <td>John Doe</td>
            <td>1</td>
            <td><span class="badge badge-success">Berhasil</span></td>
          </tr>
          <tr>
            <td>1234654</td>
            <td>Jane Smith</td>
            <td>2</td>
            <td><span class="badge badge-warning">Menunggu persetujuan</span></td>
          </tr>
          <tr>
            <td>87653456</td>
            <td>Michael Johnson</td>
            <td>3</td>
            <td><span class="badge badge-info">Diproses</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <!-- /.card -->
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('user-growth-chart-canvas').getContext('2d');
    var userGrowthChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
        datasets: [{
          label: 'Jumlah Pengguna',
          data: [30, 50, 100, 150, 200, 300, 400],
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 2,
          fill: false
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  });
</script>
@endpush
