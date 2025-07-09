<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Upload Laporan HSE | Minia</title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/dropzone/min/dropzone.min.css" rel="stylesheet" type="text/css" />
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Upload Laporan HSE</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                    <li class="breadcrumb-item active">File Upload</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Dropzone</h4>
                                <p class="card-title-desc">Pilih satu atau beberapa file laporan, lalu klik tombol "Kirim Semua File" untuk memulai proses impor.</p>
                            </div>
                            <div class="card-body">
                                <div>
                                    <form action="import/hsedailyimport.php" class="dropzone" id="hse-dropzone-form">
                                        <div class="fallback">
                                            <input name="files[]" type="file" multiple="multiple">
                                        </div>
                                        <div class="dz-message needsclick">
                                            <div class="mb-3">
                                                <i class="display-4 text-muted bx bx-cloud-upload"></i>
                                            </div>
                                            <h5>Letakkan file di sini atau klik untuk memilih.</h5>
                                        </div>
                                    </form>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="button" id="kirim-semua-file" class="btn btn-primary waves-effect waves-light">Kirim Semua File</button>
                                </div>
                                <div id="upload-results" class="mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>
<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/libs/dropzone/min/dropzone.min.js"></script>

<script>
// Nonaktifkan pencarian otomatis Dropzone
Dropzone.autoDiscover = false;

var myDropzone = new Dropzone("#hse-dropzone-form", {
    url: "import/hsedailyimport.php", 
    paramName: "files",              
    autoProcessQueue: false,         
    uploadMultiple: true,           
    parallelUploads: 5,              
    addRemoveLinks: true,           
    acceptedFiles: ".xlsx, .xls",    
    dictDefaultMessage: "Letakkan file di sini atau klik untuk memilih",
    dictRemoveFile: "Hapus file",
});

// Ketika tombol "Kirim Semua File" di-klik
document.querySelector("#kirim-semua-file").addEventListener("click", function() {
    if (myDropzone.getQueuedFiles().length > 0) {                        
        myDropzone.processQueue();
    } else {
        alert("Silakan pilih file terlebih dahulu!");
    }
});

// Event ketika semua file dalam satu batch selesai diupload
myDropzone.on("successmultiple", function(files, response) {
    document.getElementById('upload-results').innerHTML += response;
    // Hapus file yang sudah berhasil diupload dari tampilan
    myDropzone.removeAllFiles();
});

myDropzone.on("errormultiple", function(files, response) {
    let errorHtml = "<div style='color:red;'>❌ Terjadi kesalahan saat mengunggah.</div><p>" + response + "</p>";
    document.getElementById('upload-results').innerHTML += errorHtml;
});
</script>

<script src="assets/js/app.js"></script>
</body>
</html>