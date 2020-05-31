<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';
?>
<!-- leaflet lib -->
<link rel="stylesheet" href="../../libs/leaflet/leaflet.css" />
<script src="../../libs/leaflet/leaflet.js"></script>
<!-- marker cluster -->
<link rel="stylesheet" href="MarkerCluster.css" />
<link rel="stylesheet" href="MarkerCluster.Default.css" />
<script src="leaflet.markercluster-src.js"></script>
<!-- custom arrows -->
<script src="../../libs/leaflet-arrow/main.js"></script>
<!-- custom spinner -->
<script src="../../libs/leaflet-spin/spin.min.js"></script>
<script src="../../libs/leaflet-spin/leaflet.spin.min.js"></script>
<style>
    /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
    #map {
        height: 100%;
        margin: 1em;
    }

    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    .legend {
        padding: 6px 8px;
        font: 14px/16px Arial, Helvetica, sans-serif;
        background: white;
        background: rgba(255, 255, 255, 0.8);
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        border-radius: 5px;
    }

    .legend-item {
        padding: 2px 0px;
    }

    .mission-header {
        margin-bottom: 0.5em;
        padding: 0.2em;
        border-radius: 5px;
        cursor: pointer;
    }

    .auto-mission-header {
        background-color: #d2b4de;
    }

    .manual-mission-header {
        background-color: #ffc107;
    }

    .mission-overview {
        min-width: 250px;
        max-width: 30vw;
    }

    .mission-header:hover {
        opacity: 0.8;
    }
</style>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Places of interest</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<?php if (isset($_SESSION["auth"])) : ?>
    <div class="text-center">
        <i>click on a POI to get its characteristics</i>
    </div>
    <div id="map" style="height: 650px;" class="content-wrapper"></div>
<?php else : ?>
    <div class="text-center content-wrapper">
        <i>you must be connected to access this content</i>
    </div>
<?php endif ?>
<script>
    var map;
    var clusterGroup;
    var areaCircle = null;
    var displayDelayerId = null;

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

    $().ready(function() {
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
            position: 'bottomleft'
        });
        legend.onAdd = function(map) {
            var div = L.DomUtil.create('div', 'legend');
            div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_harbour_icon.png" height="20" width="20"> harbour</div>';
            div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_anchorage_icon.png" height="20" width="20"> anchorage</div>';
            div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_oddity_icon.png" height="20" width="20"> oddity</div>';
            div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_waypoint_icon.png" height="20" width="20"> waypoint</div>';
            return div;
        }
        legend.addTo(map);
        map.spin(true);
        //get last pois
        $.post("../../backend/rest/pois.php", {
            action: "get-pois",
            "app-token": getAppToken()
        }, function(result, status) {
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
                let iconUrl, popupContent;
                const manualMissions = poi.missions.filter(elt => elt.type == 1);
                switch (poi.type) {
                    case 0: // harbour
                        if (manualMissions.length > 0) {
                            iconUrl = "../../assets/poi_harbour_mission_icon.png";
                        } else {
                            iconUrl = "../../assets/poi_harbour_icon.png";
                        }
                        popupContent = getharbourPopup(poi);
                        break;
                    case 1: // anchorage
                        if (manualMissions.length > 0) {
                            iconUrl = "../../assets/poi_anchorage_mission_icon.png";
                        } else {
                            iconUrl = "../../assets/poi_anchorage_icon.png";
                        }
                        popupContent = getAnchoragePopup(poi);
                        break;
                    case 2: // oddity
                        iconUrl = "../../assets/poi_oddity_icon.png";
                        popupContent = getOddityPopup(poi);
                        break;
                    default:
                        alert("unrecognized poi type");
                }
                marker.setIcon(L.icon({
                    iconUrl: iconUrl,
                    iconSize: [30, 30]
                }));
                marker.bindPopup(popupContent, {
                        maxWidth: 500
                    })
                    .on('mouseover', function(event) {
                        if (areaCircle) {
                            map.removeLayer(areaCircle);
                        }
                        areaCircle = L.circle([poi.lat, poi.lng], {
                            color: 'blue',
                            weight: 0,
                            fillColor: '#4287f5',
                            fillOpacity: 0.5,
                            radius: getDetectionRadiusKm() * 1000
                        });
                        displayDelayerId = setTimeout(areaDisplayDelayer, 700);
                    })
                    .on('mouseout', function() {
                        map.removeLayer(areaCircle);
                        areaCircle = null;
                        clearTimeout(displayDelayerId);
                    });
                clusterGroup.addLayer(marker);
            }
        }
        map.addLayer(clusterGroup);
    }

    function getharbourPopup(harbour) {
        var str = `<h6>${decodeURI(harbour.name)}</h6>`;
        str += '<strong>Facilities:</strong>';
        str += '<ul style="list-style-type:none;  padding-left: 0px;">';
        str += "<li>" + (harbour.provides_water ? "&#x2714" : "&#x274C") + " water</li>";
        str += "<li>" + (harbour.sells_food ? "&#x2714" : "&#x274C") + " food</li>";
        str += "<li>" + (harbour.sells_spare_parts ? "&#x2714" : "&#x274C") + " spare parts</li>";
        str += "<li>" + (harbour.dry_dock ? "&#x2714" : "&#x274C") + " dry dock</li>";
        str += "</ul>";
        str += getMissionsList(harbour.missions);
        return str;
    }

    function getAnchoragePopup(anchorage) {
        var str = `<h6>${decodeURI(anchorage.name)}</h6>`;
        str += getMissionsList(anchorage.missions);
        return str;
    }

    function getOddityPopup(oddity) {
        var str = `<h6>${decodeURI(oddity.name)}</h6>`;
        if (oddity.caption != "") {
            str += `<p class="text-info"><i>${decodeURI(oddity.caption)}</i></p>`;
        }
        return str;
    }

    function getMissionsList(missions) {
        const autoMissions = missions.filter(elt => elt.type == 0);
        const manualMissions = missions.filter(elt => elt.type == 1);
        var str = '';
        if (missions.length > 0) {
            str += '<strong>Missions:</strong>';
            str += '<div style="overflow-y: scroll; max-height: 150px;">';
            manualMissions.forEach(mission => {
                str += '<div class="mission-header manual-mission-header" onclick="requestMissionDisplay(' + mission.id + ')">';
                str += '<div>';
                str += decodeURI(mission.title);
                str += '</div>';
                str += '<small class="text-muted" style="margin-right: 0.5em;">by ' + decodeURI(mission.creator) + '</small>';
                str += '<small>reward: ' + mission.reward + '</small>';
                str += '</div>';
            });
            autoMissions.forEach(mission => {
                str += '<div class="mission-header auto-mission-header" onclick="requestMissionDisplay(' + mission.id + ')">';
                str += '<div>';
                str += decodeURI(mission.title);
                str += '</div>';
                str += '<small>reward: ' + mission.reward + '</small>';
                str += '</div>';
            });
            str += '</div>';
        }
        return str;
    }

    function requestMissionDisplay(missionId) {
        $.post("../../backend/rest/missions.php", {
            action: "get-mission-by-id",
            "mission-id": missionId,
            "app-token": getAppToken()
        }, function(result, status) {
            if (result.success) {
                displayMission(result.mission);
            } else {
                alert(result.message);
            }
        });
    }

    function displayMission(mission) {
        if ($('#map-' + mission.id).length == 0) {
            var missionDisplay = L.control({
                position: 'topright'
            });
            missionDisplay.onAdd = function(map) {
                var div = L.DomUtil.create('div', 'legend mission-overview');
                div.innerHTML += '<h5>' + decodeURI(mission.title) + '</h5>';
                div.innerHTML += '<small class="text-muted">by ' + decodeURI(mission.creator) + '</small>';
                div.innerHTML += '<div id="map-' + mission.id + '" style="height: 20vh;"><div>';
                div.innerHTML += '<p class="text-info">' + decodeURI(mission.caption) + '<p>';
                div.innerHTML += '<p class="text-secondary" style="margin-bottom: 0;">reward: ' + mission.reward + '</p>';
                div.innerHTML += '<p class="text-secondary">passengers: ' + mission.passengers + '</p>';
                div.innerHTML += '<button type="button" class="btn btn-sm btn-danger" onclick="$(this).parent().remove();">close</button>'
                return div;
            }
            missionDisplay.addTo(map);

            setTimeout(function() {
                var mapItems = [];
                var map = L.map(('map-' + mission.id), {
                    doubleClickZoom: false
                }).setView([0, 0], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?{foo}', {
                    foo: 'bar'
                }).addTo(map);

                mission.steps.sort((stepA, stepB) => {
                    if (stepA.rank > stepB.rank) {
                        return 1;
                    }
                    if (stepA.rank < stepB.rank) {
                        return -1;
                    }
                    return 0;
                });

                for (let i = 0; i < mission.steps.length; i++) {
                    const step = mission.steps[i];

                    if (i == 0 && mission.steps.length > 1) {
                        var startLabel = L.marker([step.poi.lat, step.poi.lng], {
                            icon: L.divIcon({
                                className: 'text-danger',
                                html: 'start',
                                iconAnchor: [-20, 10]
                            })
                        });
                        startLabel.addTo(map);
                        mapItems.push(startLabel);
                    } else if (i == mission.steps.length - 1 && mission.steps.length > 1) {
                        var endLabel = L.marker([step.poi.lat, step.poi.lng], {
                            icon: L.divIcon({
                                className: 'text-danger',
                                html: 'end',
                                iconAnchor: [-20, -10]
                            })
                        });
                        endLabel.addTo(map);
                        mapItems.push(endLabel);
                    }

                    // marker
                    var iconPath;
                    switch (step.poi.type) {
                        case 0:
                            iconPath = "../../assets/poi_harbour_icon.png";
                            break;
                        case 1:
                            iconPath = "../../assets/poi_anchorage_icon.png";
                            break;
                        case 2:
                            iconPath = "../../assets/poi_oddity_icon.png";
                            break;
                        case 3:
                            iconPath = "../../assets/poi_waypoint_icon.png";
                            break;
                        default:
                            alert("unknown poi type");
                    }
                    var marker = L.marker([step.poi.lat, step.poi.lng]);
                    if (step.caption != "") {
                        marker.bindPopup(decodeURI(step.caption));
                    }
                    marker.setIcon(L.icon({
                        iconUrl: iconPath,
                        iconSize: [30, 30]
                    }));
                    mapItems.push(marker);
                    marker.addTo(map);

                    // places of availability
                    if (i == 0) {
                        var startLat = parseFloat(mission.steps[0].poi.lat);
                        var startLng = parseFloat(mission.steps[0].poi.lng);
                        var startCoeff = 0.1;
                        var endCoeff = 0.9;
                        mission.places.forEach(place => {
                            // marker
                            var iconPath;
                            switch (place.type) {
                                case 0:
                                    iconPath = "../../assets/poi_harbour_icon.png";
                                    break;
                                case 1:
                                    iconPath = "../../assets/poi_anchorage_icon.png";
                                    break;
                                default:
                                    alert("unknown poi type");
                            }
                            var marker = L.marker([place.lat, place.lng]);
                            marker.bindPopup(decodeURI(decodeURI(place.name)));
                            marker.setIcon(L.icon({
                                iconUrl: iconPath,
                                iconSize: [30, 30]
                            }));
                            mapItems.push(marker);
                            marker.addTo(map);

                            // arrow
                            var pos = {
                                lat: place.lat,
                                lng: place.lng
                            };
                            if (Math.abs(pos.lat - startLat) > 0.0001 && Math.abs(pos.lng - startLng) > 0.0001) {
                                var polyline = L.polyline([
                                    [startLat, startLng],
                                    [pos.lat, pos.lng]
                                ]);
                                var arrow = L.swoopyArrow([pos.lat + startCoeff * (startLat - pos.lat), pos.lng + startCoeff * (startLng - pos.lng)], [pos.lat + endCoeff * (startLat - pos.lat), pos.lng + endCoeff * (startLng - pos.lng)], beginArrowOption);
                                mapItems.push(polyline);
                                arrow.addTo(map);
                            }
                        });
                    }

                    //arrow
                    if (i < mission.steps.length - 1 && mission.steps.length > 1) {
                        var startCoeff = 0.2;
                        var endCoeff = 0.8;
                        var startLat = mission.steps[i].poi.lat;
                        var startLng = mission.steps[i].poi.lng;
                        var endLat = mission.steps[i + 1].poi.lat;
                        var endLng = mission.steps[i + 1].poi.lng;
                        var arrow = L.swoopyArrow([startLat + startCoeff * (endLat - startLat), startLng + startCoeff * (endLng - startLng)], [startLat + endCoeff * (endLat - startLat), startLng + endCoeff * (endLng - startLng)], transitArrowOption);
                        var polyline = L.polyline([
                            [startLat, startLng],
                            [endLat, endLng]
                        ]); // not shown on the map, only used for bounds
                        mapItems.push(polyline);
                        arrow.addTo(map);
                    }
                }

                var group = new L.featureGroup(mapItems);
                map.fitBounds(group.getBounds());

            }, 200);
        }
    }

    function areaDisplayDelayer() {
        areaCircle.addTo(map);
    }
</script>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>