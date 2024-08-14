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
                            @if($levels->count() != 0)
                            <table id="example2" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;">Level</th>
                                        <th style="width: 380px;">Topik</th>
                                        <th style="width: 50px;">Pertanyaan</th>
                                        <th style="width: 150px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Your table rows here -->
                                </tbody>
                                <tfoot>
                                    @foreach ($levels as $level)
                                    <tr>
                                        <td>{{ $level->sortId }}</td>
                                        <td>{{ $level->topic }}</td>
                                        <td>{{ $level->questions()->count() }}</td>
                                        <td colspan="6" style="text-align: center;">
                                            <a class="edit-button" level-id="{{ $level->id }}">
                                                <img src="{{ asset('edit.png') }}" alt="Edit Button">
                                                Update
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
                                                            <label for="editVideo" class="font-weight-bold">Link Video</label>
                                                            <input type="text" class="form-control @error('videoLink') is-invalid @enderror" name="videoLink" id="editVideo">
                                                            @error('videoLink')
                                                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="col-sm-10 offset-sm-2">
                                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                                            <button type="button" id="cancelButton" class="btn btn-secondary">Batalkan</button>
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
                            <b>Tidak ada level untuk unit ini</b>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    var uploadBtn = document.querySelectorAll('.upload-button');
    var modalUpload = document.getElementById('modalUpload');
    var cancelButton = document.getElementById('cancelButton');
    var editBtn = document.querySelectorAll('.edit-button');
    var editModal = document.getElementById('editModal');

    // Fungsi untuk menampilkan modal upload
    uploadBtn.forEach(function(button) {
        button.addEventListener('click', function() {
            var unitId = this.getAttribute('unit-id');
            var levelId = this.getAttribute('level-id');
    
            // Dynamically set the form action
            var actionUrl = `/materiPembelajaran/${unitId}/levels/${levelId}/videos`;
            document.getElementById('uploadForm').action = actionUrl;
            modalUpload.style.display = 'block';
        });
    });

    // Fungsi untuk menampilkan modal edit
    editBtn.forEach(function(button) {
        button.addEventListener('click', function() {
            var levelId = this.getAttribute('level-id');
            fetch(`/levels/${levelId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editTopik').value = data.topic;
                    document.getElementById('editContent').value = data.content;
                    document.getElementById('editVideo').value = data.videoLink;
                    document.getElementById('level-update-form').action = `/levels/${levelId}`;
                    editModal.style.display = "block";
                });
        });
    });

    // Handle form submission
    uploadForm.addEventListener('submit', function (event) {
        event.preventDefault();

        // Hide the upload button and show the loading button
        document.getElementById('uploadButton').style.display = 'none';
        document.getElementById('loadingButton').style.display = 'inline-block';

        // Create a FormData object from the form
        var formData = new FormData(uploadForm);

        // Log the form data for debugging
        for (var pair of formData.entries()) {
            console.log(pair[0]+ ', ' + pair[1]); 
        }

        // Send the AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('POST', uploadForm.action, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        xhr.onload = function () {
            if (xhr.status === 200) {
                // Success
                alert('Videos uploaded successfully!');
            } else {
                // Error
                alert('An error occurred while uploading the videos.');
            }

            // Reset the form
            uploadForm.reset();

            // Show the upload button and hide the loading button
            document.getElementById('uploadButton').style.display = 'inline-block';
            document.getElementById('loadingButton').style.display = 'none';

            // Close the modal
            modalUpload.style.display = 'none';
        };

        xhr.send(formData);
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