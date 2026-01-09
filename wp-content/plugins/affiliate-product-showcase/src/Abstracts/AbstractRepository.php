<?php

namespace AffiliateProductShowcase\Abstracts;

use AffiliateProductShowcase\Interfaces\RepositoryInterface;

abstract class AbstractRepository implements RepositoryInterface {
	protected function parse_int( $value ): int {
		return (int) $value;
	}
}
