<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractValidator {
	abstract public function validate( array $data ): array;
}
