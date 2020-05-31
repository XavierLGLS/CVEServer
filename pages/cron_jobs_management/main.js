var boatCount = 0;

$().ready(init);

function init() {
    $('.toggle-btn').click(function () {
        if ($(this).hasClass('fa-toggle-off')) {
            $(this).removeClass('fa-toggle-off');
            $(this).addClass('fa-toggle-on');
        } else if ($(this).hasClass('fa-toggle-on')) {
            $(this).removeClass('fa-toggle-on');
            $(this).addClass('fa-toggle-off');
        }
        displaySwQueriesLabel();
    });

    $.post("../../backend/rest/cron-jobs-control.php", {
        "action": "get-settings",
        "app-token": getAppToken()
    }, function (result, status) {
        if (result.success) {
            initSettings(result.settings);
            $.post("../../backend/rest/cron-jobs-control.php", {
                "action": "get-stats",
                "app-token": getAppToken()
            }, function (result, status) {
                if (result.success) {
                    initStats(result);
                } else {
                    alert(result.message);
                }
            });
        } else {
            alert(result.message);
        }
    });

    $('.is-valid').on('input', function () {
        displayPeriodLabel(parseInt($(this).val()), $(this).attr('id').replace("period", "label"));
        displaySwQueriesLabel();
    });

    $('#submit-btn').click(function () {
        const damagePeriod = parseInt($('#damage-period').val());
        const weatherPeriod = parseInt($('#weather-period').val());
        const locationPeriod = parseInt($('#location-period').val());
        const foodPeriod = parseInt($('#food-period').val());
        const swAccouontsPeriod = parseInt($('#sw-accounts-period').val());
        const trajectoriesPeriod = parseInt($('#trajectories-period').val());
        const logsPeriod = parseInt($('#logs-period').val());

        if (isNaN(damagePeriod) || isNaN(weatherPeriod) || isNaN(locationPeriod) || isNaN(foodPeriod) || isNaN(swAccouontsPeriod) || isNaN(trajectoriesPeriod) || isNaN(logsPeriod)) {
            alert('At least one period is invalid !');
            return;
        }

        var settings = {
            damage_enabled: $('#damage-toggle').hasClass('fa-toggle-on') ? 1 : 0,
            damage_period: damagePeriod,
            weather_enabled: $('#weather-toggle').hasClass('fa-toggle-on') ? 1 : 0,
            weather_period: weatherPeriod,
            food_enabled: $('#food-toggle').hasClass('fa-toggle-on') ? 1 : 0,
            food_period: foodPeriod,
            location_enabled: $('#location-toggle').hasClass('fa-toggle-on') ? 1 : 0,
            location_period: locationPeriod,
            sw_accounts_enabled: $('#sw-accounts-toggle').hasClass('fa-toggle-on') ? 1 : 0,
            sw_accounts_period: swAccouontsPeriod,
            trajectories_enabled: $('#trajectories-toggle').hasClass('fa-toggle-on') ? 1 : 0,
            trajectories_period: trajectoriesPeriod,
            logs_enabled: $('#logs-toggle').hasClass('fa-toggle-on') ? 1 : 0,
            logs_period: logsPeriod
        };

        $.post("../../backend/rest/cron-jobs-control.php", {
            "action": "put-settings",
            "app-token": getAppToken(),
            "settings": settings
        }, function (result, status) {
            if (result.success) {
                alert("settings updated !");
            } else {
                alert(result.message);
            }
        });
    });
}

function initSettings(settings) {
    // food
    if (settings.food_update.enabled) {
        setToggleOn("food-toggle");
    } else {
        setToggleOff("food-toggle");
    }
    $('#food-period').val(settings.food_update.period);
    displayPeriodLabel(settings.food_update.period, 'food-label');

    // damage
    if (settings.damage_update.enabled) {
        setToggleOn("damage-toggle");
    } else {
        setToggleOff("damage-toggle");
    }
    $('#damage-period').val(settings.damage_update.period);
    displayPeriodLabel(settings.damage_update.period, 'damage-label');

    // location
    if (settings.location_update.enabled) {
        setToggleOn("location-toggle");
    } else {
        setToggleOff("location-toggle");
    }
    $('#location-period').val(settings.location_update.period);
    displayPeriodLabel(settings.location_update.period, 'location-label');

    // weather
    if (settings.weather_update.enabled) {
        setToggleOn("weather-toggle");
    } else {
        setToggleOff("weather-toggle");
    }
    $('#weather-period').val(settings.weather_update.period);
    displayPeriodLabel(settings.weather_update.period, 'weather-label');

    // sw accounts
    if (settings.sw_accounts_update.enabled) {
        setToggleOn("sw-accounts-toggle");
    } else {
        setToggleOff("sw-accounts-toggle");
    }
    $('#sw-accounts-period').val(settings.sw_accounts_update.period);
    displayPeriodLabel(settings.sw_accounts_update.period, 'sw-accounts-label');

    // trajectories
    if (settings.trajectories_update.enabled) {
        setToggleOn("trajectories-toggle");
    } else {
        setToggleOff("trajectories-toggle");
    }
    $('#trajectories-period').val(settings.trajectories_update.period);
    displayPeriodLabel(settings.trajectories_update.period, 'trajectories-label');

    // logs
    if (settings.logs_update.enabled) {
        setToggleOn("logs-toggle");
    } else {
        setToggleOff("logs-toggle");
    }
    $('#logs-period').val(settings.logs_update.period);
    displayPeriodLabel(settings.logs_update.period, 'logs-label');

    boatCount = settings.boat_count;
    displaySwQueriesLabel();
}

function setToggleOn(id) {
    $('#' + id).removeClass('fa-toggle-off');
    $('#' + id).addClass('fa-toggle-on');
}

function setToggleOff(id) {
    $('#' + id).removeClass('fa-toggle-on');
    $('#' + id).addClass('fa-toggle-off');
}

function displayPeriodLabel(totalMin, labelId) {
    var strItems = [];
    if (!isNaN(totalMin)) {
        var days = Math.floor(totalMin / (60 * 24));
        var hours = Math.floor((totalMin - 24 * 60 * days) / 60);
        var minutes = totalMin % 60;
        if (days != 0) {
            strItems.push(days + ' day' + (days > 1 ? 's' : ''));
            if (hours != 0 || minutes != 0) {
                strItems.push(hours + ' h');
            }
        } else if (hours != 0) {
            strItems.push(hours + ' h');
        }
        if (minutes != 0) {
            strItems.push(minutes + ' min');
        }
    }
    $('#' + labelId).text(strItems.join(' '));
}

function displaySwQueriesLabel() {
    const locationPeriod = parseInt($('#location-period').val());
    const weatherPeriod = parseInt($('#weather-period').val());
    const swAccountsPeriod = parseInt($('#sw-accounts-period').val());
    var data = [];
    var labels = [];
    if (!isNaN(locationPeriod) && !isNaN(weatherPeriod)) {
        var total = 0;
        if ($('#location-toggle').hasClass('fa-toggle-on')) {
            total += (24 * 60) / locationPeriod;
            data.push((24 * 60) / locationPeriod);
            labels.push('locations');
        }
        if ($('#weather-toggle').hasClass('fa-toggle-on')) {
            total += boatCount * (24 * 60) / weatherPeriod;
            data.push(boatCount * (24 * 60) / weatherPeriod);
            labels.push('weather');
        }
        if ($('#sw-accounts-toggle').hasClass('fa-toggle-on')) {
            total += (24 * 60) / swAccountsPeriod;
            data.push((24 * 60) / swAccountsPeriod);
            labels.push('sailaway accounts sync');
        }
        if (total > 1) {
            total = Math.floor(total);
        } else {
            total = total.toFixed(1);
        }
        $('#sw-queries-label').text(total + ' / day');
    } else {
        $('#sw-queries-label').text('');
    }
    updateSwChart(data, labels);
}

function initStats(stats) {
    function getDurationLabel(value) {
        if (value < 1) {
            return Math.round(1000 * value) + ' ms';
        } else {
            return Math.round(value) + ' s';
        }
    }

    // location
    if (isNaN(stats.last_dates.location) || stats.last_dates.location == null) {
        $('#location-last-exec').text('-');
    } else {
        $('#location-last-exec').text(getElapsedTimeLabel(stats.last_dates.location));
    }
    if (isNaN(stats.durations.location) || stats.durations.location == null) {
        $('#location-duration').text('-');
    } else {
        $('#location-duration').text(getDurationLabel(stats.durations.location));
        initProgressChart("location-progress-chart", $('#location-period').val(), stats.last_dates.location);
    }

    // weather
    if (isNaN(stats.last_dates.weather) || stats.last_dates.weather == null) {
        $('#weather-last-exec').text('-');
    } else {
        $('#weather-last-exec').text(getElapsedTimeLabel(stats.last_dates.weather));
    }
    if (isNaN(stats.durations.weather) || stats.durations.weather == null) {
        $('#weather-duration').text('-');
    } else {
        $('#weather-duration').text(getDurationLabel(stats.durations.weather));
    }
    initProgressChart("weather-progress-chart", $('#weather-period').val(), stats.last_dates.weather);

    // damage
    if (isNaN(stats.last_dates.damage) || stats.last_dates.damage == null) {
        $('#damage-last-exec').text('-');
    } else {
        $('#damage-last-exec').text(getElapsedTimeLabel(stats.last_dates.damage));
    }
    if (isNaN(stats.durations.damage) || stats.durations.damage == null) {
        $('#damage-duration').text('-');
    } else {
        $('#damage-duration').text(getDurationLabel(stats.durations.damage));
    }
    initProgressChart("damage-progress-chart", $('#damage-period').val(), stats.last_dates.damage);

    // food
    if (isNaN(stats.last_dates.food) || stats.last_dates.food == null) {
        $('#food-last-exec').text('-');
    } else {
        $('#food-last-exec').text(getElapsedTimeLabel(stats.last_dates.food));
    }
    if (isNaN(stats.durations.food) || stats.durations.food == null) {
        $('#food-duration').text('-');
    } else {
        $('#food-duration').text(getDurationLabel(stats.durations.food));
    }
    initProgressChart("food-progress-chart", $('#food-period').val(), stats.last_dates.food);

    // sw accounts
    if (isNaN(stats.last_dates.sw_accounts) || stats.last_dates.sw_accounts == null) {
        $('#sw-accounts-last-exec').text('-');
    } else {
        $('#sw-accounts-last-exec').text(getElapsedTimeLabel(stats.last_dates.sw_accounts));
    }
    if (isNaN(stats.durations.sw_accounts) || stats.durations.sw_accounts == null) {
        $('#sw-accounts-duration').text('-');
    } else {
        $('#sw-accounts-duration').text(getDurationLabel(stats.durations.sw_accounts));
    }
    initProgressChart("sw-accounts-progress-chart", $('#sw-accounts-period').val(), stats.last_dates.sw_accounts);

    // trajectories
    if (isNaN(stats.last_dates.trajectories) || stats.last_dates.trajectories == null) {
        $('#trajectories-last-exec').text('-');
    } else {
        $('#trajectories-last-exec').text(getElapsedTimeLabel(stats.last_dates.trajectories));
    }
    if (isNaN(stats.durations.trajectories) || stats.durations.trajectories == null) {
        $('#trajectories-duration').text('-');
    } else {
        $('#trajectories-duration').text(getDurationLabel(stats.durations.trajectories));
    }
    initProgressChart("trajectories-progress-chart", $('#trajectories-period').val(), stats.last_dates.trajectories);

    // logs
    if (isNaN(stats.last_dates.logs) || stats.last_dates.logs == null) {
        $('#logs-last-exec').text('-');
    } else {
        $('#logs-last-exec').text(getElapsedTimeLabel(stats.last_dates.logs));
    }
    if (isNaN(stats.durations.logs) || stats.durations.logs == null) {
        $('#logs-duration').text('-');
    } else {
        $('#logs-duration').text(getDurationLabel(stats.durations.logs));
    }
    initProgressChart("logs-progress-chart", $('#logs-period').val(), stats.last_dates.logs);
}

function updateSwChart(data, labels) {
    var ctx = document.getElementById('sw-chart').getContext('2d');
    var swChart = new Chart(ctx, {
        type: 'pie',
        data: {
            datasets: [{
                data: data,
                backgroundColor: [
                    '#3D9970',
                    '#0074D9',
                    '#FF4136'
                ]
            }],
            labels: labels
        },
        options: {
            responsive: true
        }
    });
}

function initProgressChart(id, period, elapsed) {
    if (period !== null && elapsed !== null) {
        $('#' + id).css('display', 'block');
        var ctx = document.getElementById(id).getContext('2d');
        data = [];
        colors = [];
        if (elapsed > period) {
            data.push(10);
            colors.push('#FF4136');
        } else {
            data.push(elapsed);
            colors.push('#2ECC40');
            data.push(period - elapsed);
            colors.push('#DDDDDD');
        }
        var progressChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: data,
                    backgroundColor: colors
                }]
            },
            options: {
                responsive: true,
                legend: {
                    display: false
                },
                tooltips: {
                    enabled: false
                }
            }
        });
    } else {
        $('#' + id).css('style', 'none');
    }
}

function getElapsedTimeLabel(value) {
    if (value >= 60) {
        var hour = Math.floor(value / 60);
        return `${hour} h ${value - hour * 60} min ago`;
    } else {
        return value + ' min ago';
    }
}