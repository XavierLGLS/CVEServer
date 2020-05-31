<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';
?>

<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Credits</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container">
    <input type="file" name="missions" id="missions-input">
    <button id="btn-update-rewards" class="btn btn-primary">Update mission rewards</button>
</div>
<script src="csv.js"></script>
<script>
    $(document).ready(function() {

        var fileInput = document.getElementById("missions-input");
        var fileReader = new FileReader();

        fileReader.addEventListener("loadstart", function() {
            //
        });

        fileReader.addEventListener("load", function() {
            try {
                var list = [];
                var fileContent = $.csv.toArrays(fileReader.result);
                fileContent.forEach(row => {
                    var obj = {};
                    obj.start_id = parseInt(row[0]);
                    obj.end_id = parseInt(row[1]);
                    obj.name = row[2];
                    obj.reward = parseInt(row[3]);
                    obj.passengers = parseInt(row[4]);
                    list.push(obj);
                });
                $.post("../../backend/rest/temp.php", {
                    action: "add-auto-generated-missions",
                    "app-token": getAppToken(),
                    "missions": JSON.stringify(list)
                }, function(result, status) {
                    console.log(result)
                    if (result.success) {
                        alert("ok");
                    } else {
                        alert(result.message);
                    }
                });
            } catch (error) {
                alert(error);
            }
        });

        fileInput.addEventListener("change", function() {
            fileReader.readAsText(this.files[0]);
        });

        $('#btn-update-rewards').click(function(){
            $.post("../../backend/rest/temp.php", {
                    action: "update-mission-rewards",
                    "app-token": getAppToken()
                }, function(result, status) {
                    console.log(result)
                });
        });

    });
</script>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>