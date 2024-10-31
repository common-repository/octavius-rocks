<?php

namespace OctaviusRocks;

use OctaviusRocks\AdminView\Api;
use OctaviusRocks\OQL\Condition;
use OctaviusRocks\OQL\ConditionSet;
use OctaviusRocks\OQL\Field;
use WP_Taxonomy;

/**
 * @property Plugin plugin
 */
class AdminMenu {

	const MENU_SLUG = "octavius-rocks";

	/**
	 * Settings constructor.
	 *
	 * @param Plugin $plugin
	 */
	function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
		add_action( 'admin_menu', array( $this, 'admin_init' ) );
        add_filter(
            Plugin::FILTER_QUERY_ATTRIBUTES_SKIP_TAXONOMY,
            array(
                $this,
                'skip_taxonomy'
            ),
            10, 2
        );
		new Api( $plugin );
	}

	public function isMenuPage(){
		return isset($_GET["page"])
	       &&
	       (
		       AdminMenu::MENU_SLUG == $_GET["page"]
		       ||
		       AdminMenu::MENU_SLUG . '-' . Settings::MENU_SLUG == $_GET["page"]
	       );
	}

	public function admin_init() {
		if($this->isMenuPage()){
			$this->plugin->assets->enqueueAdminScript([
			       "filterAttributes" => $this->getFilterAttributes()
            ]);
		}

		$dashboardCapability = apply_filters(Plugin::FILTER_DASHBOARD_CAPABILITY, 'manage_options');
		add_menu_page(
			__('Octavius Rocks', Plugin::DOMAIN),
			__('Octavius Rocks', Plugin::DOMAIN),
			$dashboardCapability,
			AdminMenu::MENU_SLUG,
			'',
			'dashicons-chart-bar',
			70
		);
		add_submenu_page(
			AdminMenu::MENU_SLUG,
			__('Dashboard ‹ Octavius Rocks', Plugin::DOMAIN),
			__('Dashboard', Plugin::DOMAIN),
			$dashboardCapability,
			AdminMenu::MENU_SLUG,
			array(
				$this,
				"render"
			)
		);

		// Add sub pages of extension plugins
		do_action(Plugin::ACTION_ADMIN_SUBMENU, AdminMenu::MENU_SLUG, $dashboardCapability);

		// last item should be settings
		add_submenu_page(
			AdminMenu::MENU_SLUG,
			__('Settings ‹ Octavius Rocks', Plugin::DOMAIN),
			__('Settings', Plugin::DOMAIN),
			'manage_options',
			AdminMenu::MENU_SLUG . '-' . Settings::MENU_SLUG,
			array(
				$this->plugin->settings,
				"render_octavius_settings"
			)
		);
	}

    /**
     * @param boolean $skip
     * @param WP_Taxonomy $taxonomy
     * @return bool
     */
    public function skip_taxonomy($skip, $taxonomy){
        if($skip) return true;
        $count = wp_count_terms($taxonomy->name,["hide_empty"=>false]);
        return  $skip || 'post_format' == $taxonomy->name || $count > 300;
    }

	function getFilterAttributes(){
        $filterAttributes = [];

        // TODO: breakpoints, event types

        // -------------------------
        // content types
        // -------------------------
        $prefix = 'content_type->';
        $group = __("Content Type", Plugin::DOMAIN);
        $filterAttributes[] = [
            'condition' => [
                Condition::builder(Field::CONTENT_TYPE, 'posts_homepage')->get()
            ],
            'id' => 	$prefix."posts_homepage",
            'title' => __("Homepage", Plugin::DOMAIN),
            'group' => $group,
        ];
        $filterAttributes[] = [
            'condition' => [
                Condition::builder(Field::CONTENT_TYPE, "author")->get()
            ],
            'id' => 	$prefix."author",
            'title' => __("Author", Plugin::DOMAIN),
            'group' => $group,
        ];
        $filterAttributes[] = [
            'condition' => [
                Condition::builder(Field::CONTENT_TYPE, "term")->get()
            ],
            'id' => 	$prefix."term",
            'title' => __("Term", Plugin::DOMAIN),
            'group' => $group,
        ];
        $filterAttributes[] = [
            'condition' => [
                Condition::builder(Field::CONTENT_TYPE, "archive")->get()
            ],
            'id' => 	$prefix."archive",
            'title' => __("Archive", Plugin::DOMAIN),
            'group' => $group,
        ];
        $filterAttributes[] = [
            'condition' => [
                Condition::builder(Field::CONTENT_TYPE, "search")->get()
            ],
            'id' => 	$prefix."search",
            'title' => __("Search", Plugin::DOMAIN),
            'group' => $group,
        ];
        $input = get_post_types( array("public" => true), 'objects' );
        foreach ( $input as $post_type => $info ) {
            $filterAttributes[] = array(
                'id' => $prefix.$post_type,
                'title' => $info->labels->name,
                'condition' => [
                    Condition::builder(Field::CONTENT_TYPE, $post_type)->get()
                ],
                'field' => Field::CONTENT_TYPE,
                'group' => $group,
            );
        }

        // -------------------------
        // taxonomies
        // -------------------------
        $taxonomies = get_taxonomies( array(
            'public' => true,
        ), 'object' );

        foreach ( $taxonomies as $tax ) {
            /**
             * post format is a special case so ignore
             */

            if ( apply_filters(Plugin::FILTER_QUERY_ATTRIBUTES_SKIP_TAXONOMY, false, $tax) ) {
                continue;
            }

            $terms = get_terms([
                'taxonomy' => $tax->name,
            ]);

            foreach ($terms as $term){
                /**
                 * add taxonomy to content structure
                 */
                $set = ConditionSet::builder()
                    ->addCondition(Condition::builder(Field::TAG1, $tax->name."/".$term->term_id))
                    ->addCondition(Condition::builder(Field::TAG2, $tax->name."/".$term->term_id))
                    ->addCondition(Condition::builder(Field::TAG3, $tax->name."/".$term->term_id))
                    ->addCondition(Condition::builder(Field::TAG5, $tax->name."/".$term->term_id))
                    ->addCondition(Condition::builder(Field::TAG6, $tax->name."/".$term->term_id))
                    ->addCondition(Condition::builder(Field::TAG7, $tax->name."/".$term->term_id))
                    ->addCondition(Condition::builder(Field::TAG8, $tax->name."/".$term->term_id))
                    ->addCondition(Condition::builder(Field::TAG9, $tax->name."/".$term->term_id))
                    ->addCondition(Condition::builder(Field::TAG10, $tax->name."/".$term->term_id))
                    ->useOr();
                $filterAttributes[] = array(
                    'id'   => $tax->name."/".$term->term_id,
                    'group' => htmlspecialchars_decode($tax->label),
                    'title' => htmlspecialchars_decode($term->name),
                    'condition' => $set->get(),
                );
            }
        }

        return $filterAttributes;
    }

	function render() {
		// everything else in JS
		?>
		<style>
			#wpcontent{padding:0}
			#octavius-rocks-loading{
				text-align: center;
				padding-top: 120px;
				font-size: 4rem;
				line-height: 4.8rem;
				color: #009D9C;
				text-shadow: 2px 2px 0px rgba(0,0,0,0.2);
			}
		</style>
		<div id='octavius-rocks-dashboard'>
			<div id="octavius-rocks-loading">
				<?php _ex(
						"Dashboard is loading...",
						"Dashboard waiting for javascript",
						Plugin::DOMAIN
				); ?>
			</div
		</div>
		<?php
	}

}
