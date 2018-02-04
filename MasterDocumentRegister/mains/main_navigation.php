<?php
$cur_file = basename($_SERVER["SCRIPT_FILENAME"], '.php');
$cur_file = $cur_file.'.php';
$files = array (
    array('index.php', 'Dashboard'),
    array('projects.php', 'Projects'),
    array('documents.php', 'Documents'),
    array('transmittals.php', 'Transmittals'),
    array('correspondence.php', 'Correspondence'),
    );
?>

<nav>
    <div class="nav">
        <ul>
            <?php
            foreach($files as $file){
                if($cur_file == $file[0]){
                    $class = ' class="active"';
                } else {
                    $class = '';
                };
                echo '<li><a href="'.$file[0].'"'.$class.'>'.$file[1].'</a></li>';
            };
            ?>
        </ul> 
    </div>
</nav>