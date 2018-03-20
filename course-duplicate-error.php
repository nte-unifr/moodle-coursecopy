<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/theme/unil/dashboard/styles.css" />
</head>
<body>
    <div id="ndd-amintool-overlay">
        <div>
            <h2>Errors</h2>
            <div>
                <ul>
<?php
    
    foreach ($errors as $error) {
        echo '<li>'.$error.'</li>';
    }
    
?>
                </ul>
            </div>
            <div>
                <p>You can now browse to:</p>
                <ul>
                    <li><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$importcourse->id; ?>">the original course</a></li>
<?php
    
    if (isset($newcourse)) {
    
?>
                    <li><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$newcourse['id']; ?>">the newly created course</a></li>
<?php
    
    }
    
?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>