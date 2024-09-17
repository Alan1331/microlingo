@extends('admin.layouts.app')

@section('content')

<body>
    <section class="content">
        <div class="container-fluid">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

            <div class="row">
                <div class="col-12">
                    <div class="card" style="margin-top: 25px;">
                        <div class="card-header">
                            <a href="{{route('materiPembelajaran')}}">
                                <button class="back-button">
                                    <img src="{{ asset('backk.png') }}" alt="Back Button">
                                    Back
                                </button>
                            </a>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h1 class="brand-text font-poppins-semibold" style="margin: 0;">Daftar Level di Unit {{$unitNumber}}</h1>
                                <a href="{{ route('units.levels.form', ['unitId' => $unitId]) }}" id="addLevel" style="display: flex; align-items: center; margin-bottom: 10px;">
                                    <button class="add-button">
                                        <img src="{{ asset('add.png') }}" alt="add Button" style="margin-right: 8px;">
                                        Tambah Level
                                    </button>
                                </a>
                            </div>
                            <b style="color: #dc3545;">Catatan : hanya level dengan status aktif yang ditampilkan ke pengguna pada WhatsApp chatbot</b>
                            @if($levels->count() != 0)
                                <table id="example2" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">Level</th>
                                            <th style="width: 40%;">Topik</th>
                                            <th style="width: 10%; text-align: center;">Pertanyaan</th>
                                            <th style="width: 10%; text-align: center;">Avg. Nilai</th>
                                            <th style="width: 10%; text-align: center;">Status</th>
                                            <th style="width: 34%; text-align: center;">Aksi</th>
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
                                                <td style="text-align: center;">
                                                    {{ $level->questions()->count() }}
                                                </td>
                                                <td style="text-align: center;">
                                                    {{ $level->averageGrade }}%
                                                </td>
                                                <td class="status-column">
                                                    <!-- Toggle Switch -->
                                                    <label class="switch">
                                                        <input type="checkbox" class="toggle-status" data-level-id="{{ $level['id'] }}" {{ $level->isActive ? 'checked' : '' }}>
                                                        <span class="slider round"></span>
                                                    </label>
                                                    <br>
                                                    <!-- Status Text -->
                                                    <span class="status-text-{{ $level->id }}">
                                                        @if($level->isActive)
                                                            <span class="text-success">Aktif</span>
                                                        @else
                                                            <span class="text-danger">Tidak Aktif</span>
                                                        @endif
                                                    </span>
                                                </td>
                                                <td colspan="6" style="text-align: center;">
                                                    <a href="/levels/{{ $level->id }}">
                                                        <button type="button" class="edit-button">
                                                            <img src="{{ asset('edit.png') }}" alt="Edit Button">
                                                            Perbarui
                                                        </button>
                                                    </a>
                                                    <form id="level-delete-form-{{ $level->id }}" action="{{ route('units.levels.delete', $level->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                    <a onclick="event.preventDefault(); if(confirm('Apakah admin yakin akan menghapus level ini?\nKarena level setelahnya akan dipindahkan ke atas')) document.getElementById('level-delete-form-{{ $level->id }}').submit();">
                                                        @method('DELETE')
                                                        <button type="button" class="delete-button">
                                                            <img src="{{ asset('delete.png') }}" alt="Delete Button">
                                                            Hapus
                                                        </button>
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
   
    // Attach event listener to all toggle-status switches
    document.querySelectorAll('.toggle-status').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var levelId = this.getAttribute('data-level-id');
            var isChecked = this.checked; // Check if the switch is turned on or off

            // Immediately update the UI (Optimistic update)
            var statusTextElement = document.querySelector('.status-text-' + levelId);
            if (isChecked) {
                statusTextElement.innerHTML = '<span class="text-success">Aktif</span>';
            } else {
                statusTextElement.innerHTML = '<span class="text-danger">Tidak Aktif</span>';
            }

            // Perform an AJAX request to toggle the status
            fetch(`/levels/${levelId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    levelId: levelId,
                    isActive: isChecked // Send the new status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert the UI if the server returns an error (if needed)
                    alert('Failed to update status.');
                    // Revert to the previous state
                    if (isChecked) {
                        statusTextElement.innerHTML = '<span class="text-danger">Tidak Aktif</span>';
                    } else {
                        statusTextElement.innerHTML = '<span class="text-success">Aktif</span>';
                    }
                    toggle.checked = !isChecked; // Revert the checkbox state
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Revert the UI if there's an error
                if (isChecked) {
                    statusTextElement.innerHTML = '<span class="text-danger">Tidak Aktif</span>';
                } else {
                    statusTextElement.innerHTML = '<span class="text-success">Aktif</span>';
                }
                toggle.checked = !isChecked; // Revert the checkbox state
            });
        });
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
    .status-column {
        text-align: center;
        vertical-align: middle;
    }

    .status-container {
        display: flex;
        flex-direction: column; /* Stack elements vertically */
        align-items: center;    /* Center them horizontally */
        justify-content: center; /* Center them vertically */
    }

    .switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
    }

    /* Hide default HTML checkbox */
    .switch input {
    opacity: 0;
    width: 0;
    height: 0;
    }

    /* The slider */
    .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    }

    .slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    }

    input:checked + .slider {
    background-color: #2196F3;
    }

    input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
    transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
    border-radius: 34px;
    }

    .slider.round:before {
    border-radius: 50%;
    }

    .toggle-status-button {
        font-weight: bold;
        text-decoration: none;
    }

    .toggle-status-button:hover {
        text-decoration: underline;
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