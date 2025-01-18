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

use App\Data\SenderResponse;
use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Sender Cache Service
 *
 * This service handles caching operations for sender data.
 */
class SenderCacheService
{
    /**
     * Cache duration in seconds (5 minutes)
     */
    private const CACHE_TTL = 300;

    /**
     * Cache key prefix for sender data
     */
    private const CACHE_PREFIX = 'mailersend_senders';

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
     * Get senders from cache
     *
     * @param  string  $key  Cache key
     * @return Collection<SenderResponse>|null
     *
     * @throws InvalidArgumentException
     */
    public function get(string $key): ?Collection
    {
        return $this->cache->get($key);
    }

    /**
     * Store senders in cache
     *
     * @param  string  $key  Cache key
     * @param  Collection<SenderResponse>  $senders  Senders to cache
     *
     * @throws InvalidArgumentException
     */
    public function put(string $key, Collection $senders): bool
    {
        return $this->cache->set($key, $senders, self::CACHE_TTL);
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
     * Generate cache key for sender list
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
