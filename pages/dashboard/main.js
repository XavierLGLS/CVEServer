var map, poiControl, missionControl;

var boat, otherBoats = [], pois, money, mission = null, waypoints = [], arrows = [], validationMarkers = [], userPath = null, userMarker = null, nearestPoi = null, otherMarkers = [], otherPaths = [];

var areaCircle = null, displayDelayerId = null, timeLeft = null;

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

    mapInit();

    dashBoardInit();

    poiPanelInit();

    missionPanelInit();

    initData();

    $(".footer").css("position", "relative");
});

function mapInit() {
    // map layout
    map = L.map('map', {
        doubleClickZoom: false,
        zoomControl: false
    }).setView([0, 0], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png?{foo}', {
        foo: 'bar',
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>'
    }).addTo(map);
    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    // legend
    var legend = L.control({
        position: 'bottomleft'
    });
    legend.onAdd = function () {
        var div = L.DomUtil.create('div', 'legend');
        div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_harbour_icon.png" height="20" width="20"> harbour</div>';
        div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_anchorage_icon.png" height="20" width="20"> anchorage</div>';
        div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_oddity_icon.png" height="20" width="20"> oddity</div>';
        div.innerHTML += '<div class="legend-item"><img src="../../assets/poi_waypoint_icon.png" height="20" width="20"> waypoint</div>';
        div.innerHTML += '<div class="legend-item"><label for="see-other-players">See other players</label><i id="see-other-players" class="fas fa-toggle-off clickable" style="margin-left: 0.5em;"></i></div>';
        div.innerHTML += '<div id="time-left-label" class="legend-item text-muted"></div>';
        return div;
    }
    legend.addTo(map);

    $('#see-other-players').click(function () {
        if ($(this).hasClass('fa-toggle-off')) {
            otherPlayersOn();
        } else if ($(this).hasClass('fa-toggle-on')) {
            otherPlayersOff();
        }
    });

    map.spin(true);
}

function dashBoardInit() {
    var control = L.control({
        position: 'topright'
    });
    control.onAdd = function () {
        var div = L.DomUtil.create('div', 'legend dashboard');
        div.innerHTML += $('#dashboard').html();
        return div;
    }
    control.addTo(map);
    // $('#dashboard').remove();

    // repair buttons
    $('#repair-hull')
        .mouseover(function () {
            if (boat.spare_parts > 0 && boat.hull < 100) {
                if (boat.hull > 90) {
                    $('#hull-damage-progress-add').css('width', (100 - boat.hull) + '%');
                } else {
                    $('#hull-damage-progress-add').css('width', '10%');
                }
                $('#spare-parts-progress').css('width', (100 * (boat.spare_parts - 1) / boat.spare_parts_capacity) + '%');
                $('#spare-parts-progress-remove').css('width', (100 / boat.spare_parts_capacity) + '%');
            }
        })
        .mouseleave(function () {
            $('#spare-parts-progress').css('width', (100 * boat.spare_parts / boat.spare_parts_capacity) + '%');
            $('#hull-damage-progress-add').css('width', '0%');
            $('#spare-parts-progress-remove').css('width', '0%');
        })
        .click(function () {
            if (boat.spare_parts > 0 && boat.hull < 100) {
                $.post("../../backend/rest/dashboard.php", {
                    action: "repair-hull",
                    "app-token": getAppToken(),
                    "boat-id": boat.id
                }, function (result, status) {
                    if (result.success) {
                        boat = result.boat;
                        updateDashBoard();
                    } else {
                        alert(result.message);
                    }
                });
            }
        });
    $('#repair-sails')
        .mouseover(function () {
            if (boat.spare_parts > 0 && boat.sails < 100) {
                if (boat.sails > 90) {
                    $('#sails-damage-progress-add').css('width', (100 - boat.sails) + '%');
                } else {
                    $('#sails-damage-progress-add').css('width', '10%');
                }
                $('#spare-parts-progress').css('width', (100 * (boat.spare_parts - 1) / boat.spare_parts_capacity) + '%');
                $('#spare-parts-progress-remove').css('width', (100 / boat.spare_parts_capacity) + '%');
            }
        })
        .mouseleave(function () {
            $('#spare-parts-progress').css('width', (100 * boat.spare_parts / boat.spare_parts_capacity) + '%');
            $('#sails-damage-progress-add').css('width', '0%');
            $('#spare-parts-progress-remove').css('width', '0%');
        })
        .click(function () {
            if (boat.spare_parts > 0 && boat.sails < 100) {
                $.post("../../backend/rest/dashboard.php", {
                    action: "repair-sails",
                    "app-token": getAppToken(),
                    "boat-id": boat.id
                }, function (result, status) {
                    if (result.success) {
                        boat = result.boat;
                        updateDashBoard();
                    } else {
                        alert(result.message);
                    }
                });
            }
        });
}

function updateDashBoard() {
    // boat name
    $('#dashboard-boat-name').text(decodeURI(boat.name));

    // food
    $('#food-progress').attr('aria-valuemax', boat.food_capacity);
    $('#food-progress').attr('aria-valuenow', boat.food);
    $('#food-progress').css('width', (100 * boat.food / boat.food_capacity) + '%');
    $('#food-label').text(`${boat.food}/${boat.food_capacity}`);
    // water
    $('#water-progress').attr('aria-valuemax', boat.water_capacity);
    $('#water-progress').attr('aria-valuenow', boat.water);
    $('#water-progress').css('width', (100 * boat.water / boat.water_capacity) + '%');
    $('#water-label').text(`${boat.water}/${boat.water_capacity}`);
    // spare parts
    $('#spare-parts-progress').attr('aria-valuemax', boat.spare_parts_capacity);
    $('#spare-parts-progress').attr('aria-valuenow', boat.spare_parts);
    $('#spare-parts-progress').css('width', (100 * boat.spare_parts / boat.spare_parts_capacity) + '%');
    $('#spare-parts-progress-remove').css('width', '0%');
    $('#spare-parts-label').text(`${boat.spare_parts}/${boat.spare_parts_capacity}`);

    // hull
    $('#hull-damage-progress').attr('aria-valuenow', boat.hull);
    $('#hull-damage-progress').css('width', boat.hull + '%');
    $('#hull-damage-progress-add').css('width', '0%');
    $('#hull-damage-label').text(boat.hull.toFixed(1) + '%');
    if (boat.hull < 80) {
        $('#hull-damage-progress').addClass("bg-danger");
    } else {
        $('#hull-damage-progress').removeClass("bg-danger");
    }
    // sails
    $('#sails-damage-progress').attr('aria-valuenow', boat.sails);
    $('#sails-damage-progress').css('width', boat.sails + '%');
    $('#sails-damage-progress-add').css('width', '0%');
    $('#sails-damage-label').text(boat.sails.toFixed(1) + '%');
    if (boat.sails < 80) {
        $('#sails-damage-progress').addClass("bg-danger");
    } else {
        $('#sails-damage-progress').removeClass("bg-danger");
    }
    // crew health
    $('#crew-health-progress').attr('aria-valuenow', boat.crew_health);
    $('#crew-health-progress').css('width', boat.crew_health + '%');
    $('#crew-health-label').text(boat.crew_health.toFixed(1) + '%');
    if (boat.crew_health < 100) {
        $('#crew-health-progress').addClass("bg-danger");
        $('#crew-health-progress').addClass("progress-bar-animated");
        $('#crew-health-progress').addClass("progress-bar-striped");
    } else {
        $('#crew-health-progress').removeClass("bg-danger");
        $('#crew-health-progress').removeClass("progress-bar-animated");
        $('#crew-health-progress').removeClass("progress-bar-striped");
    }

    //passengers
    $('#passengers').text(`${boat.passengers}/${boat.passengers_capacity}`);
    //money
    $('#money').text(money);

    // dry dock
    if (boat.in_dry_dock) {
        $('#dry-dock-container').css('display', 'block');
    } else {
        $('#dry-dock-container').css('display', 'none');
    }
}

function poiPanelInit() {
    poiControl = L.control({
        position: 'topleft'
    });
    poiControl.onAdd = function () {
        var div = L.DomUtil.create('div', 'legend poi-control');
        div.innerHTML += $('#poi-panel').html();
        return div;
    }
    poiControl.addTo(map);
}

function updatePoiPanel() {

    function updatePrice() {
        var price = 0;
        price += getSparePartPrice() * parseInt($('#poi-panel-spare-parts-progress-add').attr('amount'));
        price += getFoodPrice() * parseInt($('#poi-panel-food-progress-add').attr('amount'));
        price += getWaterPrice() * parseInt($('#poi-panel-water-progress-add').attr('amount'));
        $('#poi-panel-price').text(price);
    }

    var nearestPoi = null;
    if (pois.length > 0) {
        if (pois[0].distance <= getDetectionRadiusNm()) {
            nearestPoi = pois[0];
        }
    }

    map.addControl(poiControl);

    if (nearestPoi && !boat.in_dry_dock) {
        $('#poi-name').text(decodeURI(nearestPoi.name));

        if (nearestPoi.provides_water) {
            $('#poi-panel-water-container').css('display', 'flex');
            $('#poi-panel-water-progress').attr('aria-valuemax', boat.water_capacity);
            $('#poi-panel-water-progress').attr('aria-valuenow', boat.water);
            $('#poi-panel-water-progress').css('width', (100 * boat.water / boat.water_capacity) + '%');
            $('#poi-panel-water-progress-add').css('width', '0%');
            $('#poi-panel-water-label').text(`${boat.water}/${boat.water_capacity}`);
        } else {
            $('#poi-panel-water-container').css('display', 'none');
        }
        if (nearestPoi.sells_food) {
            $('#poi-panel-food-container').css('display', 'flex');
            $('#poi-panel-food-progress').attr('aria-valuemax', boat.food_capacity);
            $('#poi-panel-food-progress').attr('aria-valuenow', boat.food);
            $('#poi-panel-food-progress').css('width', (100 * boat.food / boat.food_capacity) + '%');
            $('#poi-panel-food-progress-add').css('width', '0%');
            $('#poi-panel-food-label').text(`${boat.food}/${boat.food_capacity}`);
        } else {
            $('#poi-panel-food-container').css('display', 'none');
        }
        if (nearestPoi.sells_spare_parts) {
            $('#poi-panel-spare-parts-container').css('display', 'flex');
            $('#poi-panel-spare-parts-progress').attr('aria-valuemax', boat.spare_parts_capacity);
            $('#poi-panel-spare-parts-progress').attr('aria-valuenow', boat.spare_parts);
            $('#poi-panel-spare-parts-progress').css('width', (100 * boat.spare_parts / boat.spare_parts_capacity) + '%');
            $('#poi-panel-spare-parts-progress-add').css('width', '0%');
            $('#poi-panel-spare-parts-label').text(`${boat.spare_parts}/${boat.spare_parts_capacity}`);
        } else {
            $('#poi-panel-spare-parts-container').css('display', 'none');
        }

        $('#poi-panel-water-remove').click(function () {
            var value = parseInt($('#poi-panel-water-progress-add').attr('amount'));
            if (value > 0) {
                $('#poi-panel-water-progress-add').attr('amount', value - 1);
                $('#poi-panel-water-progress-add').css('width', (100 * (value - 1) / boat.water_capacity) + '%');
            }
        });
        $('#poi-panel-water-add').click(function () {
            var value = parseInt($('#poi-panel-water-progress').attr('aria-valuenow'));
            var addValue = parseInt($('#poi-panel-water-progress-add').attr('amount'));
            if ((value + addValue) < boat.water_capacity) {
                $('#poi-panel-water-progress-add').attr('amount', addValue + 1);
                $('#poi-panel-water-progress-add').css('width', (100 * (addValue + 1) / boat.water_capacity) + '%');
            }
        });

        $('#poi-panel-food-remove').click(function () {
            var value = parseInt($('#poi-panel-food-progress-add').attr('amount'));
            if (value > 0) {
                $('#poi-panel-food-progress-add').attr('amount', value - 1);
                $('#poi-panel-food-progress-add').css('width', (100 * (value - 1) / boat.food_capacity) + '%');
                updatePrice();
            }
        });
        $('#poi-panel-food-add').click(function () {
            var value = parseInt($('#poi-panel-food-progress').attr('aria-valuenow'));
            var addValue = parseInt($('#poi-panel-food-progress-add').attr('amount'));
            if ((value + addValue) < boat.food_capacity) {
                $('#poi-panel-food-progress-add').attr('amount', addValue + 1);
                $('#poi-panel-food-progress-add').css('width', (100 * (addValue + 1) / boat.food_capacity) + '%');
                updatePrice();
            }
        });

        $('#poi-panel-spare-parts-remove').click(function () {
            var value = parseInt($('#poi-panel-spare-parts-progress-add').attr('amount'));
            if (value > 0) {
                $('#poi-panel-spare-parts-progress-add').attr('amount', value - 1);
                $('#poi-panel-spare-parts-progress-add').css('width', (100 * (value - 1) / boat.spare_parts_capacity) + '%');
                updatePrice();
            }
        });
        $('#poi-panel-spare-parts-add').click(function () {
            var value = parseInt($('#poi-panel-spare-parts-progress').attr('aria-valuenow'));
            var addValue = parseInt($('#poi-panel-spare-parts-progress-add').attr('amount'));
            if ((value + addValue) < boat.spare_parts_capacity) {
                $('#poi-panel-spare-parts-progress-add').attr('amount', addValue + 1);
                $('#poi-panel-spare-parts-progress-add').css('width', (100 * (addValue + 1) / boat.spare_parts_capacity) + '%');
                updatePrice();
            }
        });

        $('#poi-panel-buy-btn').click(function () {
            var list = {};
            list.water = $('#poi-panel-water-progress-add').attr('amount');
            list.food = $('#poi-panel-food-progress-add').attr('amount');
            list.spare_parts = $('#poi-panel-spare-parts-progress-add').attr('amount');
            $.post("../../backend/rest/dashboard.php", {
                action: "buy-items",
                "app-token": getAppToken(),
                "boat-id": getBoatId(),
                "user-id": getUserId(),
                items: list
            }, function (result, status) {
                if (result.success) {
                    boat = result.boat;
                    money = result.money;
                    updateDashBoard();
                    updatePoiPanel();
                } else {
                    alert(result.message);
                }
            });
        });

        if (nearestPoi.dry_dock) {
            $('#poi-panel-dry-dock-container').css('display', 'flex');
            $('#poi-panel-dry-dock-btn').click(function () {
                requestUseDryDock(nearestPoi);
            });
        } else {
            $('#poi-panel-dry-dock-container').css('display', 'none');
        }

        $('#poi-panel-missions-container').empty();
        if (nearestPoi.missions.length > 0) {
            const autoMissions = nearestPoi.missions.filter(elt => elt.type == 0);
            const manualMissions = nearestPoi.missions.filter(elt => elt.type == 1);
            $('#poi-panel-missions-container').css('display', 'block');
            $('#poi-panel-missions-label').css('display', 'block');
            var str = '';
            manualMissions.forEach(mission => {
                str += '<div class="manual-mission-header mission-header" onclick="requestMissionDisplay(' + mission.id + ')">';
                str += '<div>';
                str += decodeURI(mission.title);
                str += '</div>';
                str += '<small class="text-muted" style="margin-right: 0.5em;">by ' + decodeURI(mission.creator) + '</small>';
                str += '<small>reward: ' + mission.reward + '</small>';
                str += '</div>';
            });
            autoMissions.forEach(mission => {
                str += '<div class="auto-mission-header mission-header" onclick="requestMissionDisplay(' + mission.id + ')">';
                str += '<div>';
                str += decodeURI(mission.title);
                str += '</div>';
                str += '<small>reward: ' + mission.reward + '</small>';
                str += '</div>';
            });
            $('#poi-panel-missions-container').html(str);
        } else {
            $('#poi-panel-missions-container').css('display', 'none');
            $('#poi-panel-missions-label').css('display', 'none');
        }
    } else {
        map.removeControl(poiControl);
    }
}

function missionPanelInit() {
    missionControl = L.control({
        position: 'topleft'
    });
    missionControl.onAdd = function () {
        var div = L.DomUtil.create('div', 'legend mission-control');
        div.innerHTML += $('#mission-panel').html();
        return div;
    }
    missionControl.addTo(map);
}

function updateMissionPanel() {
    // reset
    arrows.forEach(arrow => {
        map.removeLayer(arrow);
    });
    arrows = [];
    waypoints.forEach(waypoint => {
        map.removeLayer(waypoint);
    });
    waypoints = [];
    validationMarkers.forEach(marker => {
        map.removeLayer(marker);
    });
    validationMarkers = [];

    if (mission !== null) {

        // control
        map.addControl(missionControl);
        var currentStep = mission.steps.filter(step => step.id == mission.current_step)[0];
        $('#mission-panel-title').text(decodeURI(mission.title));
        $('#mission-panel-poi-name').text(decodeURI(currentStep.poi.name));
        $('#mission-panel-caption').text(decodeURI(currentStep.caption));
        $('#mission-panel-rank').text(`${currentStep.rank + 1}/${mission.steps.length}`);
        $('#mission-panel-dist').text((mission.dist).toFixed(1));
        if (mission.dist < getDetectionRadiusNm()) {
            $('#mission-panel-validation-btn').css('display', 'inline');
        } else {
            $('#mission-panel-validation-btn').css('display', 'none');
        }

        mission.steps.sort((stepA, stepB) => {
            if (stepA.rank > stepB.rank) {
                return 1;
            }
            if (stepA.rank < stepB.rank) {
                return -1;
            }
            return 0;
        });

        // waypoints
        var wpts = mission.steps.map(step => step.poi).filter(poi => poi.type == 3);
        wpts.forEach(wpt => {
            var marker = L.marker([wpt.lat, wpt.lng]);
            marker._id = wpt.id;
            marker.setIcon(L.icon({
                iconUrl: '../../assets/poi_waypoint_icon.png',
                iconSize: [30, 30]
            }))
                .on('mouseover', function (event) {
                    if (areaCircle) {
                        map.removeLayer(areaCircle);
                    }
                    areaCircle = L.circle([wpt.lat, wpt.lng], {
                        color: 'blue',
                        weight: 0,
                        fillColor: '#4287f5',
                        fillOpacity: 0.5,
                        radius: getDetectionRadiusKm() * 1000
                    });
                    displayDelayerId = setTimeout(areaDisplayDelayer, 700);
                })
                .on('mouseout', function () {
                    map.removeLayer(areaCircle);
                    areaCircle = null;
                    clearTimeout(displayDelayerId);
                });
            waypoints.push(marker);
            marker.addTo(map);
        });

        //arrows
        var startCoeff = 0.1;
        var endCoeff = 0.9;
        for (let i = 0; i < mission.steps.length - 1; i++) {
            const startLat = mission.steps[i].poi.lat;
            const startLng = mission.steps[i].poi.lng;
            const endLat = mission.steps[i + 1].poi.lat;
            const endLng = mission.steps[i + 1].poi.lng;
            var arrow = L.swoopyArrow([startLat + startCoeff * (endLat - startLat), startLng + startCoeff * (endLng - startLng)], [startLat + endCoeff * (endLat - startLat), startLng + endCoeff * (endLng - startLng)], transitArrowOption);
            arrows.push(arrow);
            arrow.addTo(map);
        }

        // validation markers
        let stepValidated = false;
        for (let i = mission.steps.length - 1; i >= 0; i--) {
            let color;
            const step = mission.steps[i];
            if (step.id == mission.current_step) {
                stepValidated = true;
                color = '#50de09'
            } else {
                if (stepValidated) {
                    color = 'green';
                } else {
                    color = 'blue';
                }
            }
            var circle = L.circleMarker({ lat: step.poi.lat, lng: step.poi.lng }, { radius: 16, color: color });
            validationMarkers.push(circle);
            circle.addTo(map)
        }

    } else {
        map.removeControl(missionControl);
    }
}

function initData() {
    //get boat info
    $.post("../../backend/rest/dashboard.php", {
        action: "get-player-status",
        "app-token": getAppToken(),
        "boat-id": getBoatId()
    }, function (result, status) {
        if (result.success) {
            boat = result.boat;
            pois = result.pois;
            money = result.money;
            if (result.mission) {
                mission = result.mission;
            } else {
                mission = null;
            }
            updateDashBoard();
            updatePoiPanel();
            updateMissionPanel();
            displayBoats(true);
            displayPois();

            timeLeft = getTimeToNextSync() + 1;
            updateData();
            map.spin(false);
        } else {
            alert(result.message);
        }
    });
}

function updateData() {
    timeLeft--;
    updateTimeLeftLabel();
    if (timeLeft > 0) {
        setTimeout(updateData, 60 * 1000);
    } else {
        $.post("../../backend/rest/dashboard.php", {
            action: "get-player-status",
            "app-token": getAppToken(),
            "boat-id": getBoatId()
        }, function (result, status) {
            if (result.success) {
                timeLeft = 10;
                updateTimeLeftLabel();
                boat = result.boat;
                money = result.money;
                if (result.mission) {
                    mission = result.mission;
                } else {
                    mission = null;
                }
                updateDashBoard();
                updatePoiPanel();
                updateMissionPanel();
                displayBoats(false);
                setTimeout(updateData, 60 * 1000);
            } else {
                alert(result.message);
            }
        });
    }
}

function displayBoats(resize) {
    // remove previous map content
    if (userMarker) {
        map.removeLayer(userMarker);
        userMarker = null;
    }
    if (userPath) {
        map.removeLayer(userPath);
        userPath = null;
    }
    if (otherMarkers.length > 0) {
        otherMarkers.forEach(marker => map.removeLayer(marker));
        otherMarkers = [];
    }
    if (otherPaths.length > 0) {
        otherPaths.forEach(path => map.removeLayer(path));
        otherPaths = [];
    }

    // display player content
    let achenSvgString = "<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512'><path d='M444.52 3.52L28.74 195.42c-47.97 22.39-31.98 92.75 19.19 92.75h175.91v175.91c0 51.17 70.36 67.17 92.75 19.19l191.9-415.78c15.99-38.39-25.59-79.97-63.97-63.97z' fill='#" + boat.color.toString(16).padStart(6, "0") + "'/></svg>";
    let myIconUrl = encodeURI("data:image/svg+xml," + achenSvgString).replace('#', '%23');
    userMarker = L.marker([boat.trajectory[0].lat, boat.trajectory[0].lng])
        .setIcon(L.icon({
            iconUrl: myIconUrl,
            iconSize: [30, 30]
        }))
        .setRotationAngle(boat.heading - 45)
        .setRotationOrigin("center");
    userPath = L.polyline(boat.trajectory.map(elt => [elt.lat, elt.lng]));
    userPath.setStyle({
        color: `#${boat.color.toString(16).padStart(6, "0")}`
    });
    if (resize) {
        var group = new L.featureGroup([userPath]);
        map.fitBounds(group.getBounds());
    }
    userPath.addTo(map);
    userMarker.addTo(map);

    // display other users
    var others = otherBoats.filter(elt => elt.id != boat.id && elt.trajectory.length > 0);
    if (others.length > 0) {
        others.forEach(boat => {
            var popup = `<h5 style="color: #${boat.color.toString(16).padStart(6, "0")}">${decodeURI(boat.name)}</h5><span class="text-muted">${boat.type_name}</span><br><strong>Skipper: </strong> ${decodeURI(boat.skipper)}`;
            if (boat.mission) {
                popup += `<br><strong>Mission: </strong> ${decodeURI(boat.mission)}`;
            }
            let achenSvgString = "<svg xmlns='http://www.w3.org/2000/svg' width='512' height='512'><path d='M444.52 3.52L28.74 195.42c-47.97 22.39-31.98 92.75 19.19 92.75h175.91v175.91c0 51.17 70.36 67.17 92.75 19.19l191.9-415.78c15.99-38.39-25.59-79.97-63.97-63.97z' fill='#" + boat.color.toString(16).padStart(6, "0") + "' style=\"fill-opacity: .5;\"/></svg>";
            let myIconUrl = encodeURI("data:image/svg+xml," + achenSvgString).replace('#', '%23');
            var marker = L.marker([boat.trajectory[0].lat, boat.trajectory[0].lng])
                .setIcon(L.icon({
                    iconUrl: myIconUrl,
                    iconSize: [30, 30]
                }))
                .bindPopup(popup)
                .setRotationAngle(boat.heading - 45)
                .setRotationOrigin("center");
            otherMarkers.push(marker);
            marker.addTo(map);
            var path = L.polyline(boat.trajectory.map(elt => [elt.lat, elt.lng]));
            path.setStyle({
                color: `#${boat.color.toString(16).padStart(6, "0")}`,
                opacity: 0.5
            });
            otherPaths.push(path);
            path.addTo(map);
        });
    }
}

function displayPois() {
    var poisToDisplay = pois;
    if (mission !== null) {
        mission.steps.map(function (item) {
            return item.poi;
        }).forEach(poi => {
            if (!poisToDisplay.includes(poi)) {
                poisToDisplay.push(poi);
            }
        })
    }
    poisToDisplay.forEach(poi => {
        var marker = L.marker([poi.lat, poi.lng]);
        var iconUrl;
        var popupContent;
        switch (poi.type) {
            case 0:
                iconUrl = '../../assets/poi_harbour_icon.png';
                popupContent = getHarbourPopupContent(poi);
                break;
            case 1:
                iconUrl = '../../assets/poi_anchorage_icon.png';
                popupContent = getAnchoragePopupContent(poi);
                break;
            case 2:
                iconUrl = '../../assets/poi_oddity_icon.png';
                popupContent = getOddityPopupContent(poi);
                break;
            case 3:
                iconUrl = '../../assets/poi_waypoint_icon.png';
                break;
            default:
                alert("unrecognized poi type")
        }
        marker.setIcon(L.icon({
            iconUrl: iconUrl,
            iconSize: [30, 30]
        }))
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
                });
                displayDelayerId = setTimeout(areaDisplayDelayer, 700);
            })
            .on('mouseout', function () {
                map.removeLayer(areaCircle);
                areaCircle = null;
                clearTimeout(displayDelayerId);
            });
        marker.bindPopup(popupContent);
        // marker.on('popupopen', initPopupContent);
        marker.addTo(map);
    });
}

function getHarbourPopupContent(harbour) {
    var str = `<h6>${decodeURI(harbour.name)}</h6>`;
    str += '<ul style="list-style-type:none; padding-left: 1em;">';
    str += "<li>" + (harbour.provides_water ? "&#x2714" : "&#x274C") + " water</li>";
    str += "<li>" + (harbour.sells_food ? "&#x2714" : "&#x274C") + " food</li>";
    str += "<li>" + (harbour.sells_spare_parts ? "&#x2714" : "&#x274C") + " spare parts</li>";
    str += "<li>" + (harbour.dry_dock ? "&#x2714" : "&#x274C") + " dry dock</li>";
    str += "</ul>";
    return str;
}

function getAnchoragePopupContent(anchorage) {
    var str = `<h6>${decodeURI(anchorage.name)}</h6>`;
    return str;
}

function getOddityPopupContent(oddity) {
    var str = `<h6>${decodeURI(oddity.name)}</h6>`;
    if (oddity.caption != "") {
        str += `<p class="text-info"><i>${decodeURI(oddity.caption)}</i></p>`;
    }
    return str;
}

function requestMissionDisplay(missionId) {
    $.post("../../backend/rest/missions.php", {
        action: "get-mission-by-id",
        "mission-id": missionId,
        "app-token": getAppToken()
    }, function (result, status) {
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
        missionDisplay.onAdd = function (map) {
            var div = L.DomUtil.create('div', 'legend mission-overview');
            div.innerHTML += '<h5>' + decodeURI(mission.title) + '</h5>';
            div.innerHTML += '<small class="text-muted">by ' + decodeURI(mission.creator) + '</small>';
            div.innerHTML += '<div id="map-' + mission.id + '" style="height: 20vh;"><div>';
            div.innerHTML += '<p class="text-info">' + decodeURI(mission.caption) + '<p>';
            div.innerHTML += '<p class="text-secondary" style="margin-bottom: 0;">reward: ' + mission.reward + '</p>';
            div.innerHTML += '<p class="text-secondary">passengers: ' + mission.passengers + '</p>';
            div.innerHTML += '<div style="display: inline;">';
            div.innerHTML += '<button type="button" class="btn btn-sm btn-danger" onclick="$(this).parent().remove();">close</button>';
            div.innerHTML += '<button type="button" class="btn btn-sm btn-primary" style="margin-left: 0.3em;" onclick="requestStartMission(' + mission.id + ')">start</button>';
            div.innerHTML += '</div>';
            return div;
        }
        missionDisplay.addTo(map);

        setTimeout(function () {
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

            // init arrows
            var startLat = parseFloat(mission.steps[0].poi.lat);
            var startLng = parseFloat(mission.steps[0].poi.lng);
            var startCoeff = 0.1;
            var endCoeff = 0.9;
            mission.places.forEach(place => {
                // arrow
                var pos = { lat: place.lat, lng: place.lng };
                if (Math.abs(pos.lat - startLat) > 0.0001 && Math.abs(pos.lng - startLng) > 0.0001) {
                    var polyline = L.polyline([[startLat, startLng], [pos.lat, pos.lng]]);
                    var arrow = L.swoopyArrow([pos.lat + startCoeff * (startLat - pos.lat), pos.lng + startCoeff * (startLng - pos.lng)], [pos.lat + endCoeff * (startLat - pos.lat), pos.lng + endCoeff * (startLng - pos.lng)], beginArrowOption);
                    arrows.push(arrow);
                    mapItems.push(polyline);
                    arrow.addTo(map);
                }

                // marker
                switch (place.type) {
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
                var marker = L.marker([place.lat, place.lng]);
                marker.setIcon(L.icon({
                    iconUrl: iconPath,
                    iconSize: [30, 30]
                }));
                mapItems.push(marker);
                marker.addTo(map);
            });
            var group = new L.featureGroup(mapItems);
            map.fitBounds(group.getBounds());

        }, 200);
    }
}

function requestStartMission(missionId) {
    if (confirm('Do you really want to start this mission ?')) {
        $.post("../../backend/rest/dashboard.php", {
            action: "start-mission",
            "app-token": getAppToken(),
            "boat-id": boat.id,
            "mission-id": missionId
        }, function (result, status) {
            if (result.success) {
                mission = result.mission;
                boat = result.boat;
                updateMissionPanel();
                updateDashBoard();
            } else {
                alert(result.message);
            }
        });
    }
}

function requestCancelMission() {
    if (mission !== null) {
        if (confirm('Do you really want to cancel this mission ?')) {
            $.post("../../backend/rest/dashboard.php", {
                action: "cancel-mission",
                "app-token": getAppToken(),
                "boat-id": boat.id,
                "mission-id": mission.id
            }, function (result, status) {
                if (result.success) {
                    mission = null;
                    boat = result.boat;
                    updateMissionPanel();
                    updateDashBoard();
                } else {
                    alert(result.message);
                }
            });
        }
    }
}

function requestValidateStep() {
    if (mission !== null) {
        $.post("../../backend/rest/dashboard.php", {
            action: "validate-step",
            "app-token": getAppToken(),
            "boat-id": boat.id
        }, function (result, status) {
            if (result.success) {
                if (result.finished) {
                    mission = null;
                    money = result.money;
                } else {
                    mission = result.mission;
                }
                boat = result.boat;
                updateMissionPanel();
                updateDashBoard();
            } else {
                alert(result.message);
            }
        });
    }
}

function requestUseDryDock(poi) {
    if (mission === null) {
        if (confirm('Do you really want to use the dry dock ?')) {
            $.post("../../backend/rest/dashboard.php", {
                action: "use-dry-dock",
                "app-token": getAppToken(),
                "boat-id": boat.id,
                "poi-id": poi.id
            }, function (result, status) {
                if (result.success) {
                    boat = result.boat;
                    updateMissionPanel();
                    updateDashBoard();
                    updatePoiPanel();
                } else {
                    alert(result.message);
                }
            });
        }
    } else {
        alert("You cannot use a dry dock while doing a mission");
    }
}

function requestLeaveDryDock() {
    if (confirm("Do you really want to leave the dry dock ?")) {
        $.post("../../backend/rest/dashboard.php", {
            action: "leave-dry-dock",
            "app-token": getAppToken(),
            "boat-id": boat.id
        }, function (result, status) {
            if (result.success) {
                boat = result.boat;
                updateMissionPanel();
                updateDashBoard();
                updatePoiPanel();
            } else {
                alert(result.message);
            }
        });
    }
}

function updateTimeLeftLabel() {
    if (timeLeft === null) {
        $('#time-left-label').text('');
    } else {
        timeLeft--;
        $('#time-left-label').text(`next sync: ${timeLeft} min`);
    }
}

function otherPlayersOn() {
    $('#see-other-players').removeClass('fa-toggle-off');
    $('#see-other-players').addClass('fa-toggle-on');
    if (otherBoats.length == 0) {
        $.post("../../backend/rest/dashboard.php", {
            action: "get-other-players",
            "app-token": getAppToken()
        }, function (result, status) {
            if (result.success) {
                otherBoats = result.boats;
                displayBoats(false);
            } else {
                alert(result.message);
            }
        });
    } else {
        displayBoats(false);
    }
}

function otherPlayersOff() {
    $('#see-other-players').removeClass('fa-toggle-on');
    $('#see-other-players').addClass('fa-toggle-off');
    if (otherMarkers.length > 0) {
        otherMarkers.forEach(marker => map.removeLayer(marker));
        otherMarkers = [];
    }
    if (otherPaths.length > 0) {
        otherPaths.forEach(path => map.removeLayer(path));
        otherPaths = [];
    }
}

function areaDisplayDelayer() {
    areaCircle.addTo(map);
}