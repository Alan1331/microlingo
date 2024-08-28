@extends('admin.layouts.app')

@section('content')

<body>
    <section class="content">
        <div class="modal-content3" data-dismiss="modalAction" aria-label="Close">
            <h2 class="modal-title">Update Unit {{$level->learningUnit->sortId}} Level {{$level->sortId}}</h2>
            <form id="level-update-form" action="{{ route('units.levels.update', ['levelId' => $level->id]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group mb-3">
                    <a href="{{route('units.levels', ['id' => $level->learningUnit->id])}}" class="back-button">
                        <img src="{{ asset('backk.png') }}" alt="Back Button">
                        Back
                    </a>
                    
                    <br>
                    <label for="editTopik" class="font-weight-bold">Topik</label>
                    <input required type="text" class="form-control" name="topic" id="editTopik" value="{{ old('topic', $level->topic) }}">
                    <label for="editContent" class="font-weight-bold">Konten Pembelajaran</label>
                    <textarea required type="text" class="form-control" name="content"
                        id="editContent" rows="10" style="min-height: 200px;">{{ old('content', $level->content) }}</textarea>
                    <label for="editVideo" class="font-weight-bold">Link Video</label>
                    <input required type="text" class="form-control" name="videoLink" id="editVideo" value="{{ old('videoLink', $level->videoLink) }}">

                    <div class="essay">
                        <label for="category1" class="font-weight-bold">Pertanyaan 1 (wajib diisi):</label>
                        @if(isset($questions[0]))
                            <input type="hidden" name="questionId1" value="{{$questions[0]->id}}"/>
                            <select class="form-control" required name="category1" id="category1" onchange="toggleForm()">
                                <option value="" disabled selected hidden>Pilih Kategori Pertanyaan</option>
                                <option value="Essay" {{old('type', $questions[0]->type) == 'Essay' ? 'selected':''}}>Essay</option>
                                <option value="Multiple Choice" {{old('type', $questions[0]->type) == 'Multiple Choice' ? 'selected':''}}>Pilihan Ganda</option>
                            </select>
                        @else
                            <input type="hidden" name="questionId1" value="-1"/>
                            <select class="form-control" required name="category1" id="category1" onchange="toggleForm()">
                                <option value="" disabled selected hidden>Pilih Kategori Pertanyaan</option>
                                <option value="Essay">Essay</option>
                                <option value="Multiple Choice">Pilihan Ganda</option>
                            </select>
                        @endif

                    <!-- Essay Form (hidden by default) -->
                    <div id="essayForm" style="display: none;">
                        @if(isset($questions[0]) && $questions[0]->type == "Essay")
                            <label for="editableQuestionEssay1" class="font-weight-bold">Pertanyaan (Essay)</label>
                            <input type="text" class="form-control" value="{{$questions[0]->question}}" name="editableQuestionEssay1" id="editableQuestionEssay1">
                            <label for="editableAnswer1" class="font-weight-bold">Jawaban</label>
                            <input type="text" class="form-control" value="{{$questions[0]->answer}}" name="editableAnswer1" id="editableAnswer1">
                        @else
                            <label for="editableQuestionEssay1" class="font-weight-bold">Pertanyaan (Essay)</label>
                            <input type="text" class="form-control" name="editableQuestionEssay1" id="editableQuestionEssay1">
                            <label for="editableAnswer1" class="font-weight-bold">Jawaban</label>
                            <input type="text" class="form-control" name="editableAnswer1" id="editableAnswer1">
                        @endif
                    </div>

                    <!-- Pilihan Ganda Form (hidden by default) -->
                    <div id="pilihanGandaForm" style="display: none;">
                        @if(isset($questions[0]) && $questions[0]->type == "Multiple Choice")
                            <label for="editableQuestionMp1">Pertanyaan (Pilihan Ganda):</label><br>
                            <input type="text" id="editableQuestionMp1" name="editableQuestionMp1"
                                value="{{$questions[0]->question}}" placeholder="Isi Pertanyaan di sini"/><br>
                            <fieldset>
                                <input type="radio" id="customOption1-1" {{old('answer', $questions[0]->answer) == 'a' ? 'checked':''}} name="choice1" value="a">
                                <label for="customOptionInput1-1">
                                    <input type="text" id="customOptionInput1-1" value="{{$questions[0]->optionA}}" name="customOptionInput1-1" placeholder="Isi Jawaban A" />
                                </label><br>
                                <input type="radio" id="customOption1-2" {{old('answer', $questions[0]->answer) == 'b' ? 'checked':''}} name="choice1" value="b">
                                <label for="customOptionInput1-2">
                                    <input type="text" id="customOptionInput1-2" value="{{$questions[0]->optionB}}" name="customOptionInput1-2" placeholder="Isi Jawaban B" />
                                </label><br>
                                <input type="radio" id="customOption1-3" {{old('answer', $questions[0]->answer) == 'c' ? 'checked':''}} name="choice1" value="c">
                                <label for="customOptionInput1-3">
                                    <input type="text" id="customOptionInput1-3" value="{{$questions[0]->optionC}}" name="customOptionInput1-3" placeholder="Isi Jawaban C" />
                                </label><br>
                            </fieldset>
                        @else
                            <label for="editableQuestionMp1">Pertanyaan (Pilihan Ganda):</label><br>
                            <input type="text" id="editableQuestionMp1" name="editableQuestionMp1"
                                placeholder="Isi Pertanyaan di sini"/><br>
                            <fieldset>
                                <input type="radio" id="customOption1-1" name="choice1" value="a">
                                <label for="customOptionInput1-1">
                                    <input type="text" id="customOptionInput1-1" name="customOptionInput1-1" placeholder="Isi Jawaban A" />
                                </label><br>
                                <input type="radio" id="customOption1-2" name="choice1" value="b">
                                <label for="customOptionInput1-2">
                                    <input type="text" id="customOptionInput1-2" name="customOptionInput1-2" placeholder="Isi Jawaban B" />
                                </label><br>
                                <input type="radio" id="customOption1-3" name="choice1" value="c">
                                <label for="customOptionInput1-3">
                                    <input type="text" id="customOptionInput1-3" name="customOptionInput1-3" placeholder="Isi Jawaban C" />
                                </label><br>
                            </fieldset>
                        @endif
                    </div>
                    </div>
                    <div class="essay2">
                    <label for="category2" class="font-weight-bold">Pertanyaan 2:</label>
                    @if(isset($questions[1]))
                        <input type="hidden" name="questionId2" value="{{$questions[1]->id}}"/>
                        <select class="form-control" name="category2" id="category2" onchange="toggleForm()">
                            <option value="" disabled selected>Pilih Kategori Pertanyaan</option>
                            <option value="Delete">Hapus Pertanyaan</option>
                            <option value="Essay" {{old('type', $questions[1]->type) == 'Essay' ? 'selected':''}}>Essay</option>
                            <option value="Multiple Choice" {{old('type', $questions[1]->type) == 'Multiple Choice' ? 'selected':''}}>Pilihan Ganda</option>
                        </select>
                    @else
                        <input type="hidden" name="questionId2" value="-1"/>
                        <select class="form-control" name="category2" id="category2" onchange="toggleForm()">
                            <option value="" disabled selected>Pilih Kategori Pertanyaan</option>
                            <option value="Delete">Hapus Pertanyaan</option>
                            <option value="Essay">Essay</option>
                            <option value="Multiple Choice">Pilihan Ganda</option>
                        </select>
                    @endif

                    <!-- Essay Form (hidden by default) -->
                    <div id="essayForm2" style="display: none;">
                        @if(isset($questions[1]) && $questions[1]->type == "Essay")
                            <label for="editableQuestionEssay2" class="font-weight-bold">Pertanyaan (Essay)</label>
                            <input type="text" class="form-control" value="{{$questions[1]->question}}" name="editableQuestionEssay2" id="editableQuestionEssay2">
                            <label for="editableAnswer2" class="font-weight-bold">Jawaban</label>
                            <input type="text" class="form-control" value="{{$questions[1]->answer}}" name="editableAnswer2" id="editableAnswer2">
                        @else
                            <label for="editableQuestionEssay2" class="font-weight-bold">Pertanyaan (Essay)</label>
                            <input type="text" class="form-control" name="editableQuestionEssay2" id="editableQuestionEssay2">
                            <label for="editableAnswer2" class="font-weight-bold">Jawaban</label>
                            <input type="text" class="form-control" name="editableAnswer2" id="editableAnswer2">
                        @endif
                    </div>

                    <!-- Pilihan Ganda Form (hidden by default) -->
                    <div id="pilihanGandaForm2" style="display: none;">
                        @if(isset($questions[1]) && $questions[1]->type == "Multiple Choice")
                            <label for="editableQuestionMp2">Pertanyaan (Pilihan Ganda):</label><br>
                            <input type="text" id="editableQuestionMp2" name="editableQuestionMp2"
                                value="{{$questions[1]->question}}" placeholder="Isi Pertanyaan di sini"/><br>
                            <fieldset>
                                <input type="radio" id="customOption2-1" {{old('answer', $questions[1]->answer) == 'a' ? 'checked':''}} name="choice2" value="a">
                                <label for="customOptionInput2-1">
                                    <input type="text" id="customOptionInput2-1" value="{{$questions[1]->optionA}}" name="customOptionInput2-1" placeholder="Isi Jawaban A" />
                                </label><br>
                                <input type="radio" id="customOption2-2" {{old('answer', $questions[1]->answer) == 'b' ? 'checked':''}} name="choice2" value="b">
                                <label for="customOptionInput2-2">
                                    <input type="text" id="customOptionInput2-2" value="{{$questions[1]->optionB}}" name="customOptionInput2-2" placeholder="Isi Jawaban B" />
                                </label><br>
                                <input type="radio" id="customOption2-3" {{old('answer', $questions[1]->answer) == 'c' ? 'checked':''}} name="choice2" value="c">
                                <label for="customOptionInput2-3">
                                    <input type="text" id="customOptionInput2-3" value="{{$questions[1]->optionC}}" name="customOptionInput2-3" placeholder="Isi Jawaban C" />
                                </label><br>
                            </fieldset>
                        @else
                            <label for="editableQuestionMp2">Pertanyaan (Pilihan Ganda):</label><br>
                            <input type="text" id="editableQuestionMp2" name="editableQuestionMp2"
                                placeholder="Isi Pertanyaan di sini"/><br>
                            <fieldset>
                                <input type="radio" id="customOption2-1" name="choice2" value="a">
                                <label for="customOptionInput2-1">
                                    <input type="text" id="customOptionInput2-1" name="customOptionInput2-1" placeholder="Isi Jawaban A" />
                                </label><br>
                                <input type="radio" id="customOption2-2" name="choice2" value="b">
                                <label for="customOptionInput2-2">
                                    <input type="text" id="customOptionInput2-2" name="customOptionInput2-2" placeholder="Isi Jawaban B" />
                                </label><br>
                                <input type="radio" id="customOption2-3" name="choice2" value="c">
                                <label for="customOptionInput2-3">
                                    <input type="text" id="customOptionInput2-3" name="customOptionInput2-3" placeholder="Isi Jawaban C" />
                                </label><br>
                            </fieldset>
                        @endif
                    </div>
                    </div>
                    <div class="essay3">
                    <label for="category3" class="font-weight-bold">Pertanyaan 3:</label>
                    @if(isset($questions[2]))
                        <input type="hidden" name="questionId3" value="{{$questions[2]->id}}"/>
                        <select class="form-control" name="category3" id="category3" onchange="toggleForm()">
                            <option value="" disabled selected>Pilih Kategori Pertanyaan</option>
                            <option value="Delete">Hapus Pertanyaan</option>
                            <option value="Essay" {{old('type', $questions[2]->type) == 'Essay' ? 'selected':''}}>Essay</option>
                            <option value="Multiple Choice" {{old('type', $questions[2]->type) == 'Multiple Choice' ? 'selected':''}}>Pilihan Ganda</option>
                        </select>
                    @else
                        <input type="hidden" name="questionId3" value="-1"/>
                        <select class="form-control" name="category3" id="category3" onchange="toggleForm()">
                            <option value="" disabled selected>Pilih Kategori Pertanyaan</option>
                            <option value="Delete">Hapus Pertanyaan</option>
                            <option value="Essay">Essay</option>
                            <option value="Multiple Choice">Pilihan Ganda</option>
                        </select>
                    @endif

                    <!-- Essay Form (hidden by default) -->
                    <div id="essayForm3" style="display: none;">
                        @if(isset($questions[2]) && $questions[2]->type == "Essay")
                            <label for="editableQuestionEssay3" class="font-weight-bold">Pertanyaan (Essay)</label>
                            <input type="text" class="form-control" value="{{$questions[2]->question}}" name="editableQuestionEssay3" id="editableQuestionEssay3">
                            <label for="editableAnswer3" class="font-weight-bold">Jawaban</label>
                            <input type="text" class="form-control" value="{{$questions[2]->answer}}" name="editableAnswer3" id="editableAnswer3">
                        @else
                            <label for="editableQuestionEssay3" class="font-weight-bold">Pertanyaan (Essay)</label>
                            <input type="text" class="form-control" name="editableQuestionEssay3" id="editableQuestionEssay3">
                            <label for="editableAnswer3" class="font-weight-bold">Jawaban</label>
                            <input type="text" class="form-control" name="editableAnswer3" id="editableAnswer3">
                        @endif
                    </div>

                    <!-- Pilihan Ganda Form (hidden by default) -->
                    <div id="pilihanGandaForm3" style="display: none;">
                        @if(isset($questions[2]) && $questions[2]->type == "Multiple Choice")
                            <label for="editableQuestionMp3">Pertanyaan (Pilihan Ganda):</label><br>
                            <input type="text" id="editableQuestionMp3" name="editableQuestionMp3"
                                value="{{$questions[2]->question}}" placeholder="Isi Pertanyaan di sini"/><br>
                            <fieldset>
                                <input type="radio" id="customOption3-1" {{old('answer', $questions[2]->answer) == 'a' ? 'checked':''}} name="choice3" value="a">
                                <label for="customOptionInput3-1">
                                    <input type="text" id="customOptionInput3-1" value="{{$questions[2]->optionA}}" name="customOptionInput3-1" placeholder="Isi Jawaban A" />
                                </label><br>
                                <input type="radio" id="customOption3-2" {{old('answer', $questions[2]->answer) == 'b' ? 'checked':''}} name="choice3" value="b">
                                <label for="customOptionInput3-2">
                                    <input type="text" id="customOptionInput3-2" value="{{$questions[2]->optionB}}" name="customOptionInput3-2" placeholder="Isi Jawaban B" />
                                </label><br>
                                <input type="radio" id="customOption3-3" {{old('answer', $questions[2]->answer) == 'c' ? 'checked':''}} name="choice3" value="c">
                                <label for="customOptionInput3-3">
                                    <input type="text" id="customOptionInput3-3" value="{{$questions[2]->optionC}}" name="customOptionInput3-3" placeholder="Isi Jawaban C" />
                                </label><br>
                            </fieldset>
                        @else
                            <label for="editableQuestionMp3">Pertanyaan (Pilihan Ganda):</label><br>
                            <input type="text" id="editableQuestionMp3" name="editableQuestionMp3"
                                placeholder="Isi Pertanyaan di sini"/><br>
                            <fieldset>
                                <input type="radio" id="customOption3-1" name="choice3" value="a">
                                <label for="customOptionInput3-1">
                                    <input type="text" id="customOptionInput3-1" name="customOptionInput3-1" placeholder="Isi Jawaban A" />
                                </label><br>
                                <input type="radio" id="customOption3-2" name="choice3" value="b">
                                <label for="customOptionInput3-2">
                                    <input type="text" id="customOptionInput3-2" name="customOptionInput3-2" placeholder="Isi Jawaban B" />
                                </label><br>
                                <input type="radio" id="customOption3-3" name="choice3" value="c">
                                <label for="customOptionInput3-3">
                                    <input type="text" id="customOptionInput3-3" name="customOptionInput3-3" placeholder="Isi Jawaban C" />
                                </label><br>
                            </fieldset>
                        @endif
                    </div>
                </div>
                <button type="submit" class="save-button">
                        Simpan
                    </button>
            </form>
    </section>
</body>
<script>
    // to load the question type after loading the page
    window.onload = function() {
        toggleForm();
    };

    function toggleForm() {
        const category = document.getElementById("category1").value;
        const category2 = document.getElementById("category2").value;
        const category3 = document.getElementById("category3").value;
        const essayForm = document.getElementById("essayForm");
        const essayForm2 = document.getElementById("essayForm2");
        const essayForm3 = document.getElementById("essayForm3");
        const pilihanGandaForm = document.getElementById("pilihanGandaForm");
        const pilihanGandaForm2 = document.getElementById("pilihanGandaForm2");
        const pilihanGandaForm3 = document.getElementById("pilihanGandaForm3");
        
        // question and answer elements for essay
        const editableQuestionEssay = document.getElementById("editableQuestionEssay1")
        const editableQuestionEssay2 = document.getElementById("editableQuestionEssay2")
        const editableQuestionEssay3 = document.getElementById("editableQuestionEssay3")
        const editableAnswer = document.getElementById("editableAnswer1");
        const editableAnswer2 = document.getElementById("editableAnswer2");
        const editableAnswer3 = document.getElementById("editableAnswer3");

        // question and answer elements for multiple choice
        const editableQuestionMp = document.getElementById("editableQuestionMp1")
        const editableQuestionMp2 = document.getElementById("editableQuestionMp2")
        const editableQuestionMp3 = document.getElementById("editableQuestionMp3")
        const customOption1 = document.getElementById("customOption1-1")
        const customOption2 = document.getElementById("customOption2-1")
        const customOption3 = document.getElementById("customOption3-1")
        const customOptionInput1_1 = document.getElementById("customOptionInput1-1")
        const customOptionInput1_2 = document.getElementById("customOptionInput1-2")
        const customOptionInput1_3 = document.getElementById("customOptionInput1-3")
        const customOptionInput2_1 = document.getElementById("customOptionInput2-1")
        const customOptionInput2_2 = document.getElementById("customOptionInput2-2")
        const customOptionInput2_3 = document.getElementById("customOptionInput2-3")
        const customOptionInput3_1 = document.getElementById("customOptionInput3-1")
        const customOptionInput3_2 = document.getElementById("customOptionInput3-2")
        const customOptionInput3_3 = document.getElementById("customOptionInput3-3")

        // Hide both forms initially and set it to not required
        essayForm.style.display = "none";
        pilihanGandaForm.style.display = "none";
        essayForm2.style.display = "none";
        pilihanGandaForm2.style.display = "none";
        essayForm3.style.display = "none";
        pilihanGandaForm3.style.display = "none";
        category.required = true;
        editableQuestionEssay.required = false;
        editableQuestionEssay2.required = false;
        editableQuestionEssay3.required = false;
        editableAnswer.required = false;
        editableAnswer2.required = false;
        editableAnswer3.required = false;
        editableQuestionMp.required = false;
        editableQuestionMp2.required = false;
        editableQuestionMp3.required = false;
        customOption1.required = false;
        customOption2.required = false;
        customOption3.required = false;
        customOptionInput1_1.required = false;
        customOptionInput1_2.required = false;
        customOptionInput1_3.required = false;
        customOptionInput2_1.required = false;
        customOptionInput2_2.required = false;
        customOptionInput2_3.required = false;
        customOptionInput3_1.required = false;
        customOptionInput3_2.required = false;
        customOptionInput3_3.required = false;
        
        if (category === "Essay") {
            essayForm.style.display = "block";

            // set elements in essay to required
            editableQuestionEssay.required = true;
            editableAnswer.required = true;
        }
        if (category === "Multiple Choice") {
            pilihanGandaForm.style.display = "block";

            // set elements in multiple choice to required
            editableQuestionMp.required = true;
            customOption1.required = true;
            customOptionInput1_1.required = true;
            customOptionInput1_2.required = true;
            customOptionInput1_3.required = true;
        }
        if (category2 === "Essay") {
            essayForm2.style.display = "block";

            // set elements in essay to required
            editableQuestionEssay2.required = true;
            editableAnswer2.required = true;
        }
        if (category2 === "Multiple Choice") {
            pilihanGandaForm2.style.display = "block";

            // set elements in multiple choice to required
            editableQuestionMp2.required = true;
            customOption2.required = true;
            customOptionInput2_1.required = true;
            customOptionInput2_2.required = true;
            customOptionInput2_3.required = true;
        }
        if (category3 === "Essay") {
            essayForm3.style.display = "block";

            // set elements in essay to required
            editableQuestionEssay3.required = true;
            editableAnswer3.required = true;
        }
        if (category3 === "Multiple Choice") {
            pilihanGandaForm3.style.display = "block";

            // set elements in multiple choice to required
            editableQuestionMp3.required = true;
            customOption3.required = true;
            customOptionInput3_1.required = true;
            customOptionInput3_2.required = true;
            customOptionInput3_3.required = true;
        }
    }
</script>
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

    .save-button {
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
    
    .save-button img {
        width: 20px;
        /* Sesuaikan ukuran gambar */
        height: 20px;
        /* Sesuaikan ukuran gambar */
        margin-right: 5px;
        /* Jarak antara gambar dan teks */
    }

    .save-button:hover {
        background-color: #03346E;
        /* Warna merah gelap saat hover */
        color: #ffffff;
    }

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