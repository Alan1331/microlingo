@extends('admin.layouts.app')

@section('content')

<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Hi Admin!</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-3 col-6">
        <div class="small-box" style="background-color: #dfdf;">
          <div class="inner">
            <h3>150</h3>
            <p>New Orders</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-6">

        <div class="small-box bg-success">
          <div class="inner">
            <h3>53<sup style="font-size: 20px">%</sup></h3>
            <p>Bounce Rate</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-6">

        <div class="small-box bg-success">
          <div class="inner">
            <h3>53<sup style="font-size: 20px">%</sup></h3>
            <p>Bounce Rate</p>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-6">

        <div class="small-box bg-success">
          <div class="inner">
            <h3>53<sup style="font-size: 20px">%</sup></h3>
            <p>Bounce Rate</p>
          </div>
        </div>
      </div>

      </div>
    <div class="card">
      <div class="card-header ui-sortable-handle" style="cursor: move;">
        <h3 class="card-title">
          <i class="fas fa-chart-pie mr-1"></i>
          Sales
        </h3>
        <div class="card-tools">
          <ul class="nav nav-pills ml-auto">
            <li class="nav-item">
              <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Area</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#sales-chart" data-toggle="tab">Donut</a>
            </li>
          </ul>
        </div>
      </div>
      <div class="card-body">
        <div class="tab-content p-0">

          <div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 300px;">
            <div class="chartjs-size-monitor">
              <div class="chartjs-size-monitor-expand">
                <div class=""></div>
              </div>
              <div class="chartjs-size-monitor-shrink">
                <div class=""></div>
              </div>
            </div>
            <canvas id="revenue-chart-canvas" height="375" style="height: 300px; display: block; width: 894px;"
              width="1117" class="chartjs-render-monitor"></canvas>
          </div>
          <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
            <canvas id="sales-chart-canvas" height="0" style="height: 0px; display: block; width: 0px;" width="0"
              class="chartjs-render-monitor"></canvas>
          </div>
        </div>
      </div>
    </div>
</section>
@endsection