<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'AppLogsManager.php';

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';

$appLogsManager = new AppLogsManager();
?>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Logs</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">
    <?php if (isset($_SESSION['auth'])) {
        if ($_SESSION['auth']->isAdmin()) {
            $logs = $appLogsManager->getStringLogs();
    ?>
            <p>
                Type something in the input field to find a log content:
            </p>
            <input class="form-control" id="search-input" type="text" placeholder="Search..">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            Date
                        </th>
                        <th>
                            Text
                        </th>
                    </tr>
                </thead>
                <tbody id="logs-body">
                    <?php
                    foreach ($logs as $log) {
                        echo '<tr><td>' . $log->date . '</td><td>' . rawurldecode($log->text) . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
            <script>
                $(document).ready(function() {
                    $("#search-input").on("keyup", function() {
                        var value = $(this).val().toLowerCase();
                        $("#logs-body tr").filter(function() {
                            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                        });
                    });
                });
            </script>
    <?php
        }
    } else {
        addFlashError('you dont have the permission to access this page');
        header('Location: ../home');
    } ?>
</div>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>