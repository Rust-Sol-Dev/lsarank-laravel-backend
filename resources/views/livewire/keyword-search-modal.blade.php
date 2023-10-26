<div>
    <div class="container">
        <div class="input-group mb-4">
            <div class="search-bar">
                <div class="position-relative">
                    <input wire:model="keyword" type="text" class="form-control keyword-search-modal" placeholder="+ add new keyword">
                    <span style="margin-left: 250px"><input wire:model="location" type="text" class="form-control locationInput" size="15" maxlength="15" placeholder="location"></span>
                </div>
            </div>
            <button wire:click="keywordSearch" style="margin-left: 180px !important; font-size: large"><i class="fe-search"></i></button>
        </div>
    </div>
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show z-50" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('error') }}
        </div>
    @endif
    <div wire:loading wire:target="keywordSearch" >
        <div class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gray-500 opacity-75 flex flex-col items-center justify-center">
            <div class="flex justify-center items-center">
                <div
                    class="animate-spin rounded-full h-32 w-32 border-b-2 border-white-900"
                ></div>
            </div>
        </div>
    </div>
</div>
