<?php
/**
 * @var $this AdminMenu
 */

use OctaviusRocks\AdminMenu;
use OctaviusRocks\Plugin;

$items = apply_filters(
	Plugin::FILTER_ADMIN_OVERVIEW_LINKS,
	array(
			// filter used in AdminMenu class
	)
);
?>

<div class="ocdb-wrapper">
	<h1 class="ocdb-title">Octavius Rocks</h1>
	<?php

	$grouped = array();
	$section = - 1;
	foreach ( $items as $index => $item ) {
		if ( $index % 3 == 0 ) {
			$section ++;
			$grouped[ $section ] = array();
		}
		$grouped[ $section ][] = $item;
	}

	foreach ( $grouped as $group ) {
		echo '<div class="ocdb-content ocdb-section-wrapper">';
		foreach ( $group as $item ) {
			$headline  = $item["headline"];
			$link      = $item["link"];
			$link_text = $item["link_text"];
			echo "<div class='ocdb-section ocdb-section--1by3'>";
			echo "<h2>$headline</h2>";
			echo "<a href='$link'>$link_text</a>";
			echo "</div>";
		}
		echo "</div>";
	}
	?>
</div>
