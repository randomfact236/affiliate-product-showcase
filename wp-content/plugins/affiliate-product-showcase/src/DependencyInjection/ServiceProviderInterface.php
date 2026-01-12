<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\DependencyInjection;

/**
 * Interface for Service Providers
 * 
 * @package AffiliateProductShowcase\DependencyInjection
 */
interface ServiceProviderInterface {
    /**
     * Register services with the container
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function register(ContainerInterface $container): void;
}
