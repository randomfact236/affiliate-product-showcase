<?php
declare(strict_types=1);

namespace AffiliateProductShowcase\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface RepositoryInterface {
	public function find( int $id );

	public function list( array $args = [] ): array;

	public function save( object $model ): int;

	public function delete( int $id ): bool;
}
