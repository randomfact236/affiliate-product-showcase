<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateProductShowcase\Interfaces\ServiceInterface;

abstract class AbstractService implements ServiceInterface {}
