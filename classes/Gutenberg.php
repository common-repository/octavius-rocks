<?php


namespace OctaviusRocks;


use OctaviusRocks\OQL\Arguments;
use OctaviusRocks\OQL\Condition;
use OctaviusRocks\OQL\ConditionSet;
use OctaviusRocks\OQL\Field;

/**
 * @property Plugin plugin
 * @property bool enqueuedGutenberg
 */
class Gutenberg{
    /**
     * Gutenberg constructor.
     * @param Plugin $plugin
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->enqueuedGutenberg = false;
        add_action('init', [$this, 'init']);
    }

    public function isGutenberg(){
        return $this->enqueuedGutenberg;
    }

    public function init(){
        add_action( 'enqueue_block_editor_assets', function () {
            // backend only
            $this->enqueuedGutenberg = true;
            $this->plugin->assets->enqueueGutenbergJS();
        } );
        add_action( 'enqueue_block_assets', function () {
            // frontend and backend
        } );
    }

    function getPostPageviewsOQL(){
	    $args = Arguments::builder()
             ->addField(
                 Field::builder( Field::HITS )
                      ->setAlias( "hits" )
                      ->addOperation( Field::OPERATION_SUM )
             )
             ->addField(
                 Field::builder(Field::TIMESTAMP)->setAlias("timestamp")
             )
             ->setConditions(
                 ConditionSet::builder()
                             ->addCondition( Condition::builder( Field::CONTENT_ID, get_the_ID() ) )
                             ->addCondition( Condition::builder( Field::CONTENT_TYPE, get_post_type() ) )
	             ->addCondition(
	             	Condition::builder(
	             		Field::TIMESTAMP,
		                date("Y-m-d 00:00:00", time() - (6 * DAY_IN_SECONDS )))
	                         ->fieldIsGreaterThanOrEqualsValue()
	             )
             )
		    ->aggregation(Arguments::AGGREGATION_DAY);

	    return $args->get();
    }

    function getPostEventsOQL() {
        $args = Arguments::builder()
            ->addField(
                Field::builder( Field::HITS )
                    ->setAlias( "hits" )
                    ->addOperation( Field::OPERATION_SUM )
            )
            ->addField(
                Field::builder(Field::VIEWMODE)
            )->addField(
                Field::builder(Field::EVENT_TYPE)
            )
            ->setConditions(
                ConditionSet::builder()
                    ->addCondition( Condition::builder( Field::CONTENT_ID, get_the_ID() ) )
                    ->addCondition( Condition::builder( Field::CONTENT_TYPE, get_post_type() ) )
            )
            ->groupBy( [Field::EVENT_TYPE, Field::VIEWMODE] );

        return $args->get();
    }

    function getTopRefererPerPostOQL() {
        return array(
            "fields"     => array(
                "referer_domain",
                array(
                    "field"      => "hits",
                    "operations" => array(
                        "sum",
                    ),
                    "as"         => "hits",
                ),
            ),
            "conditions" => array(
                "entries"  => array(
                    array(
                        "field"   => "content_id",
                        "value"   => get_the_ID(),
                        "compare" => "=",
                    ),
                    array(
                        "field"   => "content_type",
                        "value"   => get_post_type(),
                        "compare" => "=",
                    ),
                    array(
                        "field"   => "referer_domain",
                        "value"   => "",
                        "compare" => "!=",
                    ),
                    array(
                        "entries"  => array(
                            array(
                                "field"   => "event_type",
                                "value"   => "pageview",
                                "compare" => "=",
                            ),
                            array(
                                "field"   => "event_type",
                                "value"   => "click",
                                "compare" => "=",
                            ),
                        ),
                        "relation" => "OR",
                    ),
                ),
                "relation" => "AND",
            ),
            "order_by"   => array(
                array(
                    "field"     => "hits",
                    "direction" => "DESC",
                ),
            ),
            "group_by"   => array( "referer_domain" ),
            "limit"      => 5,
            "page"       => 1,
        );
    }
}