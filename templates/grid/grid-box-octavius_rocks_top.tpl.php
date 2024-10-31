<?php
/**
 * @author Palasthotel <rezeption@palasthotel.de>
 * @copyright Copyright (c) 2014, Palasthotel
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @package OctaviusRocks
 *
 * @var object $content
 */

$classes = $this->classes;
array_push($classes, 'grid-box');

if ($this->style) {
	array_push($classes, $this->style);
}

if (!empty($this->title)) {
	array_push($classes, 'has-title');
}
if ( isset( $content->viewmode ) && ! empty( $content->viewmode ) ) {
	array_push( $classes, "viewmode-".$content->viewmode );
}

?>
<div class="<?php echo implode(' ', $classes); ?>">
	<?php if (!empty($this->title)): ?>
		<?php if (!empty($this->titleurl)): ?>
			<h2 class="grid-box-title"><a class="grid-box-title-link grid-box-title-text" href="<?php echo $this->titleurl; ?>"><?php echo $this->title; ?></a></h2>
		<?php else: ?>
			<h2 class="grid-box-title grid-box-title-text"><?php echo $this->title; ?></h2>
		<?php endif; ?>
	<?php endif; ?>

	<?php if (!empty($this->prolog)): ?>
		<div class="grid-box-prolog">
			<?php echo $this->prolog; ?>
		</div>
	<?php endif; ?>
	<ol><?php
		if(count($content->post_ids) > 0){
			$query = new WP_Query(array(
				"post__in" => $content->post_ids,
				"orderby" =>  "post__in",
				'post_type' => "any",
			));
			$i = 0;
			while ($query->have_posts()){
				$query->the_post();
				$hits = isset($content->hits[$i])? $content->hits[$i++]: 0;
				$permalink = get_the_permalink();
				$attributes = octavius_rocks_get_attributes(array(
					"content_id" => get_the_ID(),
					"content_type" => get_post_type(),
					"viewmode" => isset($content->viewmode) && !empty($content->viewmode) ? $content->viewmode : "octavius_rocks_simple_link",
				));
				echo "<li>";
				echo "<a $attributes href='$permalink'>";
				the_title();
				echo " [$hits]";
				echo "</a>";
				echo "</li>";
			}
			wp_reset_postdata();
		}
	?></ol>

	<?php if (!empty($this->epilog)): ?>
		<div class="grid-box-epilog">
			<?php echo $this->epilog; ?>
		</div>
	<?php endif; ?>

	<?php if (!empty($this->readmore)): ?>
		<a href="<?php echo $this->readmoreurl; ?>" class="grid-box-readmore-link"><?php echo $this->readmore; ?></a>
	<?php endif; ?>
</div>
