@extends('adminlte::page')

@section('title', 'Upload Invoices')

@section('content_header')
    <h1>Upload Invoices</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-upload mr-2"></i> Upload Your Invoices
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Our AI will automatically extract and structure the data from your invoices.</p>

                    <form method="POST" action="{{ route('invoices.store') }}" enctype="multipart/form-data" id="upload-form">
                        @csrf

                        <div class="form-group">
                            <div id="drop-zone" class="border border-dashed rounded p-5 text-center" style="border-width: 2px; cursor: pointer;">
                                <input type="file" name="invoice_files[]" id="invoice_files" class="d-none" accept=".jpg,.jpeg,.png,.pdf" multiple>
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2">
                                    <strong class="text-primary">Click to upload</strong> or drag and drop
                                </p>
                                <p class="text-muted small">JPG, PNG, or PDF up to 10MB each</p>
                                <p class="text-primary small">You can select multiple files</p>
                            </div>

                            @error('invoice_files')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                            @error('invoice_files.*')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- File preview list -->
                        <div id="file-list" class="d-none mb-4">
                            <label class="font-weight-bold">Selected Files</label>
                            <div id="file-items"></div>
                        </div>

                        <!-- Processing indicator -->
                        <div id="processing" class="d-none mb-4">
                            <div class="alert alert-info">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                <strong>Processing invoices with AI...</strong>
                                <p class="mb-0 small mt-2">This may take a moment depending on the number of files</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('invoices.index') }}" class="btn btn-default">
                                Cancel
                            </a>
                            <button type="submit" id="submit-btn" class="btn btn-primary" disabled>
                                <i class="fas fa-magic mr-2"></i> Parse Invoices
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Features -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">AI-Powered</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Advanced AI extracts data with high accuracy from any invoice format</p>
                </div>
            </div>

            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Structured Data</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Get organized line items, totals, and party information instantly</p>
                </div>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">API Access</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Integrate with your apps using our REST API</p>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('invoice_files');
    const fileList = document.getElementById('file-list');
    const fileItems = document.getElementById('file-items');
    const submitBtn = document.getElementById('submit-btn');
    const form = document.getElementById('upload-form');
    const processing = document.getElementById('processing');

    // Click to upload
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'bg-light');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-primary', 'bg-light');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-light');
        fileInput.files = e.dataTransfer.files;
        updateFileList();
    });

    // File input change
    fileInput.addEventListener('change', updateFileList);

    function updateFileList() {
        fileItems.innerHTML = '';
        const files = fileInput.files;

        if (files.length > 0) {
            fileList.classList.remove('d-none');
            submitBtn.disabled = false;

            Array.from(files).forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'd-flex justify-content-between align-items-center p-2 bg-light rounded mb-2';
                item.innerHTML = `
                    <div>
                        <i class="fas fa-file mr-2 text-muted"></i>
                        <span class="small">${file.name}</span>
                    </div>
                    <span class="badge badge-secondary">${formatFileSize(file.size)}</span>
                `;
                fileItems.appendChild(item);
            });
        } else {
            fileList.classList.add('d-none');
            submitBtn.disabled = true;
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Show processing indicator on submit
    form.addEventListener('submit', () => {
        processing.classList.remove('d-none');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
    });
</script>
@stop
