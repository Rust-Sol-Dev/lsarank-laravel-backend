<div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let timezone = moment.tz.guess();

            if (timezone) {
                Livewire.emit('timeZoneDetected', timezone)

            }
        });
    </script>
</div>
