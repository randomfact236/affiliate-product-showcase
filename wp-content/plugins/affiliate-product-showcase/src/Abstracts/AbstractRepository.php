<?php

namespace AffiliateProductShowcase\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Interfaces\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface {
	protected function parse_int( $value ): int {
		return (int) $value;
	}
}
