<?php
/**
 * @var \OctaviusRocks\Widgets\MostReadWidget $this
 * @var array $args of widget instance
 * @var array $instance widget instance
 * @var string $title widget instance title
 * @var \OctaviusRocks\Widgets\Utils $utils
 * @var \WP_Query $query
 */

// before and after widget arguments are defined by themes
echo $args['before_widget'];
if ( ! empty( $title ) )
	echo $args['before_title'] . $title . $args['after_title'];

echo "<ol>";
while($query->have_posts()){
	$query->the_post();
	echo "<li>";
	$url = get_the_permalink();
	$title = get_the_title();
	echo "<a href='$url'>$title</a>";
	echo "</li>";
}
echo "</ol>";


echo $args['after_widget'];