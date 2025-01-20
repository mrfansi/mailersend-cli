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

namespace App\Services;

use App\Data\SmtpUserResponse;
use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * SmtpUser Cache Service
 *
 * This service handles caching operations for smtp_user data.
 */
class SmtpUserCacheService
{
    /**
     * Cache duration in seconds (5 minutes)
     */
    private const CACHE_TTL = 300;

    /**
     * Cache key prefix for smtp_user data
     */
    private const CACHE_PREFIX = 'mailersend_smtp_users';

    /**
     * Cache implementation
     */
    private CacheInterface $cache;

    /**
     * Constructor
     *
     * @param  CacheInterface  $cache  Cache implementation
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get smtp_users from cache
     *
     * @param  string  $key  Cache key
     * @return Collection<SmtpUserResponse>|null
     *
     * @throws InvalidArgumentException
     */
    public function get(string $key): ?Collection
    {
        return $this->cache->get($key);
    }

    /**
     * Store smtp_users in cache
     *
     * @param  string  $key  Cache key
     * @param  Collection<SmtpUserResponse>  $smtp_users  SmtpUsers to cache
     *
     * @throws InvalidArgumentException
     */
    public function put(string $key, Collection $smtp_users): bool
    {
        return $this->cache->set($key, $smtp_users, self::CACHE_TTL);
    }

    /**
     * Check if key exists in cache
     *
     * @param  string  $key  Cache key
     *
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }

    /**
     * Generate cache key for smtp_user list
     *
     * @param  int  $limit  Number of items per page
     * @param  int  $page  Pagination offset
     * @return string Cache key
     */
    public function generateKey(int $limit, int $page): string
    {
        return sprintf('%s_%d_%d', self::CACHE_PREFIX, $limit, $page);
    }
}
