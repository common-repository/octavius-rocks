<?php

namespace OctaviusRocks;

use OctaviusRocks\Model\TaxonomyTermViewsQueryArgs;

class REST extends Components\Component {

	const NAMESPACE = "octavius-rocks/v1";

	const ROUTE_TAXONOMY_TERM_VIEWS = "/taxonomy-term-views";

	public function onCreate() {
		parent::onCreate();
		add_action( 'rest_api_init', [$this, 'init']);
	}

	public function getTaxonomyTermViewsRoute(){
		return static::NAMESPACE.static::ROUTE_TAXONOMY_TERM_VIEWS;
	}

	public function init(){
		register_rest_route(
			static::NAMESPACE,
			static::ROUTE_TAXONOMY_TERM_VIEWS,
			[
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => function(){
					return current_user_can( 'manage_options' ) || WP_DEBUG;
				},
				'callback'            => function (\WP_REST_Request $request) {

					$args = TaxonomyTermViewsQueryArgs::build();
					$args->page($request->get_param("page"));
					if(!empty($request->get_param("taxonomy"))){
						$args->filterForTaxonomy($request->get_param("taxonomy"));
					}

					if(!empty($request->get_param("from"))){
						$args->from($request->get_param("from"));
					}
					if(!empty($request->get_param("until"))){
						$args->until($request->get_param("until"));
					}

					$views = $this->plugin->repo->getTaxonomyTermViews( $args );
					return array_map(function($item){
						$item->url = get_term_link($item->term->term_id);
						return $item;
					},$views);
				},
				'args' => array(
					'page' => array(
						'default' => 1,
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param ) && intval($param) > 0;
						}
					),
					'taxonomy' => array(
						'default' => "",
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function($param, $request, $key) {
							return is_string($param);
						}
					),
					'from' => array(
						'default' => "",
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function($param, $request, $key) {
							return empty($param) || strtotime($param);
						}
					),
					'until' => array(
						'default' => "",
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function($param, $request, $key) {
							return empty($param) || strtotime($param);
						}
					),
				),
			]
		);
	}



}