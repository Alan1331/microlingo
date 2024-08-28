@extends('admin.layouts.app')

@section('content')

<body>
    <section class="content">
        <div class="container-fluid">
            <!-- Alert Message for Failed -->
            @if(session('failed'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('failed') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="row">
                <div class="col-12">
                    <div class="card" style="margin-top: 25px;">
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h1 class="brand-text font-poppins-semibold" style="margin: 0;">Kelola Data</h1>
                                <a class="add-button" id="topikBtn"
                                    style="display: flex; align-items: center; margin-bottom: 10px;">
                                    <img src="{{ asset('add.png') }}" alt="add Button" style="margin-right: 8px;">
                                    Tambah Unit
                                </a>
                                <div id="editModal" class="modalAction">
                                    <div class="modal-content4" data-dismiss="modalAction" aria-label="Close">
                                        <h2 class="modal-title">Tambah Unit Pembelajaran</h2>
                                        <form id="addUnitForm" method="POST" action="{{ route('units.create') }}">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label for="topik" class="font-weight-bold" style="text-align: right;">Topik</label>
                                                <textarea class="form-control @error('topik') is-invalid @enderror"
                                                    name="topic" id="topik" rows="5"></textarea>
                                                @error('topik')
                                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-10 offset-sm-2" style="display: flex; justify-content: center; gap: 10px;">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                    <button type="button" id="cancelBtn" class="btn btn-secondary">Batalkan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @if($learningUnits->count() != 0)
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;">Unit</th>
                                        <th style="width: 500px;">Topik</th>
                                        <th style="width: 300px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dataTableBody">
                                    <!-- Table rows will be added here dynamically -->
                                </tbody>
                                <tfoot>
                                    @foreach ($learningUnits as $unit)
                                    <tr>
                                        <td>{{ $unit['sortId'] }}</td>
                                        <td>{{ $unit['topic'] }}</td>
                                        <td colspan="6" style="text-align: center;">
                                            <a href="/materiPembelajaran/{{ $unit['id'] }}" class="view-button">
                                                <img src="{{ asset('view.png') }}" alt="View Button">
                                                Lihat Level
                                            </a>
                                            <form id="unit-delete-form-{{ $unit['id'] }}" action="{{ route('units.delete', $unit['id']) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <a onclick="event.preventDefault(); if(confirm('Apakah admin yakin akan menghapus unit ini?\nKarena unit sebelumnya akan dipindahkan ke atas')) document.getElementById('unit-delete-form-{{ $unit['id'] }}').submit();">
                                                @method('DELETE')
                                                <button type="button" class="delete-button">
                                                    <img src="{{ asset('delete.png') }}" alt="Delete Button">
                                                    Hapus
                                                </button>
                                            </a>
                                            <a class="edit-button" id="updateBtn">
                                                        <img src="{{ asset('edit.png') }}" alt="Edit Button">
                                                        Update
                                                    </a>
                                                    <div id="updateModal" class="modalAction2">
                                    <div class="modal-content4" data-dismiss="modalAction2" aria-label="Close">
                                        <h2 class="modal-title">Update Topik</h2>
                                        <form id="addUnitForm2" method="POST" >
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label for="topik" class="font-weight-bold" style="text-align: right;">Update Topik</label>
                                                <textarea class="form-control @error('topik') is-invalid @enderror"
                                                    name="topic" id="topik" rows="5"></textarea>
                                                @error('topik')
                                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-10 offset-sm-2" style="display: flex; justify-content: center; gap: 10px;">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                    <button type="button" id="cancelBtn2" class="btn btn-secondary">Batalkan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tfoot>
                            </table>
                            @else
                            <b>Tidak ada unit yang tersedia</b>
                            @endif
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
<style>
    .delete-button {
        display: inline-block;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: bold;
        color: white;
        background-color: #dc3545;
        /* Warna merah untuk tombol delete */
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;
        text-align: center;
    }

    .delete-button img {
        width: 20px;
        /* Sesuaikan ukuran gambar */
        height: 20px;
        /* Sesuaikan ukuran gambar */
        margin-right: 5px;
        /* Jarak antara gambar dan teks */
    }

    .delete-button:hover {
        background-color: #A91D3A;
        /* Warna merah gelap saat hover */
        color: #ffffff;
    }

    .delete-button:active {
        background-color: #bd2130;
        /* Warna merah lebih gelap saat ditekan */
    }

    .view-button {
        display: inline-block;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: bold;
        color: white;
        background-color: blue;
        /* Warna merah untuk tombol delete */
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;
        text-align: center;
    }

    .view-button img {
        width: 20px;
        /* Sesuaikan ukuran gambar */
        height: 20px;
        /* Sesuaikan ukuran gambar */
        margin-right: 5px;
        /* Jarak antara gambar dan teks */
    }

    .view-button:hover {
        background-color: #03346E;
        /* Warna merah gelap saat hover */
        color: #ffffff;
    }

    .edit-button {
        display: inline-block;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: bold;
        color: white;
        background-color: blue;
        /* Warna merah untuk tombol delete */
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;
        text-align: center;
    }

    .edit-button img {
        width: 20px;
        /* Sesuaikan ukuran gambar */
        height: 20px;
        /* Sesuaikan ukuran gambar */
        margin-right: 5px;
        /* Jarak antara gambar dan teks */
    }

    .edit-button:hover {
        background-color: #03346E;
        /* Warna merah gelap saat hover */
        color: #ffffff;
    }

    .add-button {
        display: inline-block;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: bold;
        color: white;
        background-color: blue;
        /* Warna merah untuk tombol delete */
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;
        text-align: center;
    }

    .add-button img {
        width: 20px;
        /* Sesuaikan ukuran gambar */
        height: 20px;
        /* Sesuaikan ukuran gambar */
        margin-right: 5px;
        /* Jarak antara gambar dan teks */
    }

    .add-button:hover {
        background-color: #03346E;
        /* Warna merah gelap saat hover */
        color: #ffffff;
    }

    .editModal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .updateModal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modalAction {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modalAction2 {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content4 {
        position: relative;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 50%;
        margin: 15% auto;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    var topikBtn = document.getElementById('topikBtn');
    var updateBtn = document.getElementById('updateBtn');
    var editModal = document.getElementById('editModal');
    var updateModal = document.getElementById('updateModal');
    var dataTableBody = document.getElementById('dataTableBody');
    var cancelBtn = document.getElementById('cancelBtn');
    var cancelBtn2 = document.getElementById('cancelBtn2');
    var editUnit = document.getElementById('editUnit');
    var editTopik = document.getElementById('editTopik');

    // Fungsi untuk menampilkan modal
    topikBtn.addEventListener('click', function () {
        editModal.style.display = 'block';
    });

    updateBtn.addEventListener('click', function () {
        updateModal.style.display = 'block';
    });

    // Menutup modal ketika klik di luar konten modal
    window.addEventListener('click', function (event) {
        if (event.target == editModal) {
            editModal.style.display = 'none';
        }
        if (event.target == updateModal) {
            updateModal.style.display = 'none';
        }
    });

    // Menutup modal ketika klik batalkan
    cancelBtn.addEventListener('click', function () {
        editModal.style.display = 'none';
    });

    cancelBtn2.addEventListener('click', function () {
        updateModal.style.display = 'none';
    });
});
</script>
@endsection