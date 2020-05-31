<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AccountsManager.php';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';

$accountsManager = new AccountsManager();
if(!isset($_SESSION['auth'])){
    if(isset($_GET['user_id']) && isset($_GET['token'])){
        $id = intval($_GET['user_id']);
        $token = htmlspecialchars($_GET['token']);
        $user = $accountsManager->getUserFromId($id);
        $status = $accountsManager->checkPasswordToken($user, $token);
        if($status === "success"){
            $accountsManager->connectUser($user);
        }else{
            addFlashError($status);
            header('Location: ../home');
        }
    }else{
        //
    }
}

if (isset($_SESSION['auth'])) {
?>
    <div style="padding: 0.5em; background-color: #EEEEEE;">
        <h3>Set <strong><?= $_SESSION['auth']->getUsername() ?></strong> new password</h3>
    </div>
    <hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
    <div class="container content-wrapper">
        <div class="row">
            <div class="col-md text-center"></div>
            <div class="col-md text-center">
                <form id="password-change" action="../../backend/accounts.php" method="post">
                    <input type="hidden" name="action" value="password-reset">
                    <input type="hidden" name="app-token" value="<?= $CVE_WEB_API_KEY ?>">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">&#128273;</span>
                            </div>
                            <input type="password" class="form-control" name="password-1" placeholder="password" style="text-align:center;" required>
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">&#128273;</span>
                            </div>
                            <input type="password" class="form-control" name="password-2" placeholder="password confirmation" style="text-align:center;" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Set new password</button>
                    </div>
                </form>
            </div>
            <div class="col-md text-center"></div>
        </div>
    </div>

    <script>
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
        $("#password-change").submit(function(event) {
            var errors = [];
            if ($('#password-change input[name="password-1"]').val() != $('#password-change input[name="password-2"]').val()) {
                errors.push("passwords are different");
            } else if ($('#password-change input[name="password-1"]').val().length < 6) {
                errors.push("the password must contain almost 6 characters");
            }
            if (errors.length > 0) {
                event.preventDefault();
                displayErrors(errors);
            }
        });
    </script>
<?php
} else {
?>
    <div class="container content-wrapper"></div>
<?php } ?>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>