<?php
/**
 * @var $current string current tab slug
 * @var $tabs array
 */
?>

<h2>Octavius Rocks › Einstellungen</h2>
<?php

echo '<h2 class="nav-tab-wrapper">';
foreach( $tabs as $tab => $name ){
    $class = ( $tab == $current ) ? ' nav-tab-active' : '';
    echo "<a class='nav-tab$class' href='?page=octavius-rocks-settings&tab=$tab'>$name</a>";

}
echo '</h2>';
