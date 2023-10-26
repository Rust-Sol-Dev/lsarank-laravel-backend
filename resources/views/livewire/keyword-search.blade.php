<div>
    <div class="container">
        <div class="input-group">
            @if(!$csv)
                <div class="search-bar p-3">
                    <div class="position-relative">
                        <input wire:model="keyword" type="text" class="form-control keyword-search" placeholder="+ add new keyword">
                    </div>
                </div>
                <div class="p-3">
                    <span><input wire:model="location" type="text" class="form-control locationInput" size="15" maxlength="15" placeholder="location"></span>
                </div>

                <button wire:click="keywordSearch" style="font-size: large"><i class="fe-search"></i></button>
            @else
                <div class="mb-3">
                    <form wire:submit.prevent="save">
                        <div class="flex" style="margin-top: 15px">
                            <label for="example-fileinput">Upload CSV file <a href="{{ route('keyword.download.list')  }}">List sample</a></label>
                                <input wire:model="csvFile" type="file" accept=".csv" id="csvUpload" class="form-control"> <span style="margin-left: 15px"><button class="btn btn-primary" type="submit" @if(!$csvFile) disabled @endif>Upload</button></span>
                        </div>
                    </form>
                </div>
            @endif
            <div class="form-check" style="margin-left: 20px!important; margin-top: 2rem!important;">
                <input  wire:model="csv" type="checkbox" class="form-check-input">
                <label class="form-check-label" for="customCheck1">Add keyword using CSV</label>
            </div>
        </div>
    </div>
    @if(session()->has('info'))
        <script>
            document.getElementById("csvUpload").value = "";
            setTimeout(() => {
                document.getElementById("successMessage").remove();
            }, 5000)
        </script>

        <div class="alert alert-success alert-dismissible fade show z-50" id="successMessage">
            {{ session()->get('info') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible show z-50" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('error') }}
        </div>
    @endif
    <div wire:loading wire:target="keywordSearch, save" >
        <div class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gray-500 opacity-75 flex flex-col items-center justify-center">
            <div class="flex justify-center items-center">
                <div
                    class="animate-spin rounded-full h-32 w-32 border-b-2 border-white-900"
                ></div>
            </div>
        </div>
    </div>
</div>
