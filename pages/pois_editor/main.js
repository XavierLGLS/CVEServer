var map;
var clusterGroup;
var locationChangingId = null;
var areaCircle = null;

$().ready(function () {
    //init the map
    map = L.map('map', {
        doubleClickZoom: false
    }).setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?{foo}', {
        foo: 'bar',
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
    }).addTo(map);
    $(".footer").css("position", "relative");

    var legend = L.control({
        position: 'topright'
    });
    legend.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'legend');
        div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_harbour_icon.png" height="20" width="20"> harbour</div>';
        div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_anchorage_icon.png" height="20" width="20"> anchorage</div>';
        div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_oddity_icon.png" height="20" width="20"> oddity</div>';
        div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_waypoint_icon.png" height="20" width="20"> waypoint</div>';
        return div;
    }
    legend.addTo(map);

    //init poi creation menu
    map.on('dblclick', function (e) {
        if (locationChangingId === null) {
            L.popup()
                .setContent(getPoiCreatorPopup(e.latlng))
                .setLatLng(e.latlng)
                .openOn(map);

            //set menu depending on type selected
            $("#type-selector").change(function () {
                $(".type-section").addClass("hidden");
                switch (parseInt($(this).val())) {
                    case 0: // harbour
                        $("#harbour-section").removeClass("hidden");
                        break;
                    case 1: // anchorage
                        $("#anchorage-section").removeClass("hidden");
                        break;
                    case 2: // oddity
                        $("#oddity-section").removeClass("hidden");
                        break;
                    default:
                        alert("unrecognized poi type (" + $(this).val() + ")");
                }
            });

            //set request sender
            $("#poi-add-form").submit(function (e) {
                e.preventDefault();
                var lat = $(this).find("#lat-input").val();
                var lng = $(this).find("#lng-input").val();
                var name = $(this).find("#name-input").val();
                var caption = $(this).find("#caption-input").val();
                $.post("../../backend/rest/pois.php", {
                    action: "add-poi",
                    "app-token": getAppToken(),
                    "lat": lat,
                    "lng": lng,
                    "type": $(this).find("#type-selector option:selected").val(),
                    "name": encodeURI(name),
                    "creator-id": getUserId(),
                    "water": ($(this).find("#water").is(":checked") ? 1 : 0),
                    "food": ($(this).find("#food").is(":checked") ? 1 : 0),
                    "spare-parts": ($(this).find("#spare-parts").is(":checked") ? 1 : 0),
                    "dry-dock": ($(this).find("#dry-dock").is(":checked") ? 1 : 0),
                    "caption": encodeURI(caption)
                }, function (result, status) {
                    if (result.success) {
                        displayPoi(result.poi);
                        map.closePopup();
                    } else {
                        alert(result.message);
                    }
                });
            });
        } else {
            $.post("../../backend/rest/pois.php", {
                action: "edit-poi-location",
                "app-token": getAppToken(),
                "poi-id": locationChangingId,
                "creator-id": getUserId(),
                lat: e.latlng.lat,
                lng: e.latlng.lng
            }, function (result, status) {
                if (result.success) {
                    locationChangingId = null;
                    displayPoi(result.poi);
                } else {
                    alert(result.message);
                }
            });
        }
    });

    $(".delete-button").click(function () {
        alert("delete")
    });

    map.spin(true);
    //get last pois
    $.post("../../backend/rest/pois.php", {
        action: "get-pois",
        "app-token": getAppToken()
    }, function (result, status) {
        if (result.success) {
            displayPois(result.pois);
            map.spin(false);
        } else {
            alert(result.message);
        }
    });
});

function displayPois(pois) {
    if (clusterGroup) {
        map.removeLayer(clusterGroup);
    }
    clusterGroup = L.markerClusterGroup();
    //harbours
    if (pois) {
        for (let i = 0; i < pois.length; i++) {
            let poi = pois[i];
            var marker = new L.Marker([poi.lat, poi.lng], {
                title: decodeURI(poi.name)
            });
            marker._id = poi.id;
            let popupContent, iconPath;
            switch (poi.type) {
                case 0: // harbour
                    iconPath = "../../assets/poi_harbour_icon.png";
                    popupContent = getHarbourPopup(poi);
                    break;
                case 1: // anchorage
                    iconPath = "../../assets/poi_anchorage_icon.png";
                    popupContent = getAnchoragePopup(poi);
                    break;
                case 2: // oddity
                    iconPath = "../../assets/poi_oddity_icon.png";
                    popupContent = getOddityPopup(poi);
                    break;
                default:
                    alert("unrecognized poi type");
            }
            marker.setIcon(L.icon({
                iconUrl: iconPath,
                iconSize: [30, 30]
            }));
            var popup = L.popup({
                maxWidth: 500
            }).setContent(popupContent);
            marker.bindPopup(popup);
            marker
                .on('mouseover', function (event) {
                    if (areaCircle) {
                        map.removeLayer(areaCircle);
                    }
                    areaCircle = L.circle([poi.lat, poi.lng], {
                        color: 'blue',
                        weight: 0,
                        fillColor: '#4287f5',
                        fillOpacity: 0.5,
                        radius: getDetectionRadiusKm() * 1000
                    }).addTo(map);
                })
                .on('mouseout', function () {
                    map.removeLayer(areaCircle);
                    areaCircle = null;
                })
                .on('popupopen', function (popupopenEvent) {
                    //deletion request bind
                    $(".delete-button").click(function () {
                        $.post("../../backend/rest/pois.php", {
                            action: "delete-poi",
                            "app-token": getAppToken(),
                            "poi-id": $(this).parent().find('input[name="poi-id"]').val()
                        }, function (result, status) {
                            if (result.success) {
                                clusterGroup.removeLayer(popupopenEvent.popup._source);
                                map.closePopup();
                            } else {
                                alert(result.message);
                            }
                        });
                    });
                    //update request bind
                    $(".update-button").click(function () {
                        let caption = $(this).parent().find("#caption").val();
                        $.post("../../backend/rest/pois.php", {
                            action: "edit-poi-content",
                            "app-token": getAppToken(),
                            "poi-id": $(this).parent().find('input[name="poi-id"]').val(),
                            "editor-id": getUserId(),
                            name: encodeURI($(this).parent().find('#name').val()),
                            type: $(this).parent().find('input[name="poi-type"]').val(),
                            water: ($(this).parent().find("#checkbox-container").find('input[value="water"]').prop("checked") ? 1 : 0),
                            food: ($(this).parent().find("#checkbox-container").find('input[value="food"]').prop("checked") ? 1 : 0),
                            "spare-parts": ($(this).parent().find("#checkbox-container").find('input[value="spare-parts"]').prop("checked") ? 1 : 0),
                            "dry-dock": ($(this).parent().find("#checkbox-container").find('input[value="dry-dock"]').prop("checked") ? 1 : 0),
                            caption: encodeURI((caption === undefined ? "" : caption))
                        }, function (result, status) {
                            if (result.success) {
                                clusterGroup.removeLayer(popupopenEvent.popup._source);
                                displayPoi(result.poi);
                                map.closePopup();
                            } else {
                                alert(result.message);
                            }
                        });
                    });
                    //new location request bind
                    $(".new-location-button").click(function () {
                        locationChangingId = $(this).parent().find('input[name="poi-id"]').val();
                        map.closePopup();
                        clusterGroup.removeLayer(popupopenEvent.popup._source);
                        alert("double click on the map where you want to set the new location");
                    });
                });
            clusterGroup.addLayer(marker);
        }
    }
    map.addLayer(clusterGroup);
}

function displayPoi(poi) {
    var marker = new L.Marker([poi.lat, poi.lng], {
        title: decodeURI(poi.name)
    });
    marker._id = poi.id;
    let popupContent;
    switch (poi.type) {
        case 0: //harbour
            marker.setIcon(L.icon({
                iconUrl: "../../assets/poi_harbour_icon.png",
                iconSize: [30, 30]
            }));
            popupContent = getHarbourPopup(poi);
            break;
        case 1: //anchorage
            marker.setIcon(L.icon({
                iconUrl: "../../assets/poi_anchorage_icon.png",
                iconSize: [30, 30]
            }));
            popupContent = getAnchoragePopup(poi);
            break;
        case 2: //oddity
            marker.setIcon(L.icon({
                iconUrl: "../../assets/poi_oddity_icon.png",
                iconSize: [30, 30]
            }));
            popupContent = getOddityPopup(poi);
            break;
        default:
            alert("unrecognized poi type");
    }
    var popup = L.popup({
        maxWidth: 500
    }).setContent(popupContent);
    marker.bindPopup(popup);
    marker
        .on('mouseover', function (event) {
            if (areaCircle) {
                map.removeLayer(areaCircle);
            }
            areaCircle = L.circle([poi.lat, poi.lng], {
                color: 'blue',
                weight: 0,
                fillColor: '#4287f5',
                fillOpacity: 0.5,
                radius: getDetectionRadiusKm() * 1000
            }).addTo(map);
        })
        .on('mouseout', function () {
            map.removeLayer(areaCircle);
            areaCircle = null;
        })
        .on('popupopen', function (popupopenEvent) {
            //deletion request bind
            $(".delete-button").click(function () {
                $.post("../../backend/rest/pois.php", {
                    action: "delete-poi",
                    "app-token": getAppToken(),
                    "poi-id": $(this).parent().find('input[name="poi-id"]').val()
                }, function (result, status) {
                    if (result.success) {
                        clusterGroup.removeLayer(popupopenEvent.popup._source);
                        map.closePopup();
                    } else {
                        alert(result.message);
                    }
                });
            });
            //update request bind
            $(".update-button").click(function () {
                let caption = $(this).parent().find("#caption").val();
                $.post("../../backend/rest/pois.php", {
                    action: "edit-poi-content",
                    "app-token": getAppToken(),
                    "poi-id": $(this).parent().find('input[name="poi-id"]').val(),
                    "editor-id": getUserId(),
                    name: encodeURI($(this).parent().find('#name').val()),
                    type: $(this).parent().find('input[name="poi-type"]').val(),
                    water: ($(this).parent().find("#checkbox-container").find('input[value="water"]').prop("checked") ? 1 : 0),
                    food: ($(this).parent().find("#checkbox-container").find('input[value="food"]').prop("checked") ? 1 : 0),
                    "spare-parts": ($(this).parent().find("#checkbox-container").find('input[value="spare-parts"]').prop("checked") ? 1 : 0),
                    "dry-dock": ($(this).parent().find("#checkbox-container").find('input[value="dry-dock"]').prop("checked") ? 1 : 0),
                    caption: encodeURI((caption === undefined ? "" : caption))
                }, function (result, status) {
                    if (result.success) {
                        clusterGroup.removeLayer(popupopenEvent.popup._source);
                        displayPoi(result.poi);
                        map.closePopup();
                    } else {
                        alert(result.message);
                    }
                });
            });
            //new location request bind
            $(".new-location-button").click(function () {
                locationChangingId = $(this).parent().find('input[name="poi-id"]').val();
                map.closePopup();
                clusterGroup.removeLayer(popupopenEvent.popup._source);
                alert("double click on the map where you want to set the new location");
            });
        });
    clusterGroup.addLayer(marker);
}

function getHarbourPopup(poi) {
    var output = '<h5><strong>Modification of the harbour</strong></h5>';
    output += '<input type="hidden" name="poi-id" value="' + poi.id + '">';
    output += '<input type="hidden" name="poi-type" value="' + poi.type + '">';
    output += '<label for="name"><strong>Name:</strong></label><input type="text" class="form-control form-control-sm" name="name" id="name" value="' + decodeURI(poi.name) + '" required>';
    output += '<div id="checkbox-container" style="margin: 10px 0;">';
    output += '<div id="water-container" class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="water" ' + (poi.provides_water ? 'checked' : '') + '>Water</label></div>';
    output += '<div id="food-container" class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="food" ' + (poi.sells_food ? 'checked' : '') + '>Food</label></div>';
    output += '<div id="spare-parts-container" class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="spare-parts" ' + (poi.sells_spare_parts ? 'checked' : '') + '>Spare parts</label></div>';
    output += '<div id="dry-dock-container" class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" value="dry-dock" ' + (poi.dry_dock ? 'checked' : '') + '>Dry dock</label></div>';
    output += '</div>';
    output += '<button type="button" class="btn btn-warning btn-sm update-button" style="margin: 2px;">Update</button>';
    output += '<button type="button" class="btn btn-primary btn-sm new-location-button" style="margin: 2px;">New location</button>';
    output += '<button type="button" class="btn btn-danger btn-sm delete-button" style="margin: 2px;">Delete</button>';
    if (poi.creator != "") {
        output += `<p><strong>Last modifier:</strong> ${decodeURI(poi.creator)}</p>`;
    }
    return output;
}

function getAnchoragePopup(poi) {
    var output = '<h5><strong>Modification of the anchorage</strong></h5>';
    output += '<input type="hidden" name="poi-id" value="' + poi.id + '">';
    output += '<input type="hidden" name="poi-type" value="' + poi.type + '">';
    output += '<label for="name"><strong>Name:</strong></label><input type="text" style="margin-bottom: 10px;" class="form-control form-control-sm" name="name" id="name" value="' + decodeURI(poi.name) + '" required>';
    output += '<button type="button" class="btn btn-warning btn-sm update-button" style="margin: 2px;">Update</button>';
    output += '<button type="button" class="btn btn-primary btn-sm new-location-button" style="margin: 2px;">New location</button>';
    output += '<button type="button" class="btn btn-danger btn-sm delete-button" style="margin: 2px;">Delete</button>';
    if (poi.creator != "") {
        output += `<p><strong>Last modifier:</strong> ${decodeURI(poi.creator)}</p>`;
    }
    return output;
}

function getOddityPopup(poi) {
    var output = '<h5><strong>Modification of the oddity</strong></h5>';
    output += '<input type="hidden" name="poi-id" value="' + poi.id + '">';
    output += '<input type="hidden" name="poi-type" value="' + poi.type + '">';
    output += '<label for="name"><strong>Name:</strong></label><input type="text" class="form-control form-control-sm" name="name" id="name" value="' + decodeURI(poi.name) + '" required>';
    output += '<div style="margin: 10px 0;"><label for="caption"><strong>Caption:</strong></label><textarea class="form-control form-control-sm" rows="5" id="caption">' + decodeURI(poi.caption) + '</textarea></div>';
    output += '<button type="button" class="btn btn-warning btn-sm update-button" style="margin: 2px;">Update</button>';
    output += '<button type="button" class="btn btn-primary btn-sm new-location-button" style="margin: 2px;">New location</button>';
    output += '<button type="button" class="btn btn-danger btn-sm delete-button" style="margin: 2px;">Delete</button>';
    if (poi.creator != "") {
        output += `<p><strong>Last modifier:</strong> ${decodeURI(poi.creator)}</p>`;
    }
    return output;
}

function getPoiCreatorPopup(location) {
    var output = '<h5><strong>Create a new POI</strong></h5>';
    output += '<form id="poi-add-form">';
    output += '<div class="from-group">';
    output += '<label for="lat-input"><strong>Latitude</strong></label>';
    output += '<input type="number" class="form-control form-control-sm" id="lat-input" step="0.001" value="' + location.lat.toFixed(3) + '" required>';
    output += '<label for="lng-input"><strong>Longitude</strong></label>';
    output += '<input type="number" class="form-control form-control-sm" id="lng-input" step="0.001" value="' + location.lng.toFixed(3) + '" required>';
    output += '<label for="name"><strong>Name</strong></label><input type="text" class="form-control form-control-sm" id="name-input" required>';
    output += '</div>';
    output += '<div class="from-group">';
    output += '<label for="type-selector"><strong>Type</strong></label><select class="form-control form-control-sm" id="type-selector"><option value="0" selected>Harbour</option><option value="1">Anchorage</option><option value="2">Oddity</option></select>';
    output += '</div>';
    output += '<div id="harbour-section" class="type-section form-group" style="margin: 10px 0;">';
    output += '<div class="form-check"><input class="form-check-input" type="checkbox" value="" id="water"><label class="form-check-label" for="water">Water</label></div>';
    output += '<div class="form-check"><input class="form-check-input" type="checkbox" value="" id="food"><label class="form-check-label" for="food">Food</label></div>';
    output += '<div class="form-check"><input class="form-check-input" type="checkbox" value="" id="spare-parts"><label class="form-check-label" for="spare-parts">Spare parts</label></div>';
    output += '<div class="form-check"><input class="form-check-input" type="checkbox" value="" id="dry-dock"><label class="form-check-label" for="dry-dock">Dry dock</label></div>';
    output += '</div>';
    output += '<div id="oddity-section" class="type-section form-group hidden" style="margin: 10px 0;">';
    output += '<label for="caption-input"><strong>Caption</strong></label><textarea class="form-control form-control-sm" id="caption-input" rows="3"></textarea>';
    output += '</div>';
    output += '<div id="anchorage-section" class="type-section form-group hidden" style="margin: 10px 0;"></div>';
    output += '<button class="btn btn-primary btn-block btn-sm" type="submit" id="poi-add">Add this POI</button>';
    output += '</form>';
    return output;
}