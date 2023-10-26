<div>
    <div class="card">
        <div class="card-body">
            <h4 class="header-title">Upload logo</h4>
            <p class="sub-header">
                Upload your company's logo for reporting
            </p>
            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <form x-data="{ file: null }" @click="$refs.fileInput.click()" wire:submit.prevent="save" class="dropzone" id="dropzone" data-plugin="dropzone" data-previews-container="#file-previews" data-upload-preview-template="#uploadPreviewTemplate">
                <input type="file" x-ref="fileInput" class="hidden" wire:model="image">
                <div class="dz-message needsclick">
                    @if ($uploaded || $image)
                        <div class="dropzone-previews mt-3" id="file-previews"><img src="{{ $image ? $image->temporaryUrl() : $oldImage }}"></div>
                    @else
                        <i class="h1 text-muted dripicons-cloud-upload"></i>
                        <h3>Drop files here or click to upload.</h3>
                        <span id="filename" class="text-muted font-13"></span>
                    @endif
                </div>

            </form>
            @if ($image)
                <div>
                    <button id="submit" wire:click="save()" class="z-50 btn btn-success rounded-pill waves-effect waves-light">
                        <span class="z-50 btn-label"><i class="z-50 mdi mdi-check-all"></i></span>Upload
                    </button>
                </div>
            @endif


        </div> <!-- end card-body-->
    </div> <!-- end card-->
</div>

