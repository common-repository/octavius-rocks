<?php
/**
 * @var stdClass $content
 */

$post_ids = $content->post_ids;

$titles = [];
foreach ($post_ids as $index => $post_id){
	$titles[] = ($index+1).". ".get_the_title($post_id);
}

echo "<p><strong>Most Read</strong>:<br/>".implode("<br/>", $titles)."</p>";
