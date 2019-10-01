var map;
var currentMarker;

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 35.6, lng: -40.9 },
        zoom: 3
    });
    map.addListener('dblclick', function (e) {
        document.querySelector('input[name="lat"]').value = e.latLng.lat();
        document.querySelector('input[name="lng"]').value = e.latLng.lng();
        updateMarkerLocation(e.latLng);
    });
    map.setOptions({ disableDoubleClickZoom: true });
}

function updateMarkerLocation(latLng) {
    if (currentMarker == undefined) {
        currentMarker = new google.maps.Marker({
            position: latLng,
            map: map
        });
    } else {
        currentMarker.setPosition(latLng);
    }
}