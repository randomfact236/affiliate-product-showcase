<?php

namespace AffiliateProductShowcase\Abstracts;

abstract class AbstractValidator {
	abstract public function validate( array $data ): array;
}
