<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'SailawayBoatsManager.php';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';

$sailawayBoatsManager = new SailawayBoatsManager();


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
function displayABoat($boat)
{
    $str = '';
    $str .= '<tr>';
    $str .= ('<td>' . $boat->name . '</td>');
    // food capacity
    $str .= '<td><input type="number" class="form-control" style="width: 150px;" initial_value="' . $boat->food_capacity . '" value="' . $boat->food_capacity . '" name="food_capacity_' . $boat->type_id . '"></td>';
    // water capacity
    $str .= '<td><input type="number" class="form-control" style="width: 150px;" initial_value="' . $boat->water_capacity . '" value="' . $boat->water_capacity . '" name="water_capacity_' . $boat->type_id . '"></td>';
    // spare parts capacity
    $str .= '<td><input type="number" class="form-control" style="width: 150px;" initial_value="' . $boat->spare_parts_capacity . '" value="' . $boat->spare_parts_capacity . '" name="spare_parts_capacity_' . $boat->type_id . '"></td>';
    // passengers capacity
    $str .= '<td><input type="number" class="form-control" style="width: 150px;" initial_value="' . $boat->additional_passengers_capacity . '" value="' . $boat->additional_passengers_capacity . '" name="passengers_capacity_' . $boat->type_id . '"></td>';
    //max speed
    $str .= '<td><input type="number" class="form-control" style="width: 150px;" initial_value="' . $boat->max_speed . '" value="' . $boat->max_speed . '" name="max_speed_' . $boat->type_id . '"></td>';
    $str .= '</tr>';
    echo $str;
}

?>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Boat characteristics management</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">
    <?php if (isset($_SESSION['auth'])) {
        if ($_SESSION['auth']->isAdmin()) { ?>
            <form action="../../backend/boat_characteristics.php" method="post">
                <input type="hidden" name="app-token" value="<?php echo $CVE_WEB_API_KEY; ?>">
                <table class="table table-hover">
                    <tr>
                        <th>
                            Type
                        </th>
                        <th>
                            Food capacity (days)
                        </th>
                        <th>
                            Water capacity (days)
                        </th>
                        <th>
                            Spare parts capacity
                        </th>
                        <th>
                            Passengers capacity
                        </th>
                        <th>
                            Max speed (knots)
                        </th>
                    </tr>
                    <?php
                    $boats = $sailawayBoatsManager->getBoatsCharacteristics();
                    foreach ($boats as $boat) {
                        displayABoat($boat);
                    }
                    ?>
                </table>
                <button type="submit" class="btn btn-warning">Update characteristics</button>
            </form>
    <?php }
    } else {
        addFlashError('you dont have the permission to access this page');
        header('Location: ../home');
    } ?>
</div>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>