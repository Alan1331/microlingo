@extends('admin.layouts.app')

@section('content')

<body>
    <section class="content">
        <div class="modal-content3" data-dismiss="modalAction" aria-label="Close">
            <h2 class="modal-title">Update Level</h2>
            <form id="level-update-form" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group mb-3">
                    <label for="editTopik" class="font-weight-bold">Topik</label>
                    <input type="text" class="form-control @error('topic') is-invalid @enderror" name="topic"
                        id="editTopik">
                    @error('topic')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                    <label for="editContent" class="font-weight-bold">Konten
                        Pembelajaran</label>
                    <textarea type="text" class="form-control @error('content') is-invalid @enderror" name="content"
                        id="editContent" rows="10" style="min-height: 200px;"></textarea>
                    @error('content')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                    <label for="editVideo" class="font-weight-bold">Link
                        Video</label>
                    <input type="text" class="form-control @error('videoLink') is-invalid @enderror" name="videoLink"
                        id="editVideo">
                    @error('videoLink')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror

                    <div class="essay">
    <label for="category" class="font-weight-bold">Pilih Kategori Pertanyaan:</label>
    <select class="form-control" name="category" id="category" onchange="toggleForm()">
        <option value="" disabled selected>Pilih Kategori Pertanyaan</option>
        <option value="essay">Essay</option>
        <option value="pilihanGanda">Pilihan Ganda</option>
    </select>

    <!-- Essay Form (hidden by default) -->
    <div id="essayForm" style="display: none;">
        <label for="editPertanyaan" class="font-weight-bold">Pertanyaan (Essay)</label>
        <input type="text" class="form-control @error('pertanyaan') is-invalid @enderror" name="pertanyaan" id="editPertanyaan">
        @error('pertanyaan')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror
        <label for="editJawaban" class="font-weight-bold">Jawaban</label>
        <input type="text" class="form-control @error('jawaban') is-invalid @enderror" name="jawaban" id="editJawaban">
        @error('jawaban')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror
    </div>

    <!-- Pilihan Ganda Form (hidden by default) -->
    <div id="pilihanGandaForm" style="display: none;">
        <form action="/submit" method="POST">
            <label for="question">Pertanyaan (Pilihan Ganda):</label><br>
            <input type="text" id="editableQuestion" name="editableQuestion" style="border: none; background: transparent; outline: none; font-size: 16px; margin-bottom: 20px;"
                value="Isi Pertanyaan di sini"/><br>
            <input type="radio" id="customOption1" name="choice" value="custom1">
            <label for="customOption1">
                <input type="text" name="customText1" placeholder="Isi Jawaban 1" />
            </label><br>
            <input type="radio" id="customOption2" name="choice" value="custom2">
            <label for="customOption2">
                <input type="text" name="customText2" placeholder="Isi Jawaban 2" />
            </label><br>
            <input type="radio" id="customOption3" name="choice" value="custom3">
            <label for="customOption3">
                <input type="text" name="customText3" placeholder="Isi Jawaban 3" />
            </label><br>
        </form>
    </div>      
                    </div>    
                    <div class="essay2">
    <label for="category2" class="font-weight-bold">Pilih Kategori Pertanyaan:</label>
    <select class="form-control" name="category2" id="category2" onchange="toggleForm()">
        <option value="" disabled selected>Pilih Kategori Pertanyaan</option>
        <option value="essay2">Essay</option>
        <option value="pilihanGanda2">Pilihan Ganda</option>
    </select>

    <!-- Essay Form (hidden by default) -->
    <div id="essayForm2" style="display: none;">
        <label for="editPertanyaan" class="font-weight-bold">Pertanyaan (Essay)</label>
        <input type="text" class="form-control @error('pertanyaan') is-invalid @enderror" name="pertanyaan" id="editPertanyaan">
        @error('pertanyaan')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror
        <label for="editJawaban" class="font-weight-bold">Jawaban</label>
        <input type="text" class="form-control @error('jawaban') is-invalid @enderror" name="jawaban" id="editJawaban">
        @error('jawaban')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror
    </div>

    <!-- Pilihan Ganda Form (hidden by default) -->
    <div id="pilihanGandaForm2" style="display: none;">
        <form action="/submit" method="POST">
            <label for="question">Pertanyaan (Pilihan Ganda):</label><br>
            <input type="text" id="editableQuestion" name="editableQuestion" style="border: none; background: transparent; outline: none; font-size: 16px; margin-bottom: 20px;"
                value="Isi Pertanyaan di sini"/><br>
            <input type="radio" id="customOption1" name="choice" value="custom1">
            <label for="customOption1">
                <input type="text" name="customText1" placeholder="Isi Jawaban 1" />
            </label><br>
            <input type="radio" id="customOption2" name="choice" value="custom2">
            <label for="customOption2">
                <input type="text" name="customText2" placeholder="Isi Jawaban 2" />
            </label><br>
            <input type="radio" id="customOption3" name="choice" value="custom3">
            <label for="customOption3">
                <input type="text" name="customText3" placeholder="Isi Jawaban 3" />
            </label><br>
        </form>
    </div>      
                    </div>    
                    <div class="essay3">
    <label for="category3" class="font-weight-bold">Pilih Kategori Pertanyaan:</label>
    <select class="form-control" name="category3" id="category3" onchange="toggleForm()">
        <option value="" disabled selected>Pilih Kategori Pertanyaan</option>
        <option value="essay3">Essay</option>
        <option value="pilihanGanda3">Pilihan Ganda</option>
    </select>

    <!-- Essay Form (hidden by default) -->
    <div id="essayForm3" style="display: none;">
        <label for="editPertanyaan" class="font-weight-bold">Pertanyaan (Essay)</label>
        <input type="text" class="form-control @error('pertanyaan') is-invalid @enderror" name="pertanyaan" id="editPertanyaan">
        @error('pertanyaan')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror
        <label for="editJawaban" class="font-weight-bold">Jawaban</label>
        <input type="text" class="form-control @error('jawaban') is-invalid @enderror" name="jawaban" id="editJawaban">
        @error('jawaban')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror
    </div>

    <!-- Pilihan Ganda Form (hidden by default) -->
    <div id="pilihanGandaForm3" style="display: none;">
        <form action="/submit" method="POST">
            <label for="question">Pertanyaan (Pilihan Ganda):</label><br>
            <input type="text" id="editableQuestion" name="editableQuestion" style="border: none; background: transparent; outline: none; font-size: 16px; margin-bottom: 20px;"
                value="Isi Pertanyaan di sini"/><br>
            <input type="radio" id="customOption1" name="choice" value="custom1">
            <label for="customOption1">
                <input type="text" name="customText1" placeholder="Isi Jawaban 1" />
            </label><br>
            <input type="radio" id="customOption2" name="choice" value="custom2">
            <label for="customOption2">
                <input type="text" name="customText2" placeholder="Isi Jawaban 2" />
            </label><br>
            <input type="radio" id="customOption3" name="choice" value="custom3">
            <label for="customOption3">
                <input type="text" name="customText3" placeholder="Isi Jawaban 3" />
            </label><br>
        </form>
    </div>      
                    </div>    
    </section>
</body>
<script>
    function toggleForm() {
        const category = document.getElementById("category").value;
        const category2 = document.getElementById("category2").value;
        const category3 = document.getElementById("category3").value;
        const essayForm = document.getElementById("essayForm");
        const essayForm2 = document.getElementById("essayForm2");
        const essayForm3 = document.getElementById("essayForm3");
        const pilihanGandaForm = document.getElementById("pilihanGandaForm");
        const pilihanGandaForm2 = document.getElementById("pilihanGandaForm2");
        const pilihanGandaForm3 = document.getElementById("pilihanGandaForm3");

        // Hide both forms initially
        essayForm.style.display = "none";
        pilihanGandaForm.style.display = "none";
        essayForm2.style.display = "none";
        pilihanGandaForm2.style.display = "none";
        essayForm3.style.display = "none";
        pilihanGandaForm3.style.display = "none";

        if (category === "essay") {
            essayForm.style.display = "block";
        } 
        if (category === "pilihanGanda") {
            pilihanGandaForm.style.display = "block";
        } 
        if (category2 === "essay2") {
            essayForm2.style.display = "block";
        } 
        if (category2 === "pilihanGanda2") {
            pilihanGandaForm2.style.display = "block";
        } 
        if (category3 === "essay3") {
            essayForm3.style.display = "block";
        } 
        if (category3 === "pilihanGanda3") {
            pilihanGandaForm3.style.display = "block";
        }
    }
</script>
<style>


    .modal-content3 {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 100%;
        margin-top: 1%;
    }

    .essay {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 100%;
        margin: 1% auto;
    }
    .essay2 {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 100%;
        margin: 1% auto;
    }
    .essay3 {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 100%;
        margin: 1% auto;
    }

    .pilihanGandaForm {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 100%;
        margin: 5% auto;
    }
    .pilihanGandaForm2 {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 100%;
        margin: 5% auto;
    }
    .pilihanGandaForm3 {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        width: 100%;
        margin: 5% auto;
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
</style>
@endsection