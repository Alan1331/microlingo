@extends('admin.layouts.app')

@section('content')

<body>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="margin-top: 25px;">
                        <div class="card-header">
                            <h1 class="card-title">Daftar Level di Unit {{$unitNumber}}</h1>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <a href="{{route('materiPembelajaran')}}" class="back-button">
                                <img src="{{ asset('backk.png') }}" alt="Back Button">
                                Back
                            </a>
                            @if($levels->count() != 0)
                                <table id="example2" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 100px;">Level</th>
                                            <th style="width: 300px;">Topik</th>
                                            <th style="width: 100px;">Pertanyaan</th>
                                            <th style="width: 100px;">Avg. Nilai</th>
                                            <th style="width: 100px;">Status</th>
                                            <th style="width: 100px;">Aksi</th>
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
                                                <td>{{ $level->averageGrade }}%</td>
                                                @if($level->isActive)
                                                    <td>Aktif</td>
                                                @else
                                                    <td>Tidak Aktif</td>
                                                @endif
                                                <td colspan="6" style="text-align: center;">
                                                    <a href="/updateLevel/{{ $level->id }}" class="edit-button">
                                                        <img src="{{ asset('edit.png') }}" alt="Edit Button">
                                                        Update
                                                    </a>
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
        document.addEventListener('DOMContentLoaded', function () {
    var uploadBtn = document.querySelectorAll('.upload-button');
    var modalUpload = document.getElementById('modalUpload');
    var cancelButton = document.getElementById('cancelButton');
    var editBtn = document.querySelectorAll('.edit-button');
    var editModal = document.getElementById('editModal');
   

    // Fungsi untuk menampilkan modal upload
    uploadBtn.forEach(function (button) {
        button.addEventListener('click', function () {
            var unitId = this.getAttribute('unit-id');
            var levelId = this.getAttribute('level-id');

            // Dynamically set the form action
            var actionUrl = `/materiPembelajaran/${unitId}/levels/${levelId}/videos`;
            document.getElementById('uploadForm').action = actionUrl;
            modalUpload.style.display = 'block';
        });
    });

    // Fungsi untuk menampilkan modal edit di bawah tabel example2
    editBtn.forEach(function (button) {
        button.addEventListener('click', function () {
            var levelId = this.getAttribute('level-id');
            fetch(`/levels/${levelId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editTopik').value = data.topic;
                    document.getElementById('editContent').value = data.content;
                    document.getElementById('editVideo').value = data.videoLink;
                    document.getElementById('level-update-form').action = `/levels/${levelId}`;
                    
                    // Pindahkan modal ke bawah tabel example2 dan tampilkan
                    exampleTable.insertAdjacentElement('afterend', editModal);
                    editModal.style.display = "block";
                });
        });
    });

    // Menangani pengiriman form edit untuk memperbarui data di halaman yang sama tanpa reload
    document.getElementById('level-update-form').addEventListener('submit', function (event) {
        event.preventDefault(); // Mencegah form melakukan submit secara default

        var formData = new FormData(this);
        var actionUrl = this.action;

        fetch(actionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the page content or specific element(s) with the updated data
                alert('Level updated successfully!');
                // Here you can update the UI dynamically without refreshing the page
                editModal.style.display = 'none'; // Close the modal
            } else {
                alert('An error occurred while updating the level.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        });
    });

    // Menutup modal ketika klik di luar konten modal
    window.addEventListener('click', function (event) {
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

document.getElementById('deleteBtn').addEventListener('click', function (event) {
    event.preventDefault(); // Mencegah aksi default tombol
    if (confirm('Are you sure you want to delete this item?')) {
        // Lakukan aksi penghapusan di sini, misalnya mengarahkan ke URL penghapusan
        window.location.href = 'URL_PENGHAPUSAN'; // Ganti dengan URL untuk menghapus item
    }
});

    </script>
</body>
<style>
    .back-button {
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
    
    .back-button img {
        width: 20px;
        height: 20px;
    }

    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
        /* Jarak antara tombol */
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