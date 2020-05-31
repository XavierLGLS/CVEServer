<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AccountsManager.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config_v2.php';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';

$accountsManager = new AccountsManager();

function getUserRow(CVEUser $user): string
{
    global $CVE_WEB_API_KEY;
    $color = '';
    if (!$user->isRegistered()) {
        $color = 'table-info';
    } else if ($user->isAdmin()) {
        $color = 'table-danger';
    } else if ($user->isContributor()) {
        $color = 'table-warning';
    }
    $output = '<tr class="' . $color . '"><td>' . $user->getEmail() . '</td>';
    $output .= '<td>' . rawurldecode($user->getUsername()) . '</td>';
    if ($user->isRegistered()) {
        $output .= '<td>' . $user->getRegistrationDate()->format("Y-m-d")  . '</td>';
        $periodString = "-";
        $inactiveTime = $user->getTimeSinceLastConnection();
        if ($inactiveTime !== NULL) {
            $inactivedays = intval($inactiveTime->format("%a day(s)"));
            $periodString = "today";
            if ($inactivedays > 1) {
                $periodString = "$inactivedays days ago";
            } else if ($inactivedays == 1) {
                $periodString = "yesterday";
            }
        }
        $output .= '<td>' . $periodString . '</td>';
        $output .= '<form action="../../backend/accounts.php" method="post">';
        $output .= '<input type="hidden" name="user-id" value="' . $user->getId() . '">';
        $output .= '<input type="hidden" name="app-token" value="' . $CVE_WEB_API_KEY . '">';
        $output .= '<input type="hidden" name="action" value="perm-edit">';
        $output .= '<td>' . '<input type="checkbox" ' . ($user->isAdmin() ? 'checked' : '') . ' disabled>' . '</td>';
        $output .= '<td>' . '<input type="checkbox" onchange="this.form.submit()" name="poi" ' . ($user->hasPOIEditionPerm() ? 'checked' : '') . '>' . '</td>';
        $output .= '<td>' . '<input type="checkbox" onchange="this.form.submit()" name="mission" ' . ($user->hasMissionEditionPerm() ? 'checked' : '') . '>' . '</td>';
        $output .= '</form>';
    } else {
        $output .= '<form action="../../backend/accounts.php" method="post" class="confirm-form" username="' . $user->getUsername() . '">';
        $output .= '<input type="hidden" name="user-id" value="' . $user->getId() . '">';
        $output .= '<input type="hidden" name="app-token" value="' . $CVE_WEB_API_KEY . '">';
        $output .= '<input type="hidden" name="action" value="manual-confirm">';
        $output .= '<td colspan="5"><i>Registration not confirmed yet </i><button type="submit" class="btn btn-info">Confirm registration</button></td>';
        $output .= '</form>';
    }
    $output .= '<form action="../../backend/accounts.php" method="post" class="delete-form" username="' . $user->getUsername() . '">';
    $output .= '<input type="hidden" name="user-id" value="' . $user->getId() . '">';
    $output .= '<input type="hidden" name="app-token" value="' . $CVE_WEB_API_KEY . '">';
    $output .= '<input type="hidden" name="action" value="admin-delete">';
    $output .= '<td><button type="submit" class="btn btn-danger">Delete</button></td>';
    $output .= '</form>';
    $output .= '</tr>';
    return $output;
}
?>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Users management</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">
    <?php if (isset($_SESSION['auth'])) : ?>
        <?php if ($_SESSION['auth']->isAdmin()) : ?>
            <p>
                Type something in the input field to search the table for username or emails:
            </p>
            <input class="form-control" id="search-input" type="text" placeholder="Search..">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Registered since</th>
                        <th>Last connection</th>
                        <th>Admin</th>
                        <th>POI editor</th>
                        <th>Mission editor</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="users-body">
                    <?php
                    $users = $accountsManager->getAllUsers();
                    foreach ($users as $user) :
                        echo getUserRow($user);
                    endforeach
                    ?>
                </tbody>
            </table>
        <?php endif ?>
    <?php else :
        addFlashError('you dont have the permission to access this page');
        header('Location: ../home');
    endif ?>
</div>

<script>
    $(document).ready(function() {
        $("#search-input").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#users-body tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        $(".confirm-form").submit(function(event) {
            if (!confirm('Do you want to confirm the registration of ' + $(this).attr("username") + ' ?')) {
                event.preventDefault();
            }
        });
        $(".delete-form").submit(function(event) {
            if (!confirm('Do you want to delete the ' + $(this).attr("username") + '\'s account ?')) {
                event.preventDefault();
            }
        });
    });
</script>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>