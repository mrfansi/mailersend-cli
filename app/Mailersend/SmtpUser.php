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

namespace App\Mailersend;

use App\Contracts\SmtpUserRepositoryInterface;
use App\Data\SmtpUserData;
use App\Data\SmtpUserResponse;
use App\Services\SmtpUserCacheService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * SmtpUser API Client
 *
 * This class handles all smtp user-related operations with the Mailersend API.
 * It implements the SmtpUserRepositoryInterface for standardized smtp user operations.
 */
class SmtpUser implements SmtpUserRepositoryInterface
{
    /**
     * Maximum number of smtp users that can be retrieved
     */
    private const MAX_COUNT = 100;

    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Cache service for smtp user data
     */
    private SmtpUserCacheService $cacheService;

    /**
     * Domain ID associated with the smtp user
     */
    protected string $domainId;

    /**
     * The current smtp user data
     *
     * This property holds the current smtp user data obtained from the cache or the
     * API. It is an array of SmtpUserResponse objects.
     *
     * @var array<int, SmtpUserResponse>
     */
    private array $currentData = [];

    /**
     * Constructor for SmtpUser API client
     *
     * @param  PendingRequest  $client  HTTP client for making API requests
     * @param  SmtpUserCacheService  $cacheService  Cache service for smtp user data
     */
    public function __construct(PendingRequest $client, SmtpUserCacheService $cacheService, string $domainId)
    {
        $this->client = $client;
        $this->cacheService = $cacheService;
        $this->domainId = $domainId;
    }

    /**
     * Retrieve a list of smtp users from Mailersend API
     *
     * @param  int  $limit  Number of smtp users to retrieve (1-500)
     * @param  int  $page  Pagination page (>= 0)
     * @return Collection<SmtpUserResponse> Collection of smtp user objects
     *
     * @throws InvalidArgumentException When input parameters are invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException|Throwable When API response is not successful
     */
    public function all(int $limit = 25, int $page = 1): Collection
    {
        $this->validatePaginationParams($limit, $page);

        $cacheKey = $this->cacheService->generateKey($limit, $page);

        try {
            if ($this->cacheService->has($cacheKey)) {
                return $this->cacheService->get($cacheKey);
            }

            $response = $this->client->get("/domains/$this->domainId/smtp-users", [
                'limit' => $limit,
                'page' => $page,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve smtp users: %s', $response->body())
                );
            }

            $hasMore = (bool) $response->json('links')['next'] ?? false;

            $data = $response->json('data');

            $this->currentData = array_merge($this->currentData, $data);

            $totalData = count($this->currentData);

            Log::info("Successfully retrieved smtp user, page: $page, limit: $limit, data: $totalData");

            if ($hasMore) {
                return $this->all($limit, $page + 1);
            }

            $smtpUsers = collect($response->json('data'))
                ->map(fn (array $smtpUser) => SmtpUserResponse::fromArray($smtpUser));

            $this->cacheService->put($cacheKey, $smtpUsers);

            return $smtpUsers;
        } catch (Throwable $e) {
            Log::error('Failed to retrieve smtp users', [
                'domain_id' => $this->domainId,
                'limit' => $limit,
                'page' => $page,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Retrieve smtp user details by ID
     *
     * @param  string  $id  SmtpUser ID to retrieve
     * @return SmtpUserResponse SmtpUser details
     *
     * @throws InvalidArgumentException When smtp user ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function find(string $id): SmtpUserResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->get("/domains/$this->domainId/smtp-users/$id");

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve smtp users: %s', $response->json('message'))
                );
            }

            return SmtpUserResponse::fromArray($response->json('data'));
        } catch (Throwable $e) {
            Log::error('Failed to find smtp user', [
                'domain_id' => $this->domainId,
                'verified' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Create new smtp user
     *
     * @param  SmtpUserData  $data  SmtpUser data
     * @return SmtpUserResponse Created smtp user details
     *
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function create(SmtpUserData $data): SmtpUserResponse
    {
        try {
            $response = $this->client->post("/domains/$this->domainId/smtp-users", $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to create smtp user: %s', $response->body())
                );
            }

            return SmtpUserResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to create smtp user', [
                'domain_id' => $this->domainId,
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Update smtp user
     *
     * @param  string  $id  SmtpUser ID to update
     * @param  SmtpUserData  $data  SmtpUser data
     * @return SmtpUserResponse Updated smtp user details
     *
     * @throws InvalidArgumentException When smtp user ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function update(string $id, SmtpUserData $data): SmtpUserResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->put("domains/$this->domainId/smtp-users/$id", $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to update smtp user: %s', $response->body())
                );
            }

            return SmtpUserResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to update smtp user', [
                'domain_id' => $this->domainId,
                'verified' => $id,
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Delete smtp user
     *
     * @param  string  $id  SmtpUser ID to delete
     * @return bool True if deletion was successful
     *
     * @throws InvalidArgumentException When smtp user ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws Throwable
     */
    public function delete(string $id): bool
    {
        $this->validateId($id);

        try {
            $response = $this->client->delete("/domains/$this->domainId/smtp-users/$id");

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('Failed to delete smtp user', [
                'domain_id' => $this->domainId,
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
     * Validate smtp user ID
     *
     * @param  string  $id  SmtpUser ID
     *
     * @throws InvalidArgumentException When ID is invalid
     */
    private function validateId(string $id): void
    {
        if (! $id) {
            throw new InvalidArgumentException('SmtpUser ID cant be null');
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
