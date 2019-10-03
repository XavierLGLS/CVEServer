var map;
var currentMarker;

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 35.6, lng: -40.9 },
        zoom: 3
    });
    map.addListener('dblclick', function (e) {
        var inputLat = document.querySelector('input[name="lat"]');
        if (inputLat != null) {
            inputLat.value = Math.round(1000 * e.latLng.lat()) / 1000;
        }
        var inputLng = document.querySelector('input[name="lng"]');
        if (inputLng != null) {
            inputLng.value = Math.round(1000 * e.latLng.lng()) / 1000;
        }
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

$(document).ready(function () {
    $('#add-form').submit(function (event) {
        $.post("handler.php", {
            request: "add",
            name: $('#add-form input[name="name"]').val(),
            lat: $('#add-form input[name="lat"]').val(),
            lng: $('#add-form input[name="lng"]').val()
        }, function (result, status) {
            if (status == "success") {
                $('#add-form')[0].reset();
            } else if (status == "timeout" || status == "error") {
                console.log("error");
            }
        });
        event.preventDefault();
    });
});