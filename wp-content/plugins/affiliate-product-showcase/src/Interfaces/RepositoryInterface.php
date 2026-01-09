<?php

namespace AffiliateProductShowcase\Interfaces;

interface RepositoryInterface {
	public function find( int $id );

	public function list( array $args = [] ): array;

	public function save( object $model ): int;

	public function delete( int $id ): bool;
}
