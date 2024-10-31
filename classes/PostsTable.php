<?php

namespace OctaviusRocks;


use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\OQL\Condition;
use OctaviusRocks\OQL\ConditionSet;
use OctaviusRocks\OQL\Field;

/**
 * @property Plugin plugin
 */
class PostsTable {

	const HANDLE_JS = "posts-pageviews";

	public function __construct(Plugin $plugin) {
		$this->plugin = $plugin;

		add_filter(Plugin::FILTER_DO_NOT_LOAD_SCRIPTS, array($this, 'do_not_load_scripts_overwrite'));
		add_action(Plugin::ACTION_ENQUEUE_ADMIN_SCRIPTS, array($this, 'enqueue_script'));

		add_action('init', function(){

			add_action( "manage_pages_custom_column" , array($this,'custom_columns'), 10, 2 );
			add_filter( 'manage_pages_columns' , array($this, 'add_column') );

			$types = get_post_types(array('public' => true));
			foreach ($types as $type){

				add_action( "manage_{$type}_posts_custom_column" , array($this,'custom_columns'), 10, 2 );
				add_filter( "manage_{$type}_posts_columns" , array($this, 'add_column') );

				add_filter( "manage_edit-{$type}_sortable_columns" , array($this, 'set_sortable') );
			}

		}, 99);
	}

	public function do_not_load_scripts_overwrite($doNotLoad){
		// function only exists in backend
		if(!function_exists('get_current_screen')) return $doNotLoad;
		$screen = get_current_screen();
		return $doNotLoad && (!is_admin() || $screen->base != 'edit');
	}

	public function enqueue_script(){
		$info = include $this->plugin->path."/dist/postsTable.asset.php";
		wp_enqueue_script(
			self::HANDLE_JS,
			$this->plugin->url . "/dist/postsTable.js",
			array_merge(array( Plugin::HANDLE_JS_ADMIN ), $info["dependencies"]),
			$info["version"],
			true
		);

		$isAmpPageviewCondition = ConditionSet::builder()
		    ->addConditionSet(
		        ConditionSet::builder()
                    ->addCondition(
                        Condition::builder(Field::EVENT_TYPE, "pageview_pixel")
                    )->addCondition(
                        Condition::builder(Field::EVENT_TYPE, "pageview")
                    )->useOr()
		    )
			->addCondition(
				Condition::builder(Field::VIEWMODE, "amp")
			)->useAnd();
		$isPageviewCondition = Condition::builder(Field::EVENT_TYPE, "pageview");
		$isAmpOrPageviewCondition = ConditionSet::builder()->addCondition(
			$isPageviewCondition
		)->addConditionSet(
			$isAmpPageviewCondition
		)->useOr();

		/**
		 * @var Arguments $oql
		 */
		$oql = Arguments::builder()
		                ->addField(Field::builder(Field::HITS)
		                                ->setAlias(Field::HITS)
		                                ->addOperation(Field::OPERATION_SUM)
		                )
		                ->addField(Field::builder(Field::CONTENT_ID))
						->addField(Field::builder(Field::EVENT_TYPE))
						->addField(Field::builder(Field::VIEWMODE))
		                ->setConditions(
			                ConditionSet::builder()
			                            ->addCondition(Condition::builder(Field::CONTENT_ID, array())->fieldIsInValues())
			                            ->addCondition(Condition::builder(
				                            Field::CONTENT_TYPE,
				                            (isset($_GET["post_type"]) && !empty($_GET["post_type"]))? sanitize_text_field($_GET["post_type"]): "post"
			                            ))
			                            ->addConditionSet($isAmpOrPageviewCondition)
		                )
		                ->groupBy([Field::CONTENT_ID, Field::EVENT_TYPE, Field::VIEWMODE]);

		$oql = apply_filters(Plugin::FILTER_POSTS_TABLE_ARGUMENTS, $oql);
		wp_localize_script(self::HANDLE_JS, 'Octavius_Rocks_Posts', array(
			"oql" => $oql->get(),
			"dashboard" => admin_url()."admin.php?page=".AdminMenu::MENU_SLUG,
		));
	}

	public function add_column($columns){

		$newCols = array();
		$added = false;
		foreach ($columns as $key => $label){
			if( !$added && ($key == "comments" || $key == "date") ){
				$added = true;
				$newCols['pageviews'] = __('Pageviews', Plugin::DOMAIN);
			}
			$newCols[$key] = $label;
		}

		// if to any reason there is no comments or date column add it to the last position
		if($added == false){
			$newCols['pageviews'] = __('Pageviews', Plugin::DOMAIN);
		}

		return $newCols;
	}
	public function custom_columns($column, $post_id){
		if($column == 'pageviews'){
			$pageviews = apply_filters(Plugin::FILTER_POSTS_TABLE_PAGE_VIEWS, $this->plugin->pageviews->getPostPageviews($post_id), $post_id);
			$postIds = apply_filters(Plugin::FILTER_POSTS_TABLE_POSTS_GROUP, [$post_id], $post_id);
			$formatted = number_format($pageviews, 0, ',', '.');
			$postIds = implode(",", $postIds);
			echo "<div class='octavius-rocks-pageview-column' data-post-id='$post_id' data-posts-group='$postIds' data-pageviews='$pageviews'><span data-rendered='true'>$formatted</span></div>";
			do_action(Plugin::ACTION_POSTS_TABLE_PAGE_VIEWS_COL, $post_id, $pageviews, $postIds);
		}
	}

	/**
	 * @param array[string]string $cols
	 *
	 * @return array[string]string
	 */
	public function set_sortable($cols){
		$cols['pageviews'] = array("pageviews",1);
		return $cols;
	}
}