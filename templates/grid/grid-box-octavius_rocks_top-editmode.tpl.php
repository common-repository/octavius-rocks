<?php
/**
 * @author Palasthotel <rezeption@palasthotel.de>
 * @copyright Copyright (c) 2014, Palasthotel
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @package OctaviusRocks
 *
 * @var \grid_octavius_rocks_top_box $this
 * @var object $content
 */

?>
<div class="grid-box-editmode">
    <div class="content">
	    Octavius Rocks Top
	    <?php
	    if(isset($content->count)){
	    	echo "<br>".$content->count." contents ";
	    }
	    if(isset($content->period)){
	    	echo "<br>of the last ".$content->period." days";
	    }
	    if(isset($content->offset) && $content->offset > 0){
	    	echo "<br>skip the first ".$content->offset." contents";
	    }
	    if(isset($content->viewmode) && !empty($content->viewmode)){
	    	echo "<br>Viewmode: ".$content->viewmode;
	    }
	    $conditions = array();
	    if(!empty($content->category_id)){
		    /**
		     * @var \WP_Term $cat
		     */
	    	$cat = get_category($content->category_id);
	    	$conditions[] = $cat->name;
	    }
	    foreach ($this->content as $key => $value) {
		    // if not tax field or has no value
		    if ( '' == $value || strpos( $key, "tax_" ) !== 0 ) {
			    continue;
		    }
		    $taxonomy = str_replace( "tax_", "", $key );
		    $term_id = $value;
		    $tax = get_taxonomy($taxonomy);
		    $term = get_term($term_id, $taxonomy);
		    $conditions[] = $tax->label.": ".$term->name;
	    }
	    if(!empty($content->post_type)){
	    	$obj = get_post_type_object($content->post_type);
	    	$conditions[] = $obj->labels->singular_name;
	    }
	    if(count($conditions)){
	    	echo "<br>";
		    echo implode(" <br><i>".$content->relation."</i><br> ", $conditions );
	    }

	    ?>
    </div>
</div>
