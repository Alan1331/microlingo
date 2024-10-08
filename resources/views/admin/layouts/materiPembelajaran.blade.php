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
                                <h1 class="brand-text font-poppins-semibold" style="margin: 0;">Kelola Unit Pembelajaran</h1>
                                <a style="display: flex; align-items: center; margin-bottom: 10px;">
                                    <button type="button" class="add-button" id="topikBtn">
                                        <img src="{{ asset('add.png') }}" alt="add Button" style="margin-right: 8px;">
                                        Tambah Unit
                                    </button>
                                </a>
                                <div id="editModal" class="modalAction">
                                    <div class="modal-content4" data-dismiss="modalAction" aria-label="Close">
                                        <h2 class="modal-title">Tambah Unit Pembelajaran</h2>
                                        <form id="addUnitForm" method="POST" action="{{ route('units.create') }}">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label for="topik" class="font-weight-bold" style="text-align: right;">Topik</label>
                                                <input type="text" class="form-control @error('topik') is-invalid @enderror"
                                                    name="topic" id="topik"></input>
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
                                        <th style="width: 5.56%;">Unit</th>
                                        <th style="width: 55.56%;">Topik</th>
                                        <th style="width: 38.89%; text-align: center;">Aksi</th>
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
                                            <a href="/materiPembelajaran/{{ $unit['id'] }}">
                                                <button type="button" class="view-button">
                                                    <img src="{{ asset('view.png') }}" alt="View Button">
                                                    Lihat Level
                                                </button>
                                            </a>
                                            <a>
                                                <button type="button" class="edit-button" unit-id="{{ $unit['id'] }}">
                                                    <img src="{{ asset('edit.png') }}" alt="Edit Button">
                                                    Perbarui
                                                </button>
                                            </a>
                                            <form id="unit-delete-form-{{ $unit['id'] }}" action="{{ route('units.delete', $unit['id']) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <a onclick="event.preventDefault(); if(confirm('Apakah admin yakin akan menghapus unit ini?\nKarena unit setelahnya akan dipindahkan ke atas')) document.getElementById('unit-delete-form-{{ $unit['id'] }}').submit();">
                                                @method('DELETE')
                                                <button type="button" class="delete-button">
                                                    <img src="{{ asset('delete.png') }}" alt="Delete Button">
                                                    Hapus
                                                </button>
                                            </a>
                                    <div id="updateModal-{{ $unit->id }}" class="modalAction2 update-modal">
                                    <div class="modal-content4" data-dismiss="modalAction2" aria-label="Close">
                                        <h2 class="update-modal-title" id="updateModalTitle">Perbarui Topik Unit {{ $unit->sortId }}</h2>
                                        <form id="unit-update-form" method="POST" action="{{ route('units.update', ['id' => $unit->id]) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="form-group mb-3">
                                                <input type="text" class="form-control" name="topic" id="editTopik" value="{{ $unit->topic }}">
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
    var updateBtn = document.querySelectorAll('.edit-button');
    var updateModal = document.querySelectorAll('.update-modal');
    var editModal = document.getElementById('editModal');
    var dataTableBody = document.getElementById('dataTableBody');
    var cancelBtn = document.getElementById('cancelBtn');
    var editUnit = document.getElementById('editUnit');

    // Fungsi untuk menampilkan modal
    topikBtn.addEventListener('click', function () {
        editModal.style.display = 'block';
    });


    // Fungsi untuk menampilkan modal update topik
    updateBtn.forEach(function(button) {
        button.addEventListener('click', function() {
            var unitId = this.getAttribute('unit-id');
            document.getElementById("updateModal-" + unitId).style.display = "block";
        });
    });

    // Menutup modal ketika klik di luar konten modal
    window.addEventListener('click', function (event) {
        if (event.target == editModal) {
            editModal.style.display = 'none';
        }
    });
    
    // Menutup modal ketika klik batalkan
    cancelBtn.addEventListener('click', function () {
        editModal.style.display = 'none';
    });

    updateModal.forEach(function(modal) {
        // Add click event listener to the entire modal
        modal.addEventListener('click', function(event) {
            // Menutup modal update ketika klik di luar konten modal
            if (event.target === modal) {
                // Close the modal by removing the 'open' class or setting display to 'none'
                modal.style.display = 'none';
            }
        });

        // Retrieve the cancel button inside the modal by its ID
        var cancelButton = modal.querySelector('#cancelBtn2');

        // Add a click event listener to the cancel button
        if (cancelButton) {  // Check if the cancel button exists
            cancelButton.addEventListener('click', function() {
                // Set the modal's display style to 'none' to hide it
                modal.style.display = 'none';
            });
        }
    });

});
</script>
@endsection