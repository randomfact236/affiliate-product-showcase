<?php

declare(strict_types=1);

namespace AffiliateProductShowcase\DependencyInjection;

use AffiliateProductShowcase\Services\ProductService;
use AffiliateProductShowcase\Services\AffiliateService;
use AffiliateProductShowcase\Services\AnalyticsService;
use AffiliateProductShowcase\Repositories\ProductRepository;
use AffiliateProductShowcase\Repositories\SettingsRepository;
use AffiliateProductShowcase\Assets\Assets;
use AffiliateProductShowcase\Assets\Manifest;
use AffiliateProductShowcase\Assets\SRI;
use AffiliateProductShowcase\Admin\Admin;
use AffiliateProductShowcase\Public\Public_;
use AffiliateProductShowcase\Cache\Cache;
use AffiliateProductShowcase\Database\Database;
use AffiliateProductShowcase\Validators\ProductValidator;

/**
 * Core Service Provider for Affiliate Product Showcase
 * 
 * Registers all core services with the DI container
 * 
 * @package AffiliateProductShowcase\DependencyInjection
 */
class CoreServiceProvider implements ServiceProviderInterface {
    /**
     * Register services with the container
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function register(ContainerInterface $container): void {
        $this->registerDatabaseServices($container);
        $this->registerRepositoryServices($container);
        $this->registerServiceServices($container);
        $this->registerAssetServices($container);
        $this->registerAdminServices($container);
        $this->registerPublicServices($container);
        $this->registerValidationServices($container);
        $this->registerCacheServices($container);
    }

    /**
     * Register database services
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function registerDatabaseServices(ContainerInterface $container): void {
        $container->register(Database::class, function(ContainerInterface $c) {
            return Database::get_instance();
        }, true);
    }

    /**
     * Register repository services
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function registerRepositoryServices(ContainerInterface $container): void {
        $container->register(ProductRepository::class, function(ContainerInterface $c) {
            return new ProductRepository();
        }, true);

        $container->register(SettingsRepository::class, function(ContainerInterface $c) {
            return new SettingsRepository();
        }, true);
    }

    /**
     * Register business logic services
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function registerServiceServices(ContainerInterface $container): void {
        $container->register(ProductService::class, function(ContainerInterface $c) {
            return new ProductService($c->get(ProductRepository::class));
        }, true);

        $container->register(AffiliateService::class, function(ContainerInterface $c) {
            return new AffiliateService($c->get(ProductService::class));
        }, true);

        $container->register(AnalyticsService::class, function(ContainerInterface $c) {
            return new AnalyticsService($c->get(ProductRepository::class));
        }, true);
    }

    /**
     * Register asset management services
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function registerAssetServices(ContainerInterface $container): void {
        $container->register(Manifest::class, function(ContainerInterface $c) {
            return Manifest::get_instance();
        }, true);

        $container->register(SRI::class, function(ContainerInterface $c) {
            return new SRI($c->get(Manifest::class), 120);
        }, true);

        $container->register(Assets::class, function(ContainerInterface $c) {
            return new Assets($c->get(Manifest::class));
        }, true);
    }

    /**
     * Register admin services
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function registerAdminServices(ContainerInterface $container): void {
        $container->register(Admin::class, function(ContainerInterface $c) {
            return new Admin();
        }, true);
    }

    /**
     * Register public-facing services
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function registerPublicServices(ContainerInterface $container): void {
        $container->register(Public_::class, function(ContainerInterface $c) {
            return new Public_();
        }, true);
    }

    /**
     * Register validation services
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function registerValidationServices(ContainerInterface $container): void {
        $container->register(ProductValidator::class, function(ContainerInterface $c) {
            return new ProductValidator();
        }, true);
    }

    /**
     * Register cache services
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function registerCacheServices(ContainerInterface $container): void {
        $container->register(Cache::class, function(ContainerInterface $c) {
            return Cache::get_instance();
        }, true);
    }
}
