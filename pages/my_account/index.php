<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AccountsManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SailawayDataManager.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'BoatsManager.php';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';

$accountsManager = new AccountsManager();
$swManager = new SailawayDataManager();
$boatsManager = new BoatsManager();

?>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>My account</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">
    <?php if (isset($_SESSION['auth'])) {
        $boat = $boatsManager->getBoatFromCVEUser($_SESSION["auth"]); ?>
        <table class="table">
            <tbody>
                <tr>
                    <td>
                        <strong>Sailaway username:</strong>
                    </td>
                    <td>
                        <?= rawurldecode($_SESSION['auth']->getUsername()) ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Boat:</strong>
                    </td>
                    <td>
                        <span><?= rawurldecode($boat->getLabel()); ?></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Color:</strong>
                    </td>
                    <td>
                        <input type="color" id="color-selector" value="#<?= dechex($boat->getColor()) ?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Email:</strong>
                    </td>
                    <td>
                        <?= $_SESSION['auth']->getEmail() ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Permission:</strong>
                    </td>
                    <td>
                        <?php
                        if ($_SESSION['auth']->isAdmin()) {
                            echo 'administrator ';
                        }
                        if ($_SESSION['auth']->hasPOIEditionPerm()) {
                            echo 'POIs editor ';
                        }
                        if ($_SESSION['auth']->hasPOIEditionPerm()) {
                            echo 'missions editor ';
                        }
                        if (!$_SESSION['auth']->isAdmin() && !$_SESSION['auth']->isContributor()) {
                            echo 'player';
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr>
        <div class="form-group">
            <form id="boat-change-form" action="../../backend/accounts.php" method="post">
                <input type="hidden" name="app-token" value="<?= $CVE_WEB_API_KEY; ?>">
                <input type="hidden" name="action" value="change-boat">
                <input type="hidden" name="user-id" value="<?= $_SESSION['auth']->getId() ?>">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">&#x26f5;</span>
                        </div>
                        <select id="boat-id" name="boat-id" class="form-control">
                            <?php
                            $currentBoatId = $boatsManager->getBoatFromCVEUser($_SESSION['auth'])->getSwId();
                            $boats = $swManager->getUserBoats($_SESSION['auth']);
                            foreach ($boats as $boat) {
                                if ($boat->id != $currentBoatId) {
                                    echo '<option value="' . $boat->id . '">' . rawurldecode($boat->name) . ' (' . rawurldecode($boat->type) . ')</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <p class="text-danger">Your score is going to be lost if you change your boat</p>
                <button type="submit" class="btn btn-primary">Change my boat</button><span class="text-info" style="margin-left: 1em;">Go <a href="../boat_characteristics" target="_blank"><u>here</u></a> to see what boat to choose</span>
            </form>
        </div>
        <hr>
        <div class="form-group">
            <a href="../password_change">
                <button type="submit" class="btn btn-primary">Set a new password</button>
            </a>
        </div>
        <div class="form-group">
            <form id="deletion-form" action="../../backend/accounts.php" method="post">
                <input type="hidden" name="app-token" value="<?= $CVE_WEB_API_KEY ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user-id" value="<?= $_SESSION['auth']->getId() ?>">
                <button type="submit" class="btn btn-danger">Delete my account</button>
            </form>
        </div>
        <script>
            $("#deletion-form").submit(function(event) {
                if (!confirm('Are you sure to delete your account ?\nAll informations about your progression are going to be lost !')) {
                    event.preventDefault();
                }
            });
            $("#boat-change-form").submit(function(event) {
                if (!confirm('Are you sure to change your boat ?\nAll informations about your progression are going to be lost !')) {
                    event.preventDefault();
                }
            });
            $('#color-selector').change(function() {
                const color = parseInt($(this).val().substring(1, 7), 16);
                $.post("../../backend/rest/account.php", {
                    action: "update-color",
                    "app-token": getAppToken(),
                    "boat-id": <?php
                    $boat = $boatsManager->getBoatFromCVEUser($_SESSION["auth"]);
                    echo $boat->getId();
                    ?>,
                    "color": color
                }, function(result, status) {
                    if (result.success) {
                        displayPois(result.pois);
                    } else {
                        alert(result.message);
                    }
                });
            });
        </script>
    <?php } ?>
</div>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>