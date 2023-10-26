<html>
<body style="margin: unset!important;">
<div id="map" style="height: 1200px; width: 1100px; position: absolute" class="map"></div>
<script>
    let map;
    let markers = @json($resultArray);
    // Create the script tag, set the appropriate attributes
    let script = document.createElement('script');
    script.src = 'https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_MAPS_API_KEY', null)}}&callback=initMap';
    script.async = true;

    // Attach your callback function to the `window` object
    window.initMap = function() {
        map = new google.maps.Map(document.getElementById("map"), {
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        let bounds = new google.maps.LatLngBounds();

        markers.forEach(function (zipCodeObject) {
            let markerLatLng = {
                lat: parseFloat(zipCodeObject.lat),
                lng: parseFloat(zipCodeObject.lng),
            }

            let lsaRankString = String(zipCodeObject.lsa_rank);
            let color = zipCodeObject.color;
            let icon = generateIcon(color);

            let marker = new google.maps.Marker({
                position: markerLatLng,
                map,
                optimized: false,
                icon: icon,
                label: {color: '#000', fontSize: '18px', fontWeight: '600', text: lsaRankString}
            });

            bounds.extend(marker.position)
        });

        map.fitBounds(bounds);
        map.zooom = map.zooom - 0.5;
    };

    // Append the 'script' element to 'head'
    document.head.appendChild(script);

    function generateIcon(color) {
        return {
            path: google.maps.SymbolPath.CIRCLE,
            fillOpacity: 0.5,
            fillColor: color,
            strokeOpacity: 1,
            strokeWeight: 1,
            strokeColor: '#333',
            scale: 20
        };
    }
</script>
</body>
</html>

