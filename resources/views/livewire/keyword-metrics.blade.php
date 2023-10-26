<div>
    <br>
    <br>
    <livewire:paid-status-indicator/>
    <livewire:average-metrics :keyword="$keyword" :currentDate="$date"/>
    <livewire:heath-map-rankings wire:key="{{ $mapKey }}" :keyword="$keyword" :currentDate="$date"/>
    <livewire:keyword-metric-table :keyword="$keyword" :currentDay="$day" :currentDate="$date"/>
    <livewire:keyword-metric-filter :currentDay="$day" :currentDate="$date"/>
    <livewire:keyword-metric-analytics :keyword="$keyword" :currentDate="$date"/>
</div>
