<?php


namespace OctaviusRocks\BlockX;


use OctaviusRocks\OQL\Field;
use OctaviusRocks\Widgets\Utils;
use Palasthotel\WordPress\BlockX\Blocks\_BlockType;
use Palasthotel\WordPress\BlockX\Gutenberg;
use Palasthotel\WordPress\BlockX\Model\BlockId;
use Palasthotel\WordPress\BlockX\Model\ContentStructure;
use Palasthotel\WordPress\BlockX\Model\Option;
use Palasthotel\WordPress\BlockX\Widgets\Number;
use Palasthotel\WordPress\BlockX\Widgets\Select;
use Palasthotel\WordPress\BlockX\Widgets\TaxQuery;
use stdClass;

/**
 * @property Utils utils
 */
class MostReadBlock extends _BlockType {

	public function __construct() {
		$this->utils = new Utils();
	}

	public function id(): BlockId {
		return BlockId::build("octavius-rocks", "most-read");
	}

	public function category(): string {
		return "widgets";
	}

	public function title(): string {
		return "Most Read";
	}

	public function contentStructure(): ContentStructure {

		$cs = $this->utils->contentStructure();
		$cs = array_filter(array_map(function($item){
			if($item["type"] === "select"){
				return Select::build(
					$item["key"],
					$item["label"],
					array_map(function($pair){
						return Option::build($pair["key"], $pair["text"]);
					}, $item["selections"])
				);
			}
			return null;
		}, $cs), function($item){ return $item !== null; });

		return new ContentStructure(array_merge(
			[
				Number::build("count", "Count", 5),
				Number::build("offset", "Offset", 0),
			],
			$cs
		));

	}

	public function prepare( stdClass $content ): stdClass {
		$content = parent::prepare( $content );

		$this->utils->setContent($content);

		$items = $this->utils->fetch();

		$count  = isset( $content->count ) ? intval( $content->count ) : Utils::DEFAULT_COUNT;
		$offset = isset( $content->offset ) ? intval( $content->offset ) : Utils::DEFAULT_OFFSET;

		$content->items = ( $offset > 0 ) ? array_slice( $items, $offset, $count ) : $items;

		$content->post_ids = array_map(
			function ( $item ) {
				return $item[ Field::CONTENT_ID ];
			},
			$content->items
		);
		$content->hits     = array_map(
			function ( $item ) {
				return $item[ Field::HITS ];
			},
			$content->items
		);

		return $content;
	}
}