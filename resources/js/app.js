import './bootstrap';

document.addEventListener('livewire:load', function () {
    if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
        Livewire.all().map((component) => {
            component.call('render');
        });
    }
})

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import GaugeChart from 'gauge-chart';

window.GaugeChart = GaugeChart;

window.Alpine = Alpine;

Alpine.plugin(focus);

Alpine.start();

import.meta.glob([
    '../fonts/**',
    '../images/**',
]);
