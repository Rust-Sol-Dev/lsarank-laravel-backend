<div>
    @if($show)
        <div class="card">
            <div class="card-body">
                <div>
                    <select wire:model="selectedMap" class="form-select">
                        <option selected="">-</option>
                        @foreach($entityDropdownOption as $heatMapId => $businessEntityName)
                            <option value="{{ $heatMapId }}">{{ $businessEntityName }}</option>
                        @endforeach
                    </select>
                    <br>
                    <br>
                    <button wire:click="generateReport()" class="btn btn-primary waves-effect waves-light mb-2" @if(!$selectedMap) disabled @else @endif>
                        Generate report
                    </button>
                </div>
                @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>
        <div wire:loading>
            <div class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gray-500 opacity-75 flex flex-col items-center justify-center">
                <div class="flex justify-center items-center">
                    <div
                        class="animate-spin rounded-full h-32 w-32 border-b-2 border-white-900"
                    ></div>
                </div>
            </div>
        </div>
    @endif
</div>

