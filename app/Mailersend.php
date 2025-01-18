<?php

/*
 * Copyright (c) 2025 Muhammad Irfan.
 *  All rights reserved.
 *
 *  This project is created and maintained by Muhammad Irfan. Redistribution or modification
 *  of this code is permitted only under the terms specified in the license.
 *
 *  @author    Muhammad Irfan <mrfansi@outlook.com>
 *  @license    MIT
 */

namespace App;

use App\Contracts\HttpClientFactory;
use App\Contracts\MailersendFactoryInterface;
use App\Mailersend\Domain;
use App\Mailersend\Sender;
use App\Services\DomainCacheService;
use App\Services\SenderCacheService;
use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

/**
 * Mailersend API Client Factory
 *
 * This class serves as a factory for creating Mailersend API clients.
 * It provides centralized configuration and client instantiation.
 */
class Mailersend implements HttpClientFactory, MailersendFactoryInterface
{
    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Cache implementation for storing API responses
     */
    private CacheInterface $cache;

    /**
     * Sender cache service instance
     */
    private ?SenderCacheService $senderCache = null;

    /**
     * Sender domain service instance
     */
    private ?DomainCacheService $domainCache = null;

    /**
     * Constructor for Mailersend factory
     *
     * @param  CacheInterface  $cache  Cache implementation for storing API responses
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function __construct(CacheInterface $cache)
    {
        $this->validateConfiguration();
        $this->cache = $cache;
        $this->initializeHttpClient();
    }

    /**
     * Validates that all required configuration values are present
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    private function validateConfiguration(): void
    {
        if (empty(config('services.mailersend.endpoint'))) {
            throw new InvalidArgumentException('Mailersend API endpoint must be configured');
        }

        if (empty(config('services.mailersend.api_key'))) {
            throw new InvalidArgumentException('Mailersend API key must be configured');
        }
    }

    /**
     * Initializes the HTTP client with base configuration
     */
    private function initializeHttpClient(): void
    {
        $this->client = Http::baseUrl(config('services.mailersend.endpoint'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.config('services.mailersend.api_key'),
            ])
            ->throw();
    }

    /**
     * Creates and returns a new Sender API instance
     *
     * @return Sender A configured Sender instance for making API requests
     *
     * @throws RuntimeException If dependencies cannot be resolved
     */
    public function sender(): Sender
    {
        if ($this->senderCache === null) {
            $this->senderCache = new SenderCacheService($this->cache);
        }

        return new Sender($this->getClient(), $this->senderCache);
    }

    /**
     * Creates and returns a new Domain API instance
     *
     * @return Domain A configured Domain instance for making API requests
     *
     * @throws RuntimeException If dependencies cannot be resolved
     */
    public function domain(): Domain
    {
        if ($this->domainCache === null) {
            $this->domainCache = new DomainCacheService($this->cache);
        }

        return new Domain($this->getClient(), $this->domainCache);
    }

    /**
     * Returns the configured HTTP client
     *
     * @return PendingRequest The configured HTTP client
     */
    public function getClient(): PendingRequest
    {
        return $this->client;
    }

    /**
     * Creates a new instance of the Mailersend factory
     *
     * @param  CacheInterface|null  $cache  Optional cache implementation
     * @return static New Mailersend factory instance
     */
    public static function create(?CacheInterface $cache = null): static
    {
        return new static($cache ?? app(CacheInterface::class));
    }
}
