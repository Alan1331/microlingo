@extends('admin.layouts.app')

@section('content')

<body>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="margin-top: 25px;">
                        <div class="card-header">
                            <h3 class="card-title">Level</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;">Level</th>
                                        <th style="width: 380px;">Topik</th>
                                        <th style="width: 200px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Your table rows here -->
                                </tbody>
                                <tfoot>
                                    @foreach ($levels as $level)
                                    <tr>
                                        <td>{{ $level['id'] }}</td>
                                        <td>{{ $level['topic'] }}</td>
                                        <td colspan="6" style="text-align: center;">
                                        <div class="action-buttons">
                                            <a class="edit-button" unit-id="{{ $unitId }}" level-id="{{ $level['id'] }}">
                                                <img src="{{ asset('edit.png') }}" alt="Edit Button">
                                                Edit/Lihat
                                            </a>
                                            <div id="editModal" class="modalAction">
                                                <div class="modal-content3" data-dismiss="modalAction" aria-label="Close">
                                                    <h2 class="modal-title">Edit Level</h2>
                                                    <form id="level-update-form" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="form-group mb-3">
                                                            <label for="editTopik" class="font-weight-bold">Topik</label>
                                                            <input type="text" class="form-control @error('topic') is-invalid @enderror" name="topic" id="editTopik">
                                                            @error('topic')
                                                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                            @enderror
                                                            <label for="editContent" class="font-weight-bold">Konten Pembelajaran</label>
                                                            <textarea type="text" class="form-control @error('content') is-invalid @enderror" name="content" id="editContent" rows="10" style="min-height: 200px;"></textarea>
                                                            @error('content')
                                                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-sm-10 offset-sm-2">
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                            <button id="cancelButton" class="btn btn-secondary">Batalkan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <a>
                                                <button type="button" class="upload-button" id="uploadBtn">
                                                    <img src="{{ asset('upload.png') }}" alt="Delete Button">
                                                    Unggah
                                                </button>
                                            </a>
                                            <div id="modalUpload" class="modalUpload">
                                                 <div class="modal-content2">
                                                <h1>Upload Video</h1>
                                                <form action="#" method="POST"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="mb-3" style="align-items: center;">
                                                        <label for="video">Choose a video:</label>
                                                        <input type="file" name="video" id="video" accept="video/*"required class="form-control">
                                                    </div>
                                                    <div>
                                                    <button type="button" class="upload-button" id="uploadBtn">
                                                    <img src="{{ asset('upload.png') }}" alt="Delete Button">
                                                    Unggah
                                                </button>
                                                    </div>
                                                </form>
                                                </div>
                                            </div>
                                            <form id="level-delete-form-{{ $level['id'] }}" action="{{ route('units.levels.delete', ['id' => $unitId, 'levelId' => $level['id']]) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <a onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this level?')) document.getElementById('level-delete-form-{{ $level['id'] }}').submit();">
                                                @method('DELETE')
                                                <button type="button" class="delete-button" id="deleteBtn">
                                                    <img src="{{ asset('delete.png') }}" alt="Delete Button">
                                                    Hapus
                                                </button>
                                            </a>
                                        </td>
                                        </div>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    var uploadBtn = document.getElementById('uploadBtn');
    var modalUpload = document.getElementById('modalUpload');
    var cancelButton = document.getElementById('cancelButton');
    var editBtn = document.querySelectorAll('.edit-button');
    var editModal = document.getElementById('editModal');

    // Fungsi untuk menampilkan modal upload
    uploadBtn.addEventListener('click', function() {
        modalUpload.style.display = 'block';
    });

    // Fungsi untuk menampilkan modal edit
    editBtn.forEach(function(button) {
        button.addEventListener('click', function() {
            var unitId = this.getAttribute('unit-id');
            var levelId = this.getAttribute('level-id');
            fetch(`/units/${unitId}/levels/${levelId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editTopik').value = data.topic;
                    document.getElementById('editContent').value = data.content;
                    document.getElementById('level-update-form').action = `/materiPembelajaran/${unitId}/levels/${levelId}`;
                    editModal.style.display = "block";
                });
        });
    });

    // Menutup modal ketika klik di luar konten modal
    window.addEventListener('click', function(event) {
        if (event.target == modalUpload) {
            modalUpload.style.display = 'none';
        } else if (event.target == editModal) {
            editModal.style.display = 'none';
        }
    });

    // Menutup modal ketika klik batalkan
    cancelButton.addEventListener('click', function () {
        editModal.style.display = 'none';
    });
});
document.getElementById('deleteBtn').addEventListener('click', function(event) {
        event.preventDefault(); // Mencegah aksi default tombol
        if (confirm('Are you sure you want to delete this item?')) {
            // Lakukan aksi penghapusan di sini, misalnya mengarahkan ke URL penghapusan
            window.location.href = 'URL_PENGHAPUSAN'; // Ganti dengan URL untuk menghapus item
        }
    });
        
    </script>
</body>
<style>

    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 10px; /* Jarak antara tombol */
    }

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

    .upload-button {
        display: inline-block;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: bold;
        color: white;
        background-color: #FFB200;
        /* Warna merah untuk tombol delete */
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, color 0.3s;
        text-align: center;
    }

    .upload-button img {
        width: 20px;
        /* Sesuaikan ukuran gambar */
        height: 20px;
        /* Sesuaikan ukuran gambar */
        margin-right: 5px;
        /* Jarak antara gambar dan teks */
    }

    .upload-button:hover {
        background-color: #EB5B00;
        /* Warna merah gelap saat hover */
        color: #ffffff;
    }

    .modalUpload {
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

    .modal-content2 {
        position: relative;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 50%;
        margin: 15% auto;
    }

    .modal-box {
        border: 2px solid #ccc;
        padding: 20px;
        border-radius: 8px;
        background-color: #f9f9f9;
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

    .modal-content3 {
        position: relative;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 50%;
        margin: 5% auto;
    }

    .modal-title {
        text-align: left;
        margin-bottom: 20px;
    }
</style>

@endsection