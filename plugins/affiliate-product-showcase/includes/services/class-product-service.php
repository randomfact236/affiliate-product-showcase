<?php

namespace AffiliateProductShowcase\Services;

use AffiliateProductShowcase\Repositories\ProductRepository;

class ProductService {
	/** @var ProductRepository */
	private $repo;

	public function __construct( ProductRepository $repo = null ) {
		$this->repo = $repo ? $repo : new ProductRepository();
	}

	public function list_products() {
		return $this->repo->all();
	}
}
