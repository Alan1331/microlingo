<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <style>
        .image-text-button {
            display: flex;
            align-items: center;
            background-color: transparent;
            border: none;
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
            color: #000;
            padding-left: 1050px;
        }

        .button-logo {
            width: 18px;
            height: auto;
            margin-right: 10px;
        }

        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgb(0, 0, 0);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.4);
            /* Black w/ opacity */
        }

        .modal-content {
            position: relative;
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 20%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            padding-left: 250px;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .confirm-button,
        .cancel-button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            border: none;
            cursor: pointer;
            border-radius: 3px;
        }

        .confirm-button {
            background-color: #bd2130;
            color: white;
        }

        .cancel-button {
            background-color: #B4B4B8;
            color: white;
        }

        .confirm-button:hover {
            background-color: #A91D3A;
            color: white;
        }

        .cancel-button:hover {
            background-color: #758694;
            color: white;
        }

        .button-container {
            display: flex; 
            justify-content: center;
            margin-top: 20px;
            padding-left: 100px;
        }
    </style>
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>
    
    <ul class="navbar-nav">
            <li class="">
                <a class="d-flex justify-content-end" href="#" id="logout-link" style="text-decoration: none; color: white;">
                    <button type="button" class="image-text-button">
                        <img src="{{ asset('logoutLogo.png') }}" alt="Sign Out Logo" class="button-logo">
                        Keluar
                    </button>
                </a>
            </li>
    </ul>
        
    <ul class="navbar-nav ml-auto"></ul>
    <!-- logoutModal -->
    <div class="modal" id="logoutModal">
        <div class="modal-content" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true" class="close">&times;</span>
            <p>Anda yakin akan keluar?</p>
            <span class="button-container">
                <button id="confirmLogout" class="confirm-button">Ya</button>
            <button id="cancelLogout" class="cancel-button">Tidak</button>
            </span>
            </div>
    </div>
</nav>

<script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const logoutLink = document.getElementById('logout-link');
            const logoutModal = document.getElementById('logoutModal');
            const confirmLogout = document.getElementById('confirmLogout');
            const cancelLogout = document.getElementById('cancelLogout');
            const closeModal = document.getElementsByClassName('close')[0];

            logoutLink.addEventListener('click', function(event) {
                event.preventDefault();
                logoutModal.style.display = 'block';
            });

            confirmLogout.addEventListener('click', function() {
                window.location.href = @json(url('/logoutAdmin'));
            });

            cancelLogout.addEventListener('click', function() {
                logoutModal.style.display = 'none';
            });

            closeModal.addEventListener('click', function() {
                logoutModal.style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target == logoutModal) {
                    logoutModal.style.display = 'none';
                }
            });
        });
    </script>
</nav>