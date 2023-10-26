<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col text-center">
                        <button wire:click="filter('monday')" type="button" class="{{ $this->getButtonClass('monday') }}" {{ $this->getDisabledAttribute('monday') }}>Monday</button>
                    </div>
                    <div class="col text-center">
                        <button wire:click="filter('tuesday')" type="button" class="{{ $this->getButtonClass('tuesday') }}" {{ $this->getDisabledAttribute('tuesday') }}>Tuesday</button>
                    </div>
                    <div class="col text-center">
                        <button wire:click="filter('wednesday')" type="button" class="{{ $this->getButtonClass('wednesday') }}" {{ $this->getDisabledAttribute('wednesday') }}>Wednesday</button>
                    </div>
                    <div class="col text-center">
                        <button wire:click="filter('thursday')" type="button" class="{{ $this->getButtonClass('thursday') }}" {{ $this->getDisabledAttribute('thursday') }}>Thursday</button>
                    </div>
                    <div class="col text-center">
                        <button wire:click="filter('friday')" type="button" class="{{ $this->getButtonClass('friday') }}" {{ $this->getDisabledAttribute('friday') }}>Friday</button>
                    </div>
                    <div class="col text-center">
                        <button wire:click="filter('saturday')" type="button" class="{{ $this->getButtonClass('saturday') }}" {{ $this->getDisabledAttribute('saturday') }}>Saturday</button>
                    </div>
                    <div class="col text-center">
                        <button wire:click="filter('sunday')" type="button" class="{{ $this->getButtonClass('sunday') }}" {{ $this->getDisabledAttribute('sunday') }}>Sunday</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
