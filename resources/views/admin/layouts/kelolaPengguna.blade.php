@extends('admin.layouts.app')

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card" style="margin-top: 25px;">
                    <div class="card-header">
                        <h3 class="card-title">Kelola Pengguna</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Nomor WhatsApp</th>
                                    <th style="width: 200px;">Nama</th>
                                    <th style="width: 200px;">Pekerjaan</th>
                                    <th style="width: 100px;">Unit</th>
                                    <th style="width: 100px;">Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Your table rows here -->
                            </tbody>
                            <tfoot>
                                @foreach ($users as $user)
                                    <tr>
                                        <?php $progress = explode("-", $user['progress']); ?>
                                        <td>{{$user['phoneNumber']}}</td>
                                        <td>{{$user['name']}}</td>
                                        <td>{{$user['occupation']}}</td>
                                        <td>{{$progress[0]}}</td>
                                        <td>{{$progress[1]}}</td>
                                    </tr>
                                @endforeach
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.13.1/standard/ckeditor.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"></script>
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

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
        white-space: nowrap;
    }

    th {
        background-color: #f4f4f4;
    }

    td {
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    /* Gaya untuk modal pop-up */
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

    .modal-content2 {
        position: relative;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 50%;
        margin: 15% auto;
    }

    .modal-title {
        text-align: left;
        margin-bottom: 20px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .button-container {     
        display: flex;
        justify-content: center;
        margin-top: 20px;
        padding-right: 250px;
    }

    .confirm-button {
        background-color: blue;
        color: white;
    }

    .cancel-button {
        background-color: #B4B4B8;
        color: white;
    }

    .confirm-button:hover {
        background-color: #03346E;
        color: white;
    }

    .cancel-button:hover {
        background-color: #758694;
        color: white;
    }

    .text-left {
        text-align: left;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalAction = document.getElementById("editModal");
        var btn = document.querySelectorAll(".edit-button");
        var cancelBtn = document.getElementById('cancelButton');

        btn.forEach(function(button) {
            button.addEventListener('click', function() {
                var userId = this.getAttribute('data-id');
                fetch(`/users/${userId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('editNama').value = data.nama;
                        document.getElementById('editPekerjaan').value = data.pekerjaan;
                        document.getElementById('editUnit').value = data.progress.split("-")[0];
                        document.getElementById('editLevel').value = data.progress.split("-")[1];
                        document.getElementById('user-update-form').action = `/admin-page/users/${userId}`;
                        modalAction.style.display = "block";
                    });
            });
        });

        var span = document.getElementsByClassName("close")[0];
        span.onclick = function () {
            modalAction.style.display = "none";
        }

        window.onclick = function (event) {
            if (event.target == modalAction) {
                modalAction.style.display = "none";
            }
        }

        // Menutup modal ketika klik batalkan
        cancelBtn.addEventListener('click', function () {
            editModal.style.display = 'none';
        });
    });

    function confirmDelete(userName) {
        return confirm('Are you sure you want to delete user named' + userName + '?')
    }
</script>
@endsection