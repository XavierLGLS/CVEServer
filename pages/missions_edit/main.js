var map, clusterGroup;
var path = [], arrows = [], waypoints = [], placesOfAvailability = [], markers = [];
var startLabel, endLabel;
var pickedStep = null;
var stepId = 0;
var poiLoaded = false;
var poiSelectionEnabled = false;
var missionsInPoiSearch = false;

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

$().ready(function () {
    initMap();
    initSearchField();
});

function initMap() {
    // map layout
    map = L.map('map', {
        doubleClickZoom: false
    }).setView([0, 0], 15);
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
    $(".footer").css("position", "relative");
    clusterGroup = L.markerClusterGroup();

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

    //get pois
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

function initSearchField() {
    $('#name-search-button').on('click', function () {
        if ($('#missions-container').find('.mission-edit-form').length > 0) {
            if (!confirm("You are going to cancel your current modification. Do you really want to search other missions ?")) {
                return;
            }
        }
        var search = $('input[name="search-field"]').val();
        if (search === undefined || search == "") {
            if (!confirm("There is no content in the search field, do you want to get all missions ?")) {
                return;
            }
        }
        $('#name-search-button').html('<span class="spinner-border spinner-border-sm"></span> Find a mission by name');
        $('#name-search-button').prop('disabled', true);
        $.post("../../backend/rest/missions.php", {
            action: "get-all-missions-matching",
            "app-token": getAppToken(),
            "field": encodeURI(search),
            "user-id": getUserId()
        }, function (result, status) {
            if (result.success) {
                clearPage();
                displayMissionOverviews(result.missions);
                $('#name-search-button').html('<i class="fas fa-search"></i> Find a mission by name');
                $('#name-search-button').prop('disabled', false);
            } else {
                alert(result.message);
            }
        });
    });
    $('#poi-search-button').on('click', function () {
        pickedStep = null;
        missionsInPoiSearch = true;
        alert("Double click on a POI to get all missions available there");
    });
}

function initDeleteButtons() {
    $('.mission-deleter').on('click', function () {
        var parent = $(this).parent().parent().parent();
        var id = parseInt(parent.attr('id').split('-')[1]);
        var title = parent.find('.mission-title').text();
        if (confirm("do you really want to delete the \"" + title + "\" mission ?")) {
            $.post("../../backend/rest/missions.php", {
                action: "remove-mission",
                "app-token": getAppToken(),
                "mission-id": id
            }, function (result, status) {
                if (result.success) {
                    parent.remove();
                } else {
                    alert(result.message);
                }
            });
        }
    });
}

function displayMissionOverviews(missions) {
    if (missions.length > 0) {
        missions.forEach(mission => {
            displayMissionOverview(mission);
        });
    } else {
        $('#missions-container').append($('<p class="text-info">no mission found</p>'));
    }
    initDeleteButtons();
    $('.mission-editor,.mission-title').on('click', function () {
        if (!poiLoaded) {
            alert('please wait POIs to be loaded on the map before editing any mission\nit should take almost 20s');
            return;
        }
        var id;
        if ($(this).hasClass('mission-title')) {
            id = parseInt($(this).parent().parent().attr('id').split('-')[1]);
        } else if ($(this).hasClass('mission-editor')) {
            id = parseInt($(this).parent().parent().parent().attr('id').split('-')[1]);
        }
        $.post("../../backend/rest/missions.php", {
            action: "get-mission-by-id",
            "app-token": getAppToken(),
            "mission-id": id
        }, function (result, status) {
            if (result.success) {
                $('#missions-container').empty();
                displayMissionEditForm(result.mission);
                updatePath();
            } else {
                alert(result.message);
            }
        });
    });
}

function displayMissionOverview(mission) {
    var str = '';
    str += '<div class="card" id="mission-' + mission.id + '-card">';
    str += '    <div class="card-header d-flex">';
    str += '        <strong class="mission-title">' + decodeURI(mission.title) + '</strong>';
    str += '        <div class="ml-auto">';
    str += '            <span class="text-secondary">by ' + decodeURI(mission.creator) + '</span>';
    str += '            <i class="fas fa-pen mission-editor" style="color: #ffc107;" title="edit the mission"></i>';
    str += '            <i class="fas fa-trash-alt mission-deleter" style="color: red;" title="delete the mission"></i>';
    str += '        </div>';
    str += '    </div>';
    str += '</div>';

    $('#missions-container').append($(str));
}

function displayMissionEditForm(mission) {
    var str = '';
    str += '<div class="card mission-edit-form" id="mission-' + mission.id + '-card">';
    str += '    <input type="hidden" name="mission-id" value="' + mission.id + '">';
    str += '    <div class="card-header d-flex">';
    str += '        <strong class="mission-title">' + decodeURI(mission.title) + '</strong>';
    str += '        <div class="ml-auto">';
    str += '            <span class="text-secondary">by ' + decodeURI(mission.creator) + '</span>';
    str += '            <i class="fas fa-upload" style="color: #ffc107;" title="send modifications" onClick="submitEdition();"></i>';
    str += '            <i class="fas fa-trash-alt mission-deleter" style="color: red;" title="delete the mission"></i>';
    str += '        </div>';
    str += '    </div>';
    str += '    <div class="card-body">';
    str += '        <label for="mission-title">Title</label>';
    str += '        <input type="text" class="form-control" name="mission-title" value="' + decodeURI(mission.title) + '">';
    str += '        <label for="mission-passengers">Passengers</label>';
    str += '        <input type="text" class="form-control" name="mission-passengers" value="' + mission.passengers + '">';
    str += '        <label for="mission-caption">Description</label>';
    str += '        <textarea class="form-control" name="mission-caption" rows="3">' + decodeURI(mission.caption) + '</textarea>';
    str += '        <div id="steps-container" style="overflow: scroll; height: 50vh;">';
    str += '            <div id="new-step" class="text-center"><i class="fas fa-plus-circle"></i> Add a new step</div>';
    str += '        </div>';
    str += '        <div class="form-group">';
    str += '            <label for="poi-selection">Enable the selection of places of availability</label>';
    str += '            <i id="poi-selection" class="fas fa-toggle-off" style="padding-top: 1.5em;"></i>';
    str += '            <small class="form-text text-muted">click on a POI to add it to the list of places of availability</small>';
    str += '        </div>';
    str += '    </div>';
    str += '</div>';

    $('#missions-container').append($(str));


    function rankSorter(step1, step2) {
        if (step1.rank > step2.rank) {
            return 1;
        } else {
            return -1;
        }
    }
    var sortedSteps = mission.steps.sort(rankSorter);
    sortedSteps.forEach(step => {
        addStep(stepId++, step);
    });

    $('#new-step').on('click', function () {
        addStep(stepId++);
    });

    initDeleteButtons();

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

    placesOfAvailability = mission.places.map(elt => elt.id);
    markers.forEach(marker => {
        if (placesOfAvailability.includes(marker._id)) {
            marker._selected = true;
            const typeStr = marker._type == 0 ? "harbour" : "anchorage";
            marker.setIcon(L.icon({
                iconUrl: "../../assets/poi_" + typeStr + "_mission_icon.png",
                iconSize: [30, 30]
            }));
        }
    });
    updatePath();

}

function addStep(stepId, stepDesc) {
    var newRank = ($('.step-container')).length;
    var stepStr = '';
    stepStr += '<div class="step-container" id="step-' + stepId + '-container">';
    stepStr += '    <div class="d-flex">';
    stepStr += '        <i class="fas fa-expand-alt toggler" data-toggle="collapse" data-target="#step-' + stepId + '"></i>';
    stepStr += '        <small class="text-info poi-label" style="margin-left: 1em;">' + (stepDesc === undefined ? "" : decodeURI(stepDesc.poi.name)) + '</small>';
    stepStr += '        <div class="ml-auto">';
    stepStr += '            <span class="text-info">step </span>';
    stepStr += '            <span class="order text-info">' + ((stepDesc === undefined ? newRank : stepDesc.rank) + 1) + '</span>';
    stepStr += '            <i class="fas fa-caret-square-up"></i>';
    stepStr += '            <i class="fas fa-caret-square-down"></i>';
    stepStr += '            <i class="fas fa-trash-alt"></i>';
    stepStr += '        </div>';
    stepStr += '    </div>';
    stepStr += '    <div>';
    stepStr += '        <small class="title">' + (stepDesc === undefined ? "" : decodeURI(stepDesc.caption)) + '</small>';
    stepStr += '    </div>';
    stepStr += '    <div id="step-' + stepId + '" class="collapse">';
    stepStr += '        <div class="form-inline form-group input-group">';
    stepStr += '            <input type="number" name="lat" class="form-control" step="0.001" placeholder="latitude" value="' + (stepDesc === undefined ? "" : parseFloat(stepDesc.poi.lat)) + '">';
    stepStr += '            <input type="number" name="lng" class="form-control" step="0.001" placeholder="longitude" value="' + (stepDesc === undefined ? "" : parseFloat(stepDesc.poi.lng)) + '">';
    stepStr += '            <input type="hidden" name="poi-id" value="' + (stepDesc === undefined ? "" : (stepDesc.poi.type != 3 ? stepDesc.poi.id : "")) + '">';
    stepStr += '            <div class="icons-container">';
    stepStr += '                <i class="fas fa-crosshairs" title="select a new location or a POI by clicking on the map" onclick="onStepPick(' + stepId + ')"></i>';
    stepStr += '            </div>';
    stepStr += '        </div>';
    stepStr += '        <div class="form-group">';
    stepStr += '            <textarea class="form-control" rows="2" name="step-caption" placeholder="description (optional)">' + (stepDesc === undefined ? "" : decodeURI(stepDesc.caption)) + '</textarea>';
    stepStr += '        </div>';
    stepStr += '    </div>';
    stepStr += '</div>';

    let newStep = $(stepStr);
    newStep.insertBefore($("#new-step"));
    initStep(newStep);

    if (stepDesc !== undefined) {
        var poi = stepDesc.poi;
        if (poi.type == 3) {
            var waypoint = new L.Marker([poi.lat, poi.lng]);
            waypoint.setIcon(L.icon({
                iconUrl: "../../assets/poi_waypoint_icon.png",
                iconSize: [30, 30]
            }));
            waypoint._stepId = `step-${stepId}-container`;
            waypoints.push(waypoint);
            clusterGroup.addLayer(waypoint);
        }
    }
}

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
                    } else if (missionsInPoiSearch) {
                        getMissionsInPoi(poiId);
                    }
                });
            let typeStr;
            if (poi.type == 0) {
                typeStr = "harbour";
            } else if (poi.type == 1) {
                typeStr = "anchorage";
            } else if (poi.type == 2) {
                typeStr = "oddity";
            } else {
                typeStr = "waypoint";
            }
            marker.setIcon(L.icon({
                iconUrl: "../../assets/poi_" + typeStr + "_icon.png",
                iconSize: [30, 30]
            }));
            clusterGroup.addLayer(marker);
            markers.push(marker);
        }
    }
    map.addLayer(clusterGroup);
    $('#name-search-button').prop('disabled', false);
    $('#poi-search-button').prop('disabled', false);
    $('#missions-container').empty();
    poiLoaded = true;
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
                if (i == 0 && steps.length > 1) {
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
                if (i == steps.length - 2 && steps.length > 1) {
                    if (endLabel) {
                        map.removeLayer(endLabel);
                    }
                    endLabel = L.marker([endLat, endLng], {
                        icon: L.divIcon({ className: 'text-danger', html: 'end', iconAnchor: [-20, -10] })
                    });
                    endLabel.addTo(map);
                }
                var arrow = L.swoopyArrow([startLat + startCoeff * (endLat - startLat), startLng + startCoeff * (endLng - startLng)], [startLat + endCoeff * (endLat - startLat), startLng + endCoeff * (endLng - startLng)], transitArrowOption);
                var polyline = L.polyline([[startLat, startLng], [endLat, endLng]]);// not shown on the map, only used for bounds
                arrows.push(arrow);
                path.push(polyline);
                clusterGroup.addLayer(arrow);
            }
        }
        var group = new L.featureGroup(path);
        map.fitBounds(group.getBounds());
    } else if (steps.length == 1) {
        var startLat = parseFloat($(steps[0]).find('input[name="lat"]').val());
        var startLng = parseFloat($(steps[0]).find('input[name="lng"]').val());
        var startCoeff = 0.1;
        var endCoeff = 0.9;
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
        var group = new L.featureGroup(path);
        map.fitBounds(group.getBounds());
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
            clusterGroup.removeLayer(waypoint);
            waypoints.splice(waypoints.indexOf(waypoint), 1)
        }
    }
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

function submitEdition() {
    mission = {};
    mission["id"] = parseInt($('input[name="mission-id"]').val());
    mission["places_of_availability"] = placesOfAvailability;
    var errors = [];
    var missionName = $('input[name="mission-title"]').val();
    if (missionName == "" || missionName === undefined) {
        errors.push("You must define a mission name");
    } else {
        mission["name"] = encodeURI(missionName);
    }
    var missionPassengers = parseInt($('input[name="mission-passengers"]').val());
    if (isNaN(missionPassengers)) {
        errors.push("You must define the number of passengers");
    } else {
        mission["passengers"] = missionPassengers;
    }
    var caption = $('textarea[name="mission-caption"]').val()
    if (caption != "" && caption !== undefined) {
        mission["caption"] = encodeURI(caption);
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
        action: "edit-mission",
        "app-token": getAppToken(),
        "user-id": getUserId(),
        "mission": JSON.stringify(mission)
    }, function (result, status) {
        if (result.success) {
            clearPage();
            alert("mission edited with success");
        } else {
            alert(result.message);
        }
    });
}

function getMissionsInPoi(id) {
    $('#poi-search-button').html('<span class="spinner-border spinner-border-sm"></span> Find a mission in a POI');
    $('#poi-search-button').prop('disabled', true);
    $.post("../../backend/rest/missions.php", {
        action: "get-all-missions-in-poi",
        "app-token": getAppToken(),
        "poi-id": id,
        "user-id": getUserId()
    }, function (result, status) {
        if (result.success) {
            clearPage();
            displayMissionOverviews(result.missions);
            $('#poi-search-button').html('<i class="fas fa-search"></i> Find a mission in a POI');
            $('#poi-search-button').prop('disabled', false);
        } else {
            alert(result.message);
        }
    });
}

function incrementOrder(step) {
    var span = step.find(".order");
    span.text(parseInt(span.text()) + 1);
}

function decrementOrder(step) {
    var span = step.find(".order");
    span.text(parseInt(span.text()) - 1);
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

function clearPage() {
    markers.forEach(marker => {
        const typeStr = marker._type == 0 ? "harbour" : "anchorage";
        marker.setIcon(L.icon({
            iconUrl: "../../assets/poi_" + typeStr + "_icon.png",
            iconSize: [30, 30]
        }));
        marker._selected = false;
    });
    placesOfAvailability = [];
    pickedStep = null;
    poiSelectionEnabled = false;
    missionsInPoiSearch = false;
    $('#missions-container').empty();
    updatePath();
}