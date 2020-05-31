var map, clusterGroup;
var path = [], waypoints = [], arrows = [], placesOfAvailability = [], markers = [];
var startLabel, endLabel;
var pickedStep = null;
var lastStepId = 0;
var poiSelectionEnabled = false;

const transitArrowOption = {
    factor: 0,
    arrowFilled: true,
    opacity: 0.7,
    color: "#FF4136",
    weight: 2,
    labelFontSize: 12,
    iconAnchor: [20, 10],
    iconSize: [20, 16]
};

const beginArrowOption = {
    factor: 0,
    arrowFilled: true,
    opacity: 0.4,
    color: "#0074D9",
    weight: 2,
    labelFontSize: 12,
    iconAnchor: [20, 10],
    iconSize: [20, 16]
};

function addStep() {
    const stepNber = $("#steps-container").children().length;
    const stepId = ++lastStepId;
    let stepStr = '';
    stepStr += '<div class="step-container" id="step-' + stepId + '-container">';
    stepStr += '    <div class="d-flex">';
    stepStr += '        <i class="fas fa-expand-alt toggler" data-toggle="collapse" data-target="#step-' + stepId + '"></i>';
    stepStr += '        <small class="text-info poi-label" style="margin-left: 1em;"></small>';
    stepStr += '        <div class="ml-auto">';
    stepStr += '            <span class="text-info">step </span>';
    stepStr += '            <span class="order text-info">' + stepNber + '</span>';
    stepStr += '            <i class="fas fa-caret-square-up"></i>';
    stepStr += '            <i class="fas fa-caret-square-down"></i>';
    stepStr += '            <i class="fas fa-trash-alt"></i>';
    stepStr += '        </div>';
    stepStr += '    </div>';
    stepStr += '    <div>';
    stepStr += '        <small class="title"></small>';
    stepStr += '    </div>';
    stepStr += '    <div id="step-' + stepId + '" class="collapse show">';
    stepStr += '        <div class="form-inline form-group input-group">';
    stepStr += '            <input type="number" name="lat" class="form-control" step="0.001" placeholder="latitude">';
    stepStr += '            <input type="number" name="lng" class="form-control" step="0.001" placeholder="longitude">';
    stepStr += '            <input type="hidden" name="poi-id" value="">';
    stepStr += '            <div class="icons-container">';
    stepStr += '                <i class="fas fa-crosshairs" title="select a new location or a POI by clicking on the map" onclick="onStepPick(' + stepId + ')"></i>';
    stepStr += '            </div>';
    stepStr += '        </div>';
    stepStr += '        <div class="form-group">';
    stepStr += '            <textarea class="form-control" rows="4" name="step-caption" placeholder="description (optional)"></textarea>';
    stepStr += '        </div>';
    stepStr += '    </div>';
    stepStr += '</div>';

    let newStep = $(stepStr);
    newStep.insertBefore($("#new-step"));
    initStep(newStep);
}

function incrementOrder(step) {
    var span = step.find(".order");
    span.text(parseInt(span.text()) + 1);
}

function decrementOrder(step) {
    var span = step.find(".order");
    span.text(parseInt(span.text()) - 1);
}


$().ready(function () {

    mapInit();

    formInit();

    $(".footer").css("position", "relative");

    // init of the 'new step' button
    $('#new-step').click(function () {
        addStep();
    });

    $('#poi-selection').on('click', function () {
        if (poiSelectionEnabled) {
            poiSelectionEnabled = false;
            $(this).removeClass('fa-toggle-on');
            $(this).addClass('fa-toggle-off');
        } else {
            poiSelectionEnabled = true;
            $(this).removeClass('fa-toggle-off');
            $(this).addClass('fa-toggle-on');
        }
    });
});

function initStep(step) {
    // init expand buttons
    $(step.find('.toggler')).click(function () {
        if ($(this).hasClass("fa-compress-alt")) {
            $(this).removeClass("fa-compress-alt");
            $(this).addClass("fa-expand-alt");
        } else {
            $(this).removeClass("fa-expand-alt");
            $(this).addClass("fa-compress-alt");
        }
    });

    // init the preview title auto fill
    $(step.find('textarea[name="step-caption"]')).change(function () {
        $(this).parent().parent().parent().find('.title').text($(this).val())
    });

    // init of the order change buttons
    $(step.find('.fas.fa-caret-square-up')).on('click', function () {
        let thisStep = $(this).parent().parent().parent();
        let previous = thisStep.prev();
        if (previous.length > 0) {
            thisStep.insertBefore(previous);
            decrementOrder(thisStep);
            incrementOrder(previous);
            updatePath();
        }
    });
    $(step.find('.fas.fa-caret-square-down')).on('click', function () {
        let thisStep = $(this).parent().parent().parent();
        var next = thisStep.next();
        if (next.attr('id') != "new-step") {
            thisStep.insertAfter(next);
            incrementOrder(thisStep);
            decrementOrder(next);
            updatePath();
        }
    });

    // init deletion
    $(step.find('.fas.fa-trash-alt')).on('click', function () {
        let parent = $(this).parent().parent().parent();
        if (confirm("Do you really want to remove this step ?")) {
            parent.nextAll('.step-container').each(function () {
                decrementOrder($(this));
            });
            parent.remove();
            updatePath();
        }
    });
}

function mapInit() {
    // map layout
    map = L.map('map', {
        doubleClickZoom: false
    }).setView([0, 0], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?{foo}', {
        foo: 'bar',
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
    }).addTo(map);
    map
        .on('dblclick', function (event) {
            const lat = event.latlng.lat;
            const lng = event.latlng.lng;
            if (pickedStep !== null) {
                locateStep(lat, lng);
                pickedStep.find('.fas.fa-crosshairs').removeClass('locating');
                pickedStep.find('.poi-label').text('waypoint');
                pickedStep = null;
                updatePath();
            }
        });

    // legend
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

    // get last pois
    $.post("../../backend/rest/pois.php", {
        action: "get-pois",
        "app-token": getAppToken()
    }, function (result, status) {
        if (result.success) {
            displayPois(result.pois);
        } else {
            alert(result.message);
        }
    });
}

function formInit() {
    $('#add-form').submit(function (event) {
        event.preventDefault();
        mission = {};
        mission["places_of_availability"] = placesOfAvailability;
        var errors = [];
        var missionName = $('input[name="mission-name"]').val();
        if (missionName == "" || missionName === undefined) {
            errors.push("You must define a mission name");
        } else {
            mission["name"] = encodeURI(missionName);
        }
        var caption = $('textarea[name="mission-caption"]').val();
        if (caption != "" && caption !== undefined) {
            mission["caption"] = encodeURI(caption);
        }
        var passengers = parseInt($('input[name="mission-passengers"]').val());
        if (!isNaN(passengers)) {
            mission["passengers"] = passengers;
        } else {
            errors.push("You must set a passengers number (skipper only => passengers = 0)");
        }
        mission["steps"] = [];
        $('.step-container').each(function () {
            var step = {};
            var poiId = $(this).find('input[name="poi-id"]').val();
            var lat = $(this).find('input[name="lat"]').val();
            var lng = $(this).find('input[name="lng"]').val();
            if (poiId != "" && poiId !== undefined) {
                step["poi_id"] = parseInt(poiId);
            } else if (lat != "" && lng != "") {
                step["lat"] = parseFloat(lat);
                step["lng"] = parseFloat(lng);
            } else {
                errors.push("You must define a step location");
            }
            var caption = $(this).find('textarea[name="step-caption"]').val();
            if (caption != "" && caption !== undefined) {
                step["caption"] = encodeURI(caption);
            }
            mission["steps"].push(step);
        });
        if (mission["steps"].length < 1) {
            errors.push("You must define at least one step");
        }
        if (mission["places_of_availability"].length < 1) {
            errors.push("You must define at least one place of availability");
        }
        if (errors.length > 0) {
            alert(errors.join("\n"));
            return;
        }
        $.post("../../backend/rest/missions.php", {
            action: "add-mission",
            "app-token": getAppToken(),
            "user-id": getUserId(),
            "mission": JSON.stringify(mission)
        }, function (result, status) {
            if (result.success) {
                for (let i = waypoints.length - 1; i >= 0; i--) {
                    const waypoint = waypoints[i];
                    clusterGroup.removeLayer(waypoint);
                }
                $('.step-container').remove();
                $('input[name="mission-name"]').val("");
                $('textarea[name="mission-caption"]').val("");
                pickedStep = null;
                waypoints = [];
                markers.forEach(marker => {
                    let iconPath;
                    switch (marker._type) {
                        case 0: // harbour
                            iconPath = "../../assets/poi_harbour_icon.png";
                            break;
                        case 1: // anchorage
                            iconPath = "../../assets/poi_anchorage_icon.png";
                            break;
                        case 2: // oddity
                            iconPath = "../../assets/poi_oddity_icon.png";
                            break;
                        default:
                            alert("unrecognized poi type");
                    }
                    marker.setIcon(L.icon({
                        iconUrl: iconPath,
                        iconSize: [30, 30]
                    }));
                    marker._selected = false;
                });
                placesOfAvailability = [];
                updatePath();
                alert("mission uploaded with success !");
            } else {
                alert(result.message);
            }
        });
    });
}


function displayPois(pois) {
    if (clusterGroup) {
        map.removeLayer(clusterGroup);
    }
    markers = [];
    clusterGroup = L.markerClusterGroup();
    if (pois) {
        for (let i = 0; i < pois.length; i++) {
            let poi = pois[i];
            var marker = new L.Marker([poi.lat, poi.lng], {
                title: decodeURI(poi.name)
            });
            marker._id = poi.id;
            marker._type = poi.type;
            marker._selected = false;
            marker._title = decodeURI(poi.name);
            let iconPath;
            switch (poi.type) {
                case 0: // harbour
                    iconPath = "../../assets/poi_harbour_icon.png";
                    break;
                case 1: // anchorage
                    iconPath = "../../assets/poi_anchorage_icon.png";
                    break;
                case 2: // oddity
                    iconPath = "../../assets/poi_oddity_icon.png";
                    break;
                default:
                    alert("unrecognized poi type");
            }
            marker.setIcon(L.icon({
                iconUrl: iconPath,
                iconSize: [30, 30]
            }));
            marker
                .on('click', function (event) {
                    const poiId = event.sourceTarget._id;
                    if (poiSelectionEnabled && (event.sourceTarget._type == 0 || event.sourceTarget._type == 1)) {
                        const typeStr = event.sourceTarget._type == 0 ? "harbour" : "anchorage";
                        if (event.sourceTarget._selected) {
                            event.sourceTarget._selected = false;
                            event.sourceTarget.setIcon(L.icon({
                                iconUrl: "../../assets/poi_" + typeStr + "_icon.png",
                                iconSize: [30, 30]
                            }));
                            placesOfAvailability.splice(placesOfAvailability.indexOf(poiId), 1);
                        } else {
                            event.sourceTarget._selected = true;
                            event.sourceTarget.setIcon(L.icon({
                                iconUrl: "../../assets/poi_" + typeStr + "_mission_icon.png",
                                iconSize: [30, 30]
                            }));
                            placesOfAvailability.push(poiId);
                        }
                        updatePath();
                    }
                })
                .on('dblclick', function (event) {
                    const poiId = event.sourceTarget._id;
                    const lat = event.latlng.lat;
                    const lng = event.latlng.lng;
                    if (pickedStep !== null) {
                        locateStep(lat, lng, poiId);
                        pickedStep.find('.fas.fa-crosshairs').removeClass('locating');
                        pickedStep.find('.poi-label').text(event.sourceTarget._title);
                        pickedStep = null;
                        updatePath();
                    }
                });
            clusterGroup.addLayer(marker);
            markers.push(marker);
        }
    }
    map.addLayer(clusterGroup);
}

function locateStep(lat, lng, poiId) {
    // fill out inputs
    pickedStep.find('input[name="lat"]').val(lat.toFixed(3));
    pickedStep.find('input[name="lng"]').val(lng.toFixed(3));
    //delete all waypoints that have the same id
    waypoints.forEach(wpt => {
        if (wpt._stepId == pickedStep.attr('id')) {
            clusterGroup.removeLayer(wpt);
            waypoints.splice(waypoints.indexOf(wpt), 1);
        }
    });
    if (poiId !== undefined) {
        pickedStep.find('input[name="poi-id"]').val(poiId);
    } else {
        pickedStep.find('input[name="poi-id"]').val("");
        // create a waypoint on the map
        var waypoint = new L.Marker([lat, lng]);
        waypoint.setIcon(L.icon({
            iconUrl: "../../assets/poi_waypoint_icon.png",
            iconSize: [30, 30]
        }));
        waypoint._stepId = pickedStep.attr('id');
        waypoints.push(waypoint);
        clusterGroup.addLayer(waypoint);
    }
    updatePath();
}

function updatePath() {
    //reset last path
    path.concat(arrows).forEach(elt => {
        clusterGroup.removeLayer(elt);
    });
    arrows = [];
    path = [];

    var steps = $('#steps-container').children('.step-container');
    if (steps.length > 1) {
        //draw arrows
        for (let i = 0; i < steps.length - 1; i++) {
            const startLatStr = $(steps[i]).find('input[name="lat"]').val();
            const startLngStr = $(steps[i]).find('input[name="lng"]').val();
            const endLatStr = $(steps[i + 1]).find('input[name="lat"]').val();
            const endLngStr = $(steps[i + 1]).find('input[name="lng"]').val();
            if (startLatStr != "" && startLngStr != "" && endLatStr != "" && endLngStr != "") {
                var startLat = parseFloat(startLatStr);
                var startLng = parseFloat(startLngStr);
                var endLat = parseFloat(endLatStr);
                var endLng = parseFloat(endLngStr);
                var startCoeff = 0.1;
                var endCoeff = 0.9;
                if (i == 0) {
                    if (startLabel) {
                        map.removeLayer(startLabel);
                    }
                    startLabel = L.marker([startLat, startLng], {
                        icon: L.divIcon({ className: 'text-danger', html: 'start', iconAnchor: [-20, 10] })
                    });
                    startLabel.addTo(map);
                    markers.forEach(marker => {
                        if (placesOfAvailability.includes(marker._id)) {
                            var pos = marker.getLatLng();
                            if (Math.abs(pos.lat - startLat) > 0.0001 && Math.abs(pos.lng - startLng) > 0.0001) {
                                var polyline = L.polyline([[startLat, startLng], [pos.lat, pos.lng]]);
                                var arrow = L.swoopyArrow([pos.lat + startCoeff * (startLat - pos.lat), pos.lng + startCoeff * (startLng - pos.lng)], [pos.lat + endCoeff * (startLat - pos.lat), pos.lng + endCoeff * (startLng - pos.lng)], beginArrowOption);
                                arrows.push(arrow);
                                path.push(polyline);
                                clusterGroup.addLayer(arrow);
                            }
                        }
                    });
                }
                if (i == steps.length - 2) {
                    if (endLabel) {
                        map.removeLayer(endLabel);
                    }
                    endLabel = L.marker([endLat, endLng], {
                        icon: L.divIcon({ className: 'text-danger', html: 'end', iconAnchor: [-20, -10] })
                    });
                    endLabel.addTo(map);
                }
                var arrow = L.swoopyArrow([startLat + startCoeff * (endLat - startLat), startLng + startCoeff * (endLng - startLng)], [startLat + endCoeff * (endLat - startLat), startLng + endCoeff * (endLng - startLng)], transitArrowOption);
                arrows.push(arrow)
                path.push(arrow);
                clusterGroup.addLayer(arrow);
            }
        }
    } else if (steps.length == 1) {
        var startLat = parseFloat($(steps[0]).find('input[name="lat"]').val());
        var startLng = parseFloat($(steps[0]).find('input[name="lng"]').val());
        var startCoeff = 0.1;
        var endCoeff = 0.9;
        markers.forEach(marker => {
            if (placesOfAvailability.includes(marker._id)) {
                var pos = marker.getLatLng();
                console.log(pos)
                if (Math.abs(pos.lat - startLat) > 0.0001 && Math.abs(pos.lng - startLng) > 0.0001) {
                    var polyline = L.polyline([[startLat, startLng], [pos.lat, pos.lng]]);
                    var arrow = L.swoopyArrow([pos.lat + startCoeff * (startLat - pos.lat), pos.lng + startCoeff * (startLng - pos.lng)], [pos.lat + endCoeff * (startLat - pos.lat), pos.lng + endCoeff * (startLng - pos.lng)], beginArrowOption);
                    arrows.push(arrow);
                    path.push(polyline);
                    clusterGroup.addLayer(arrow);
                }
            }
        });
    } else {
        if (startLabel) {
            map.removeLayer(startLabel);
        }
        if (endLabel) {
            map.removeLayer(endLabel);
        }
    }
    var stepIds = [];
    $('.step-container').each(function () {
        stepIds.push($(this).attr('id'));
    });
    for (let i = waypoints.length - 1; i >= 0; i--) {
        const waypoint = waypoints[i];
        if (!stepIds.includes(waypoint._stepId)) {
            map.removeLayer(waypoint);
            waypoints.splice(waypoints.indexOf(waypoint), 1)
        }
    }
}

function onStepPick(id) {
    if (pickedStep === null) {
        pickedStep = $('#step-' + id + '-container');
        pickedStep.find('.fas.fa-crosshairs').addClass('locating');
        alert("double click on a POI to select it\nor double click anywhere on the map to create a custom waypoint");
    } else {
        let title = pickedStep.find('.title').text();
        alert("you are already selecting a location for an other step (" + title + ")");
    }
}