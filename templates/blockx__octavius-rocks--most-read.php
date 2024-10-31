<?php
/**
 * @var stdClass $content
 */

$post_ids = $content->post_ids;

?>
<div class="octavius-rocks__block--most-read">
	<h2>Most Read</h2>
	<?php

	if(count($post_ids) > 0){
		echo "<ol>";
		foreach ($post_ids as $index => $post_id){
			$link =  get_permalink($post_id);
			$title = get_the_title($post_id);
			echo "<li><a href='$link'>$title</a></li>";
		}
		echo "</ol>";
	} else {
		echo "<p><small>No data found</small></p>";
	}
	?>
</div>