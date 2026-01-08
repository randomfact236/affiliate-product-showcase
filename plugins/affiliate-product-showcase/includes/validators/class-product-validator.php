<?php

namespace AffiliateProductShowcase\Validators;

class ProductValidator {
	public function validate( $data ) {
		return is_array( $data );
	}
}
