<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';
?>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Sign up</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">
    <div class="row">
        <div class="col-md text-center"></div>
        <div class="col-md text-center " id="step-1">
            <h3>Step 1: find your sailaway account</h3>
            <form id="sw-username-form" action="" method="post">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">&#x26f5;</span>
                        </div>
                        <input type="text" class="form-control" name="username" placeholder="sailaway username" style="text-align:center;" required>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Step 2</button>
                </div>
            </form>
        </div>
        <div class="col-md text-center hidden" id="step-2">
            <h3>Step 2: fill out your personnal information</h3>
            <form id="register-form" action="../../backend/accounts.php" method="post">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="app-token" value="<?= $CVE_WEB_API_KEY ?>">
                <input type="hidden" name="username">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">@</span>
                        </div>
                        <input type="mail" class="form-control" name="email-1" placeholder="mail" style="text-align:center;" required>
                    </div>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">@</span>
                        </div>
                        <input type="mail" class="form-control" name="email-2" placeholder="mail confirmation" style="text-align:center;" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">&#128273;</span>
                        </div>
                        <input type="password" class="form-control" name="password-1" placeholder="password" style="text-align:center;" required>
                    </div>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">&#128273;</span>
                        </div>
                        <input type="password" class="form-control" name="password-2" placeholder="password confirmation" style="text-align:center;" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">&#x26f5;</span>
                        </div>
                        <select id="boat-id" name="boat-id" class="form-control"></select>
                    </div>
                    <p class="text-info">this list only contains boats used for the last 7 days</p>
                    <p class="text-info">only one boat can be used per player</p>
                    <p class="text-info">Go <a href="../boat_characteristics" target="_blank"><u>here</u></a> to see what boat to choose</p>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </div>
            </form>
            <div id="callback"></div>
        </div>
        <div class="col-sm text-center"></div>
    </div>
</div>

<script>
    var username;
    var boats;

    function displayErrors(errors) {
        var navbar = document.querySelector("nav.navbar");
        errors.forEach(error => {
            var div = document.createElement("div");
            div.classList = ["alert alert-danger alert-dismissible fade show"];
            div.role = "alert";
            if (navbar != null) {
                // display after navbar
                navbar.parentNode.insertBefore(div, navbar.nextSibling);
            } else {
                // display at the beginning of the body
                document.body.prepend(div);
            }
            div.innerHTML = error + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        });
    }

    function toStep2() {
        $("#step-1").addClass("hidden");
        $("#step-2").removeClass("hidden");
        boats.forEach(boat => {
            $("#boat-id").append(new Option(`${decodeURI(boat.name)} (${decodeURI(boat.type)})`, `${boat.id}-${boat.type_id}`));
        });
        $('#register-form input[name="username"]').val(username);
    }
    $("#sw-username-form").submit(function(event) {
        username = $('#sw-username-form input[name="username"]').val();
        event.preventDefault();
        $.post("../../backend/rest/get-sw-boats.php", {
            "username": encodeURI(username),
            "app-token": getAppToken()
        }, function(result, status) {
            if (result.success) {
                boats = result.boats;
                toStep2();
            } else {
                alert(result.message);
            }
        });
    });
    $("#register-form").submit(function(event) {
        var errors = [];
        if ($('#register-form input[name="password-1"]').val() != $('#register-form input[name="password-2"]').val()) {
            errors.push("passwords are different");
        } else if ($('#register-form input[name="password-1"]').val().length < 6) {
            errors.push("the password must contain almost 6 characters");
        }
        if ($('#register-form input[name="email-1"]').val() != $('#register-form input[name="email-2"]').val()) {
            errors.push("emails are different");
        }
        if (errors.length > 0) {
            event.preventDefault();
            displayErrors(errors);
        }
    });
</script>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>