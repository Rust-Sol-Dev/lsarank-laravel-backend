<div>
    <div class="row g-0">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div style="display: flex; justify-content: center;" class="col-12 flex">
                        <div style="display: flex; justify-content: center;" class="text-center">
                            <div class="my-auto" style="font-size: 24px; font-weight: 900">Free Data</div>
                            <div id="gaugeArea-{{ $rand }}"></div>
                            <div class="my-auto" style="font-size: 24px; font-weight: 900">Premium Data</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        let paid = {{ $paid }}
        document.addEventListener("DOMContentLoaded", function() {
            let needle;

            if (paid) {
                needle = 95;
            } else {
                needle = 5;
            }

            let element = document.querySelector('#gaugeArea-{{ $rand }}')

            // Properties of the gauge
            let gaugeOptions = {
                hasNeedle: true,
                needleColor: 'gray',
                needleUpdateSpeed: 1000,
                arcColors: ['red', 'green'],
                arcDelimiters: [50],
            }

            // Drawing and updating the chart
            GaugeChart.gaugeChart(element, 170, gaugeOptions).updateNeedle(needle)
        });
    </script>
</div>
