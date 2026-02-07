<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;
use Throwable;

/**
 * Base exception for repository operations
 * 
 * @package AffiliateProductShowcase\Exceptions
 */
class RepositoryException extends RuntimeException {
    /**
     * @var array<string, mixed>
     */
    private array $context = [];

    /**
     * Create a new repository exception
     *
     * @param string $message Error message
     * @param int $code Error code
     * @param Throwable|null $previous Previous exception
     * @param array<string, mixed> $context Additional context
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get exception context
     *
     * @return array<string, mixed>
     */
    public function getContext(): array {
        return $this->context;
    }

    /**
     * Create exception for invalid model type
     *
     * @param string $expected Expected model type
     * @param string $actual Actual model type
     * @return self
     */
    public static function invalidModelType(string $expected, string $actual): self {
        return new self(
            sprintf('Invalid model type. Expected: %s, Actual: %s', $expected, $actual),
            1001
        );
    }

    /**
     * Create exception for not found entity
     *
     * @param string $entity Entity type
     * @param int|string $id Entity ID
     * @return self
     */
    public static function notFound(string $entity, $id): self {
        return new self(
            sprintf('%s with ID "%s" not found.', $entity, $id),
            1002
        );
    }

    /**
     * Create exception for save failure
     *
     * @param string $entity Entity type
     * @param string $reason Failure reason
     * @return self
     */
    public static function saveFailed(string $entity, string $reason): self {
        return new self(
            sprintf('Failed to save %s: %s', $entity, $reason),
            1003
        );
    }

    /**
     * Create exception for delete failure
     *
     * @param string $entity Entity type
     * @param string $reason Failure reason
     * @return self
     */
    public static function deleteFailed(string $entity, string $reason): self {
        return new self(
            sprintf('Failed to delete %s: %s', $entity, $reason),
            1004
        );
    }

    /**
     * Create exception for query error
     *
     * @param string $entity Entity type
     * @param string $reason Failure reason
     * @return self
     */
    public static function queryError(string $entity, string $reason): self {
        return new self(
            sprintf('Query error for %s: %s', $entity, $reason),
            1005
        );
    }

    /**
     * Create exception for validation error
     *
     * @param string $field Field name
     * @param string $reason Failure reason
     * @return self
     */
    public static function validationError(string $field, string $reason): self {
        return new self(
            sprintf('Validation failed for field "%s": %s', $field, $reason),
            1006
        );
    }
}
