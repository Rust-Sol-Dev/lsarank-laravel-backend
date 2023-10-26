<div>
    @php
        $names2 = $data['names'];
        $values2 = $data['values'];
    @endphp
    @if ($show)
        <livewire:polar-chart key="{{ now() }}" :names="$names" :values="$values" :keywordName="$keywordName"/>
    @endif
</div>
