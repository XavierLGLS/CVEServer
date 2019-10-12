<?php

require("../config.php");
require("../db.php");
$db = new DB();

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>CVE temporary website</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
        <h1>Complete voyaging experience</h1>
        <p class="text-warning">This is a work in progress (and temporary) website...</p>
        <h3>Harbour Picker <span class="label label-success">released</span></h3>
        <div class="text-center">
            <p class="text-muted">Almost <?php echo $db->getHarboursNumber(); ?> harbours are currently referenced !</p>
            <a href="harbour_picker/index.php">
                <button type="button" class="btn btn-primary">Go to the harbour picker page</button>
            </a>
        </div>
        <h3>User interface <span class="label label-danger">not developped yet</span></h3>
        <button type="button" disabled="true" class="btn btn-primary disable">Go to the user interface template</button>
        <h3>Admin panel <span class="label label-danger">not developped yet</span></h3>
        <button type="button" disabled="true" class="btn btn-primary disable">Go to the admin panel</button>
    </div>
</body>

</html>