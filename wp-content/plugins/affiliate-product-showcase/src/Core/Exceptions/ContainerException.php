<?php
/**
 * Container Exception
 *
 * Exception class for dependency injection container errors.
 *
 * @package AffiliateProductShowcase
 * @since 1.0.0
 */

namespace AffiliateProductShowcase\Core\Exceptions;

use Exception;
use Throwable;

/**
 * Class ContainerException
 *
 * Thrown when dependency injection container operations fail.
 */
class ContainerException extends Exception {

	/**
	 * Create exception for service not found.
	 *
	 * @param string $id Service identifier.
	 * @param int $code Exception code.
	 * @param Throwable|null $previous Previous exception.
	 * @return self
	 * @since 1.0.0
	 */
	public static function notFound( string $id, int $code = 0, ?Throwable $previous = null ): self {
		return new self(
			sprintf( 'Service "%s" not found in container.', $id ),
			$code,
			$previous
		);
	}

	/**
	 * Create exception for non-instantiable class.
	 *
	 * @param string $class Class name.
	 * @param int $code Exception code.
	 * @param Throwable|null $previous Previous exception.
	 * @return self
	 * @since 1.0.0
	 */
	public static function notInstantiable( string $class, int $code = 0, ?Throwable $previous = null ): self {
		return new self(
			sprintf( 'Class "%s" is not instantiable.', $class ),
			$code,
			$previous
		);
	}

	/**
	 * Create exception for resolution failure.
	 *
	 * @param mixed $concrete Service implementation.
	 * @param int $code Exception code.
	 * @param Throwable|null $previous Previous exception.
	 * @return self
	 * @since 1.0.0
	 */
	public static function cannotResolve( $concrete, int $code = 0, ?Throwable $previous = null ): self {
		$concreteType = is_object( $concrete ) ? get_class( $concrete ) : gettype( $concrete );
		return new self(
			sprintf( 'Cannot resolve service of type "%s".', $concreteType ),
			$code,
			$previous
		);
	}

	/**
	 * Create exception for parameter resolution failure.
	 *
	 * @param string $parameter Parameter name.
	 * @param int $code Exception code.
	 * @param Throwable|null $previous Previous exception.
	 * @return self
	 * @since 1.0.0
	 */
	public static function cannotResolveParameter( string $parameter, int $code = 0, ?Throwable $previous = null ): self {
		return new self(
			sprintf( 'Cannot resolve parameter "%s".', $parameter ),
			$code,
			$previous
		);
	}

	/**
	 * Create exception for reflection error.
	 *
	 * @param string $class Class name.
	 * @param string $message Error message.
	 * @param int $code Exception code.
	 * @param Throwable|null $previous Previous exception.
	 * @return self
	 * @since 1.0.0
	 */
	public static function reflectionError( string $class, string $message, int $code = 0, ?Throwable $previous = null ): self {
		return new self(
			sprintf( 'Reflection error for class "%s": %s', $class, $message ),
			$code,
			$previous
		);
	}
}
