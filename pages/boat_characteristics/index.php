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
    $str .= ('<td class=\"text-center\">' . $boat->name . '</td>');
    // food capacity
    $str .= "<td class=\"text-center\">$boat->food_capacity</td>";
    // water capacity
    $str .= "<td class=\"text-center\">$boat->water_capacity</td>";
    // spare parts capacity
    $str .= "<td class=\"text-center\">$boat->spare_parts_capacity</td>";
    // passengers capacity
    $str .= "<td class=\"text-center\">$boat->additional_passengers_capacity</td>";
    $str .= '</tr>';
    echo $str;
}

?>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Boat characteristics</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">
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
            </tr>
            <?php
            $boats = $sailawayBoatsManager->getBoatsCharacteristics();
            foreach ($boats as $boat) {
                displayABoat($boat);
            }
            ?>
        </table>
    </form>
</div>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>