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

namespace App\Factories;

use App\Contracts\MailersendFactoryInterface;
use App\Mailersend\Domain;
use App\Mailersend\Email;
use App\Mailersend\Sender;
use App\Mailersend\Template;
use App\Mailersend\Token;
use App\Services\DomainCacheService;
use App\Services\SenderCacheService;
use App\Services\TemplateCacheService;
use App\Services\TokenCacheService;
use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Factory for creating Mailersend API clients
 */
class MailersendFactory implements MailersendFactoryInterface
{
    /**
     * Cache service instance
     */
    private ?CacheInterface $cache;

    /**
     * Create a new factory instance
     */
    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Create a new Sender Identity API instance
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function sender(): Sender
    {
        $client = $this->createClient();

        return new Sender(
            $client,
            new SenderCacheService($this->cache)
        );
    }

    /**
     * Create a new Domain API instance
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function domain(): Domain
    {
        $client = $this->createClient();

        return new Domain(
            $client,
            new DomainCacheService($this->cache)
        );
    }

    /**
     * Create a new Token API instance
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function token(): Token
    {
        $client = $this->createClient();

        return new Token(
            $client,
            new TokenCacheService($this->cache)
        );
    }

    /**
     * Create a new Template API instance
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function template(): Template
    {
        $client = $this->createClient();

        return new Template(
            $client,
            new TemplateCacheService($this->cache)
        );
    }

    /**
     * Create a new Token API instance
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function email(): Email
    {
        $client = $this->createClient();

        return new Email(
            $client,
        );
    }

    /**
     * Create HTTP client with Mailersend configuration
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    private function createClient(): PendingRequest
    {
        $endpoint = config('services.mailersend.endpoint');
        $token = config('services.mailersend.api_key');

        if (empty($endpoint)) {
            throw new InvalidArgumentException(
                'Mailersend API endpoint is not configured. Please set MAILERSEND_ENDPOINT in your .env file.'
            );
        }

        if (empty($token)) {
            throw new InvalidArgumentException(
                'Mailersend account token is not configured. Please set MAILERSEND_API_KEY in your .env file.'
            );
        }

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->baseUrl($endpoint);
    }

    /**
     * Create a new factory instance
     */
    public static function create(?CacheInterface $cache = null): static
    {
        return new static($cache);
    }
}
