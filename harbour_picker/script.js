var map;
var currentMarker;
var selectedHarbourIds = [];
var selectedMarkers = [];

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

    //get all stored harbours
    $.post("handler.php", {
        request: "get"
    }, function (result, status) {
        if (status == "success") {
            displayStoredHarbours(JSON.parse(result));
        } else if (status == "timeout" || status == "error") {
            console.log("error");
        }
    });
}

function updateMarkerLocation(latLng, title) {
    if (currentMarker == undefined) {
        currentMarker = new google.maps.Marker({
            position: latLng,
            icon: {
                url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
            },
            map: map
        });
    } else {
        currentMarker.setPosition(latLng);
    }
}

function displayStoredHarbours(list) {
    for (var i = 0; i < list.length; i++) {
        var harbour = list[i];
        var tempMarker = new google.maps.Marker({
            position: { lat: parseFloat(harbour["lat"]), lng: parseFloat(harbour["lng"]) },
            title: harbour["name"],
            icon: {
                url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
            },
            harbour_id: parseInt(harbour["harbour_id"]),
            map: map
        });
        tempMarker.addListener("click", function () {
            if (selectedHarbourIds.includes(this.harbour_id)) {
                selectedHarbourIds = selectedHarbourIds.filter(id => id != this.harbour_id);
                selectedMarkers = selectedMarkers.filter(marker => marker.harbour_id != this.harbour_id);
                this.setIcon({ url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png" });
            } else {
                selectedHarbourIds.push(this.harbour_id);
                selectedMarkers.push(this);
                this.setIcon({ url: "http://maps.google.com/mapfiles/ms/icons/green-dot.png" });
            }
        });
    }
}

function sendHarbours(list, sender) {
    var json = {};
    json.user_id = $('#add-form input[name="user_id"]').val();
    json.list = list;
    $.post("handler.php", {
        request: "add",
        data: json
    }, function (result, status) {
        if (status == "success") {
            var ids = JSON.parse("{list: " + result) + "}";
            ids = ids.list;
            if (sender == "form") {
                currentMarker.setTitle($('#add-form input[name="name"]').val());
                currentMarker.setIcon({ url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png" });
                currentMarker.harbour_id = 0;//TODO
                currentMarker = null;
                $('#add-form')[0].reset();
            } else if (sender == "csv") {
                list.forEach(point => {
                    var tempMarker = new google.maps.Marker({
                        position: { lat: parseFloat(point.lat), lng: parseFloat(point.lng) },
                        title: point.name,
                        icon: {
                            url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                        },
                        harbour_id: parseInt(harbour["harbour_id"]),
                        map: map
                    });
                    tempMarker.addListener("click", function () {
                        if (selectedHarbourIds.includes(this.harbour_id)) {
                            selectedHarbourIds = selectedHarbourIds.filter(id => id != this.harbour_id);
                            selectedMarkers = selectedMarkers.filter(marker => marker.harbour_id != this.harbour_id);
                            this.setIcon({ url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png" });
                        } else {
                            selectedHarbourIds.push(this.harbour_id);
                            selectedMarkers.push(this);
                            this.setIcon({ url: "http://maps.google.com/mapfiles/ms/icons/green-dot.png" });
                        }
                    });
                });
            }
            console.log(ids);
        } else if (status == "timeout" || status == "error") {
            console.log("error");
        }
    });
}



function csvImporterSetup() {
    var fileInput = document.getElementById("file-input");
    var fileReader = new FileReader();

    fileReader.addEventListener("loadstart", function () {
        var fileName = fileInput.files[0].name;
        var names = fileName.split(".");
        var name = names.splice(0, names.length - 1).join(".");
        document.getElementById("file-name-display").value = "importing " + name + "...";
    });

    fileReader.addEventListener("load", function () {
        var list = [];
        var fileContent = $.csv.toArrays(fileReader.result);
        fileContent.forEach(row => {
            var content = row[0].split(';');
            if (content[0] != "Latitude") {
                var obj = {};
                obj.lat = content[0];
                obj.lng = content[1];
                obj.name = content[2];
                list.push(obj);
            }
        });
        sendHarbours(list, "csv");
    });

    fileInput.addEventListener("change", function () {
        fileReader.readAsText(this.files[0]);
    });
}

$(document).ready(function () {
    $('#add-form').submit(function (event) {
        var harbour = [{
            name: $('#add-form input[name="name"]').val(),
            lat: $('#add-form input[name="lat"]').val(),
            lng: $('#add-form input[name="lng"]').val()
        }];
        sendHarbours(harbour, "form");
        event.preventDefault();
    });

    $('#remove').click(function () {
        var confirmString = "Do you really want to remove";
        for (var i = 0; i < selectedMarkers.length; i++) {
            confirmString += ("\n - " + selectedMarkers[i].title);
        }
        if (confirm(confirmString)) {
            $.post("handler.php", {
                request: "remove",
                list: { list: JSON.stringify(selectedHarbourIds) }
            }, function (result, status) {
                if (status == "success") {
                    for (var i = 0; i < selectedMarkers.length; i++) {
                        selectedMarkers[i].setVisible(false);
                    }
                    selectedHarbourIds = [];
                    selectedMarkers = [];
                } else if (status == "timeout" || status == "error") {
                    console.log("error");
                }
            });
        }
    });

    csvImporterSetup();
});