<?php

namespace OctaviusRocks\Model;

/**
 * @property null|string $taxonomy
 * @property null|string $term
 * @property int $numberOfElements
 * @property int $page
 * @property false|string $from
 * @property false|string $until
 */
class TaxonomyTermViewsQueryArgs {

	private function __construct() {
		$this->from = date("Y-m-d", strtotime("-30 days"));
		$this->until = date("Y-m-d");
		$this->numberOfElements = 50;
		$this->page = 1;
		$this->taxonomy = null;
		$this->term = null;
	}

	public static function build(): self{
		return new self();
	}

	public function from($from){
		$this->from = $from;
		return $this;
	}
	public function until($until){
		$this->until = $until;
		return $this;
	}

	public function filterForTaxonomy(string $taxonomySlug): self {
		$this->taxonomy = $taxonomySlug;
		return $this;
	}

	public function filterForTerm(string $termSlug): self {
		$this->term = $termSlug;
		return $this;
	}

	public function numberOfElements(int $number): self {
		$this->numberOfElements = ($number >= 1)? $number : 1;
		return $this;
	}

	public function page(int $page): self {
		$this->page = ( $page >= 1 ) ? $page : 1;
		return $this;
	}

}