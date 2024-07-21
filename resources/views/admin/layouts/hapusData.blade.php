@extends('admin.layouts.app')

@section('content')
<body>
<style>
        .delete-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            color: white;
            background-color: #dc3545; /* Warna merah untuk tombol delete */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            text-align: center;
        }

        .delete-button:hover {
            background-color: #c82333; /* Warna merah gelap saat hover */
            color: #ffffff;
        }

        .delete-button:active {
            background-color: #bd2130; /* Warna merah lebih gelap saat ditekan */
        }
    </style>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card" style="margin-top: 25px;">
                    <div class="card-header">
                        <h3 class="card-title">Kelola Data</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Nama</th>
                                    <th>Nomor WhatsApp</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody> 
                                <!-- Your table rows here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>...</td>
                                    <td>...</td>
                                    <td>...</td>
                                    <td><button class="delete-button" onclick="confirmDelete()">Delete</button</td> 
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->

                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
</body>
<!-- /.content -->
@endsection
