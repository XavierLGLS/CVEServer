var map;
// Current new position
var selectionMode;
var selectedHarboursNumber = 0;
var storedHarbourNumber;
var currentHarbour;
// Current polygon
var currentPolygon;
// Harbours that are stored in the database
var harbours = [];
// Post requests
var harboursBeingSent = {
    batchSize: 500
};

class SelectionPolygon {
    constructor(map) {
        this._polygon = new google.maps.Polygon({
            paths: [],
            draggable: true, // turn off if it gets annoying
            editable: true,
            strokeColor: "#00FF00",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#00FF00",
            fillOpacity: 0.35
        });
        this._polygon.setMap(map);
        this._markers = [];
    }

    addCorner(latLng) {
        this._markers.push(latLng)
        this._polygon.setPath(this._markers);
    }

    selectContainedHarbours() {
        harbours.forEach(harbour => {
            if (google.maps.geometry.poly.containsLocation(harbour.position, this._polygon)) {
                harbour.select();
            }
        });
    }

    unselectContainedHarbours() {
        harbours.forEach(harbour => {
            if (google.maps.geometry.poly.containsLocation(harbour.position, this._polygon)) {
                harbour.unselect();
            }
        });
    }

    reset() {
        this._markers = [];
        this._polygon.setPath([]);
    }
}

class Harbour {
    /**
     * 
     * @param {*} map 
     * @param {string} type "background, current"
     */
    constructor(map, latLng, id, name) {
        this._selected = false;
        this._id = id;
        this._name = name;
        this._marker = new google.maps.Marker({
            map: map,
            position: latLng,
            icon: storedHarbour_icon,
            parent: this
        });
        if (id === undefined) { // current harbour
            this._marker.setIcon(currentHarbour_icon);
            this._marker.addListener('dblclick', function () {
                this.setVisible(false);
                $('#add-form input[name="lat"]').val('');
                $('#add-form input[name="lng"]').val('');
            });
        } else {// stored harbour
            this._marker.setTitle(name);
            this._marker.addListener("click", function () {
                if (this.parent.selected) {
                    this.parent.unselect();
                } else {
                    this.parent.select();
                }
            });
        }
    }

    select() {
        if (!this._selected) {
            this._marker.setIcon(selectedHarbour_icon);
            this._selected = true;
            selectedHarboursNumber++;
            updateSelectNbreDisplay();
        }
    }

    unselect() {
        if (this._selected) {
            this._marker.setIcon(storedHarbour_icon);
            this._selected = false;
            selectedHarboursNumber--;
            updateSelectNbreDisplay();
        }
    }

    hide() {
        this._marker.setVisible(false);
        this.unselect();
    }

    set position(value) {
        this._marker.setVisible(true);
        this._marker.setPosition(value);
    }

    get id() {
        if (this._id === undefined) {
            return null;
        }
        return this._id;
    }

    get name() {
        if (this._name === undefined) {
            return "unknown";
        }
        return this._name;
    }

    get position() {
        return this._marker.position;
    }

    get selected() {
        return this._selected;
    }
}

/**
 * ======================================================================================
 *                             SETUP
 * ======================================================================================
 */

function initMenus() {

    function setHarbourCreationMenu() {
        $(".visible-when-polygon-selection").each(function () {
            hide(this);
        });
        $(".visible-when-harbour-creation").each(function () {
            reveal(this);
        });
    }

    function setPolygonSelectionMenus() {
        $(".visible-when-polygon-selection").each(function () {
            reveal(this);
        });
        $(".visible-when-harbour-creation").each(function () {
            hide(this);
        });
    }

    $("#harbour-creation").click(function () {
        setHarbourCreationMenu();
        selectionMode = "harbour-creation";
        resetPolygon();
    });
    $("#polygon-selection").click(function () {
        setPolygonSelectionMenus();
        selectionMode = "polygon-selection";
        resetCurrentHarbour();
    });
    setHarbourCreationMenu();
    selectionMode = "harbour-creation";
}

function initMap() {
    //map configuration
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 35.6, lng: -40.9 },
        zoom: 3
    });
    currentPolygon = new SelectionPolygon(map);
    map.addListener('dblclick', function (e) {
        switch (selectionMode) {
            case "harbour-creation":
                var inputLat = document.querySelector('input[name="lat"]');
                if (inputLat != null) {
                    inputLat.value = Math.round(1000 * e.latLng.lat()) / 1000;
                }
                var inputLng = document.querySelector('input[name="lng"]');
                if (inputLng != null) {
                    inputLng.value = Math.round(1000 * e.latLng.lng()) / 1000;
                }
                if (currentHarbour == null) {
                    currentHarbour = new Harbour(map, e.latLng);
                } else {
                    currentHarbour.position = e.latLng;
                }
                break;
            case "polygon-selection":
                currentPolygon.addCorner(e.latLng);
                break;
        }
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

function displayStoredHarbours(list) {
    for (var i = 0; i < list.length; i++) {
        var harbour = list[i];
        var newHarbour = new Harbour(map, { lat: parseFloat(harbour["lat"]), lng: parseFloat(harbour["lng"]) }, parseInt(harbour["harbour_id"]), harbour["name"]);
        harbours.push(newHarbour);
    }
}

/**
 * ======================================================================================
 *                             SEND REQUEST
 * ======================================================================================
 */

function sendHarbours_init(list, sender) {
    harboursBeingSent.totalSize = list.length;
    harboursBeingSent.batchsSent = -1;
    harboursBeingSent.dataToSend = list;
    harboursBeingSent.sender = sender;
    this.sendHarbours_onSendSuccess(null, "init");
}

function sendHarbours_sendBatch(batch) {
    var json = {};
    json.user_id = $('#add-form input[name="user_id"]').val();
    json.list = batch;
    $.post("handler.php", {
        request: "add",
        data: json
    }, sendHarbours_onSendSuccess
    );
}

function sendHarbours_onSendSuccess(result, status) {
    if (status == "success" || status == "init") {
        harboursBeingSent.batchsSent++;
        if (harboursBeingSent.batchsSent > 0) {
            var ids = result.replace(/[\"\[\]]/g, "").split(",");
            storedHarbourNumber += ids.length;
            updatedStoredNbreDisplay();
            harboursBeingSent.lastBatch.forEach(point => {
                var newHarbour = new Harbour(map, { lat: parseFloat(point.lat), lng: parseFloat(point.lng) }, parseInt(ids.shift()), point.name);
                harbours.push(newHarbour);
            });
        }
        if (harboursBeingSent.dataToSend.length == 0) {
            switch (harboursBeingSent.sender) {
                case "csv":
                    displayProgress(1);
                    document.getElementById("file-name-display").value = "import done";
                    setTimeout(function () {
                        document.getElementById("file-name-display").value = "";
                    }, 3000);
                    break;
                case "form":
                    currentHarbour.hide();
                    $('#add-form')[0].reset();
                    break;
            }
        } else {
            displayProgress(harboursBeingSent.batchsSent * harboursBeingSent.batchSize / harboursBeingSent.totalSize)
            //little hack for UI update
            setTimeout(function () {
                var batch = harboursBeingSent.dataToSend.splice(0, (harboursBeingSent.dataToSend.length > harboursBeingSent.batchSize ? harboursBeingSent.batchSize : harboursBeingSent.dataToSend.length));
                harboursBeingSent.lastBatch = batch;
                sendHarbours_sendBatch(batch);
            }, 0);
        }
    }
}

function displayProgress(progress) {
    var container = document.querySelector("#send-progress");
    var progressBar = document.querySelector("#send-progress .progress-bar");
    if (progress < 0) {
        hide(container);
    } else {
        if (progress > 1) {
            progress = 1;
        }
        reveal(container);
        progressBar.style.width = Math.round(100 * progress) + "%";
        progressBar.innerHTML = Math.round(100 * progress) + "%";
        if (progress >= 1) {
            setTimeout(function () {
                hide(container);
            }, 3000);
        }
    }
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
        try {
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
            sendHarbours_init(list, "csv");
        } catch (error) {
            alert(error);
        }
    });

    fileInput.addEventListener("change", function () {
        fileReader.readAsText(this.files[0]);
    });
}

function hide(elt) {
    elt.classList.add("hidden");
}

function reveal(elt) {
    elt.classList.remove("hidden");
}

$(document).ready(function () {
    initMenus();
    storedHarbourNumber = parseInt($("#harbour-nbre")[0].innerHTML);
    $('#add-form').submit(function (event) {
        var harbour = [{
            name: $('#add-form input[name="name"]').val(),
            lat: $('#add-form input[name="lat"]').val(),
            lng: $('#add-form input[name="lng"]').val()
        }];
        sendHarbours_init(harbour, "form");
        event.preventDefault();
    });

    $('#remove').click(function () {
        var confirmString = "Do you really want to remove";
        var ids = [];
        harbours.filter(harbour => harbour.selected).forEach(harbour => {
            confirmString += ("\n - " + harbour.name);
            ids.push(harbour.id);
        });
        if (ids.length == 0) {
            alert("Please select at least one harbour !");
        } else {
            if (confirm(confirmString)) {
                $.post("handler.php", {
                    request: "remove",
                    list: { list: JSON.stringify(ids) }
                }, function (result, status) {
                    if (status == "success") {
                        storedHarbourNumber -= harbours.filter(harbour => harbour.selected).length;
                        updatedStoredNbreDisplay();
                        harbours.filter(harbour => harbour.selected).forEach(harbour => {
                            harbour.unselect();
                            harbour.hide();
                        });
                    } else if (status == "timeout" || status == "error") {
                        console.log("error");
                    }
                });
            }
        }
    });

    csvImporterSetup();
});

function selectHarboursInPolygon() {
    if (currentPolygon != null) {
        currentPolygon.selectContainedHarbours();
    }
}

function unselectHarboursInPolygon() {
    if (currentPolygon != null) {
        currentPolygon.unselectContainedHarbours();
    }
}

function resetPolygon() {
    if (currentPolygon != null) {
        currentPolygon.reset();
    }
}

function resetCurrentHarbour() {
    if (currentHarbour != null) {
        currentHarbour.hide();
    }
}

function unselectAllHarbours() {
    harbours.forEach(harbour => {
        harbour.unselect();
    });
}

function exportCSV() {
    $.post("handler.php", {
        request: "get"
    }, function (result, status) {
        if (status == "success") {
            var csv = 'Latitude,Longitude,Name\n';
            var data = JSON.parse(result);
            for (var i = 0; i < data.length; i++) {
                var harbour = data[i];
                csv += (harbour["lat"] + ";" + harbour["lng"] + ";" + harbour["name"] + "\n");
            }
            var hiddenElement = document.createElement('a');
            hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);
            hiddenElement.target = '_blank';
            hiddenElement.download = 'harbours.csv';
            hiddenElement.click();
        } else if (status == "timeout" || status == "error") {
            console.log("error");
        }
    });
}

function updateSelectNbreDisplay() {
    var countDisp = $("#select-count .badge")[0];
    if (selectedHarboursNumber > 0) {
        reveal($("#select-count")[0]);
        countDisp.innerHTML = selectedHarboursNumber;
    } else {
        hide($("#select-count")[0]);
    }
}

function updatedStoredNbreDisplay() {
    $("#harbour-nbre").text(storedHarbourNumber);
}