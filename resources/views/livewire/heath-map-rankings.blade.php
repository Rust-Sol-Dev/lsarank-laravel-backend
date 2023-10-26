<div>
    @if(!$show && $enabled)
        <div wire:poll.10000ms="$refresh" class="map">
            <div class="w-full mapSpinnerHeight z-50 overflow-hidden bg-gray-500 opacity-75 flex flex-col items-center justify-center spinner-relative">
                <div class="flex justify-center items-center center-inner">
                    <div
                        class="animate-spin rounded-full h-32 w-32 border-b-2 border-white-900"
                    ></div>
                </div>
            </div>
        </div>
    @endif
    @if($show)
        <div wire:ignore:self id="map" class="map"/>
        <script>
            var map;
            var markers = [];
            var markerTrack = [];
            let booted = false;
            let route = "{{ route('sync.radius') }}";
            let keywordId = "{{$keywordId}}";
            let googleId = "{{$googleId}}";
            let currentDate = "{{$currentDate}}";
            let lat = {{$lat}};
            let lng = {{$lng}};

            // Create the script tag, set the appropriate attributes
            var script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_MAPS_API_KEY', null)}}&callback=initMap';
            script.async = true;

            let seconds = 12000;
            let lastCount;

            setInterval(function () {
                let time = new Date().getTime();
                let random = Math.random();
                let result = time / random;

                if (booted) {
                    fetch(route+"?keyword_id=" + keywordId + "&date=" + currentDate + "&googleId=" + googleId + "&queryId=" + result + "&seconds=" + seconds, {
                        headers: {
                            'Content-Type': 'application/json;charset=utf-8',
                            'Cache-Control': 'no-store, no-cache, must-revalidate',
                            'Pragma': 'no-cache',
                            'Expires': '0'
                        },
                        cache: "no-store"
                    }).then(res => res.json())
                        .then((zipcodeRankings) => {
                            if (Array.isArray(zipcodeRankings)) {
                                lastCount = zipcodeRankings.length

                                let currentCount = zipcodeRankings.length;

                                if (lastCount == currentCount) {
                                    seconds = seconds + 1000;
                                }

                                updateTheMarkers(zipcodeRankings);
                            }
                        }).catch((error) => {
                        console.log('Error');
                        console.log(error);
                    });
                }
            }, seconds);


            function updateTheMarkers(zipcodeRankings)
            {
                let zipcodeRankingsLength = zipcodeRankings.length;
                let markersLength = markers.length;

                if ((zipcodeRankingsLength == markersLength) && (markersLength > 0)) {
                    return true;
                }

                if (markerTrack.length === 0) {
                    setMarkers(zipcodeRankings);
                    return true;
                }

                let newZipcodeRankings = [];

                for (let i = 0; i < zipcodeRankings.length; i++){
                    let rankingObject = zipcodeRankings[i]
                    let coordinateKey = rankingObject.lat + '+' + rankingObject.lng;

                    if (!markerTrack.hasOwnProperty(coordinateKey)) {
                        newZipcodeRankings.push(rankingObject);
                    }
                }

                if (newZipcodeRankings.length == 0) {
                    return true;
                }

                setMarkers(newZipcodeRankings);
            }

            function setMarkers(zipcodeRankings)
            {
                zipcodeRankings.forEach(function (zipCodeObject) {
                    let markerLatLng = {
                        lat: parseFloat(zipCodeObject.lat),
                        lng: parseFloat(zipCodeObject.lng),
                    }

                    let lsaRankString = String(zipCodeObject.lsa_rank);
                    let color = zipCodeObject.color;

                    let icon = generateIcon(color);

                    let content = zipCodeObject.zipcode + '<br>' + zipCodeObject.place_name + ', ' + zipCodeObject.state

                    let infowindow = new google.maps.InfoWindow({
                        content: content
                    });

                    let marker = new google.maps.Marker({
                        position: markerLatLng,
                        map,
                        optimized: false,
                        icon: icon,
                        label: {color: '#000', fontSize: '18px', fontWeight: '600', text: lsaRankString}
                    });

                    marker.addListener('click', function () {
                        infowindow.open(map, marker);
                    });

                    markers.push(marker);

                    let coordinateKey = zipCodeObject.lat + '+' + zipCodeObject.lng;
                    markerTrack[coordinateKey] = true;
                });
            }


            // Attach your callback function to the `window` object
            window.initMap = function() {
                map = new google.maps.Map(document.getElementById("map"), {
                    center: { lat: lat, lng: lng },
                    zoom: 10,
                });

                booted = true;
            };

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
            // Append the 'script' element to 'head'
            document.head.appendChild(script);
        </script>
    @endif
</div>
