@extends('admin.layouts.app')

@section('content')

<body>
    <section class="content">
        <div class="container-fluid">
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
                                    Topik Baru
                                </a>
                                <div id="editModal" class="modalAction">
                                    <div class="modal-content4" data-dismiss="modalAction" aria-label="Close">
                                        <h2 class="modal-title">Tambah Topik</h2>
                                        <form id="editUserForm">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold" style="text-align: right;">Unit</label>
                                                <input type="text"
                                                    class="form-control @error('unit') is-invalid @enderror" name="unit"
                                                    id="editUnit">
                                                @error('unit')
                                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                @enderror
                                                <label class="font-weight-bold" style="text-align: right;">Topik</label>
                                                <textarea class="form-control @error('topik') is-invalid @enderror"
                                                    name="topik" id="editTopik" rows="5"></textarea>
                                                @error('topik')
                                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-10 offset-sm-2">
                                                    <button type="button" class="btn btn-primary" id="saveButton">Simpan</button>
                                                    <button id="cancelButton"
                                                        class="btn btn-secondary">Batalkan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;">Unit</th>
                                        <th style="width: 500px;">Topik</th>
                                        <th style="width: 200px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dataTableBody">
                                    <!-- Table rows will be added here dynamically -->
                                </tbody>
                                <tfoot>
                                    @foreach ($learningUnits as $unit)
                                    <tr>
                                        <td>{{ $unit['id'] }}</td>
                                        <td>{{ $unit['topic'] }}</td>
                                        <td colspan="6" style="text-align: center;">
                                            <a href="/materiPembelajaran/{{ $unit['id'] }}" class="view-button">
                                                <img src="{{ asset('view.png') }}" alt="View Button">
                                                View Level
                                            </a>
                                            <form id="unit-delete-form-{{ $unit['id'] }}" action="{{ route('units.delete', $unit['id']) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <a onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this unit?')) document.getElementById('unit-delete-form-{{ $unit['id'] }}').submit();">
                                                @method('DELETE')
                                                <button type="button" class="delete-button">
                                                    <img src="{{ asset('delete.png') }}" alt="Delete Button">
                                                    Delete
                                                </button>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
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
    var editModal = document.getElementById('editModal');
    var dataTableBody = document.getElementById('dataTableBody');
    var saveButton = document.getElementById('saveButton');
    var editUnit = document.getElementById('editUnit');
    var editTopik = document.getElementById('editTopik');

    // Fungsi untuk menampilkan modal
    topikBtn.addEventListener('click', function () {
        editModal.style.display = 'block';
    });

    // Menutup modal ketika klik di luar konten modal
    window.addEventListener('click', function (event) {
        if (event.target == editModal) {
            editModal.style.display = 'none';
        }
    });

    // Fungsi untuk menyimpan data dan menambahkannya ke tabel
    saveButton.addEventListener('click', function () {
        var unit = editUnit.value.trim(); // Menghilangkan spasi di awal dan akhir
        var topik = editTopik.value.trim(); // Menghilangkan spasi di awal dan akhir

        if (unit && topik) {
            // Buat elemen baris tabel baru
            var row = document.createElement('tr');

            // Buat elemen sel tabel untuk unit
            var cellUnit = document.createElement('td');
            cellUnit.textContent = unit;
            row.appendChild(cellUnit);

            // Buat elemen sel tabel untuk topik
            var cellTopik = document.createElement('td');
            cellTopik.textContent = topik;
            row.appendChild(cellTopik);

            // Buat elemen sel tabel untuk aksi
            var cellAction = document.createElement('td');
            cellAction.innerHTML = `
            <a href="/viewLevel" class="view-button">
                <img src="{{ asset('view.png') }}" alt="View Button" style="width: 20px; height: 20px;">
                View Level
            </a>
            <a href="#" class="delete-link">
                <button type="button" class="delete-button">Delete</button>
            </a>
            `;
            
            row.appendChild(cellAction);

            // Tambahkan baris ke tabel
            dataTableBody.appendChild(row);

            // Kosongkan input setelah menyimpan
            editUnit.value = '';
            editTopik.value = '';

            // Sembunyikan modal
            editModal.style.display = 'none';
        } else {
            alert('Harap isi semua kolom.');
        }
    });
});
</script>
@endsection