<?php
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'header.php';
?>
<div style="padding: 0.5em; background-color: #EEEEEE;">
    <h3>Reset your password</h3>
</div>
<hr class="text-warning" style="border: 2px solid; border-radius: 1px; margin-top: 0;">
<div class="container content-wrapper">
    <div class="row">
        <div class="col-md text-center"></div>
        <div class="col-md text-center">
            <form action="../../backend/accounts.php" method="post">
                <input type="hidden" name="action" value="password-reset-request">
                <input type="hidden" name="app-token" value="<?= $CVE_WEB_API_KEY ?>">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">@</span>
                        </div>
                        <input type="mail" class="form-control" name="email" placeholder="mail" style="text-align:center;" required>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Send reset request</button>
                </div>
            </form>
        </div>
        <div class="col-sm text-center"></div>
    </div>
</div>

<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'footer.php'; ?>