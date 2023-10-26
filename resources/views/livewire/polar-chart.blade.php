<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="polar-chart-{{ $rand }}"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">

        let names = @json($names);
        let values = @json($values);
        let colors = @json($colors);

        (function() {
            new Chart(document.getElementById("polar-chart-{{ $rand }}"), {
                type: 'polarArea',
                data: {
                    labels: names,
                    datasets: [{
                        label: 'LSA ranking',
                        data: values,
                        backgroundColor: colors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'LSA Marketshare Estimation by % for "{{ $keywordName }}"'
                        }
                    }
                }
            });
        })();
    </script>
</div>
