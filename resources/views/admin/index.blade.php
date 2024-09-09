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
        <div class="info-box-content">
          <span class="badge badge-info" style="height: 100px; width: 105px; color: black; font-size: 18px;" >
              <img src="{{ asset('users.png') }}" class="users">
              Total Pengguna
          </span>
          <span class="info-box-number">
            4,210
          </span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-6">
      <div class="info-box mb-3">
        <div class="info-box-content">
          <span class="badge badge-success" style="height: 100px; width: 105px; color: black;  font-size: 18px;">
          <img src="{{ asset('book.png') }}" >
            Pelajaran yang diselesaikan
          </span>
          <span class="info-box-number">12,300</span>
        </div>
      </div>
    </div>
  </div>
  <!-- /.row -->

  

  <!-- Recent Activity Table -->
  
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

<style>
  .users {
        display: inline-block;
        margin-bottom: 1%;
        font-size: 14px;
        font-weight: bold;
        color: black;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;
        margin-left: 0px;
    }
    
    .users img {
        width: 5px;
        height: 5px;
    }

    
</style>
@endpush
