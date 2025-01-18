<?php

/*
 * Copyright (c) 2025 Muhammad irfan.
 * All rights reserved.
 *
 * This project is created and maintained by Muhammad Irfan. Redistribution or modification
 * of this code is permitted only under the terms specified in the license.
 *
 * @package    postmark-cli
 * @license    MIT
 * @author     Muhammad Irfan <mrfansi@outlook.com>
 * @version    1.0.0
 * @since      2025-01-18
 */

namespace App\Mailersend;

use App\Contracts\DomainRepositoryInterface;
use App\Data\DomainData;
use App\Data\DomainResponse;
use App\Services\DomainCacheService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Domain API Client
 *
 * This class handles all domain-related operations with the Mailersend API.
 * It implements the DomainRepositoryInterface for standardized domain operations.
 */
class Domain implements DomainRepositoryInterface
{
    /**
     * Maximum number of domains that can be retrieved
     */
    private const MAX_COUNT = 100;

    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Cache service for domain data
     */
    private DomainCacheService $cacheService;

    /**
     * The current domain data
     *
     * This property holds the current domain data obtained from the cache or the
     * API. It is an array of DomainResponse objects.
     *
     * @var array<int, DomainResponse>
     */
    private array $currentData = [];

    /**
     * Constructor for Domain API client
     *
     * @param  PendingRequest  $client  HTTP client for making API requests
     * @param  DomainCacheService  $cacheService  Cache service for domain data
     */
    public function __construct(PendingRequest $client, DomainCacheService $cacheService)
    {
        $this->client = $client;
        $this->cacheService = $cacheService;
    }

    /**
     * Retrieve a list of domains from Mailersend API
     *
     * @param  int  $limit  Number of domains to retrieve (1-500)
     * @param  int  $page  Pagination page (>= 0)
     * @return Collection<DomainResponse> Collection of domain objects
     *
     * @throws InvalidArgumentException When input parameters are invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException|Throwable When API response is not successful
     */
    public function all(int $limit = 25, int $page = 0): Collection
    {
        $this->validatePaginationParams($limit, $page);

        $cacheKey = $this->cacheService->generateKey($limit, $page);

        try {
            if ($this->cacheService->has($cacheKey)) {
                return $this->cacheService->get($cacheKey);
            }

            $response = $this->client->get('/domains', [
                'limit' => $limit,
                'page' => $page,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve domains: %s', $response->body())
                );
            }

            $hasMore = (bool) $response->json('links')['next'] ?? false;

            $data = $response->json('data');
            $this->currentData = array_merge($this->currentData, $data);

            $totalData = count($this->currentData);

            Log::info("Successfully retrieved domain, page: $page, limit: $limit, data: {$totalData}");

            if ($hasMore) {
                return $this->all($limit, $page + 1);
            }

            $domains = collect($response->json('data'))
                ->map(fn (array $domain) => DomainResponse::fromArray($domain));

            $this->cacheService->put($cacheKey, $domains);

            return $domains;
        } catch (Throwable $e) {
            Log::error('Failed to retrieve domains', [
                'limit' => $limit,
                'page' => $page,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Retrieve domain details by ID
     *
     * @param  string  $id  Domain ID to retrieve
     * @return DomainResponse Domain details
     *
     * @throws InvalidArgumentException When domain ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function find(string $id): DomainResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->get("/domains/$id");

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve domains: %s', $response->json('message'))
                );
            }

            return DomainResponse::fromArray($response->json('data'));
        } catch (Throwable $e) {
            Log::error('Failed to find domain', [
                'verified' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Create new domain
     *
     * @param  DomainData  $data  Domain data
     * @return DomainResponse Created domain details
     *
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function create(DomainData $data): DomainResponse
    {
        try {
            $response = $this->client->post('/domains', $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to create domain: %s', $response->body())
                );
            }

            return DomainResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to create domain', [
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Update domain
     *
     * @param  string  $id  Domain ID to update
     * @param  DomainData  $data  Domain data
     * @return DomainResponse Updated domain details
     *
     * @throws InvalidArgumentException When domain ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function update(string $id, DomainData $data): DomainResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->put("/domains/$id", $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to update domain: %s', $response->body())
                );
            }

            return DomainResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to update domain', [
                'verified' => $id,
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Delete domain
     *
     * @param  string  $id  Domain ID to delete
     * @return bool True if deletion was successful
     *
     * @throws InvalidArgumentException When domain ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws Throwable
     */
    public function delete(string $id): bool
    {
        $this->validateId($id);

        try {
            $response = $this->client->delete("/domains/$id");

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('Failed to delete domain', [
                'verified' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Validate pagination parameters
     *
     * @param  int  $limit  Number of items per page
     * @param  int  $page  Pagination page
     *
     * @throws InvalidArgumentException When parameters are invalid
     */
    private function validatePaginationParams(int $limit, int $page): void
    {
        if ($limit < 1 || $limit > self::MAX_COUNT) {
            throw new InvalidArgumentException(
                sprintf('Count must be between 1 and %d, given: %d', self::MAX_COUNT, $limit)
            );
        }

        if ($page < 0) {
            throw new InvalidArgumentException(
                sprintf('Offset cannot be negative, given: %d', $page)
            );
        }
    }

    /**
     * Validate domain ID
     *
     * @param  string  $id  Domain ID
     *
     * @throws InvalidArgumentException When ID is invalid
     */
    private function validateId(string $id): void
    {
        if (! $id) {
            throw new InvalidArgumentException('Domain ID must be greater than 0');
        }
    }

    /**
     * Validate domain Email
     *
     * @param  string  $email  Domain Email
     *
     * @throws InvalidArgumentException When Email is invalid
     */
    private function validateEmail(string $email): void
    {
        // Validate email address
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf('Domain Email must be a valid email address, given: %s', $email)
            );
        }
    }

    /**
     * Handle exception and convert to appropriate type
     *
     * @param  Throwable  $e  Exception to handle
     * @return Throwable Converted exception
     */
    private function handleException(Throwable $e): Throwable
    {
        if ($e instanceof ConnectionException) {
            return $e;
        }

        if ($e instanceof InvalidArgumentException) {
            return $e;
        }

        return new RuntimeException($e->getMessage(), 0, $e);
    }
}
