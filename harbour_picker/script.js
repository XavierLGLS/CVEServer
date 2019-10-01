var map;

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 35.6, lng: -40.9 },
        zoom: 3
    });
    map.addListener('dblclick', function (e) {
        document.querySelector('input[name="lat"]').value = e.latLng.lat();
        document.querySelector('input[name="lng"]').value = e.latLng.lng();
    });
}