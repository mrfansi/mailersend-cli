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

use App\Contracts\TokenRepositoryInterface;
use App\Data\TokenCreateResponse;
use App\Data\TokenData;
use App\Data\TokenResponse;
use App\Services\TokenCacheService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Token API Client
 *
 * This class handles all token-related operations with the Mailersend API.
 * It implements the TokenRepositoryInterface for standardized token operations.
 */
class Token implements TokenRepositoryInterface
{
    /**
     * Maximum number of tokens that can be retrieved
     */
    private const MAX_COUNT = 100;

    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Cache service for token data
     */
    private TokenCacheService $cacheService;

    /**
     * The current token data
     *
     * This property holds the current token data obtained from the cache or the
     * API. It is an array of TokenResponse objects.
     *
     * @var array<int, TokenResponse>
     */
    private array $currentData = [];

    /**
     * Constructor for Token API client
     *
     * @param  PendingRequest  $client  HTTP client for making API requests
     * @param  TokenCacheService  $cacheService  Cache service for token data
     */
    public function __construct(PendingRequest $client, TokenCacheService $cacheService)
    {
        $this->client = $client;
        $this->cacheService = $cacheService;
    }

    /**
     * Retrieve a list of tokens from Mailersend API
     *
     * @param  int  $limit  Number of tokens to retrieve (1-500)
     * @param  int  $page  Pagination page (>= 0)
     * @return Collection<TokenResponse> Collection of token objects
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

            $response = $this->client->get('/token', [
                'limit' => $limit,
                'page' => $page,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve tokens: %s', $response->body())
                );
            }

            $hasMore = (bool) $response->json('links')['next'] ?? false;

            $data = $response->json('data');

            $this->currentData = array_merge($this->currentData, $data);

            $totalData = count($this->currentData);

            Log::info("Successfully retrieved token, page: $page, limit: $limit, data: $totalData");

            if ($hasMore) {
                return $this->all($limit, $page + 1);
            }

            $tokens = collect($response->json('data'))
                ->map(fn (array $token) => TokenResponse::fromArray($token));

            $this->cacheService->put($cacheKey, $tokens);

            return $tokens;
        } catch (Throwable $e) {
            Log::error('Failed to retrieve tokens', [
                'limit' => $limit,
                'page' => $page,
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

    /**
     * Retrieve token details by ID
     *
     * @param  string  $id  Token ID to retrieve
     * @return TokenResponse Token details
     *
     * @throws InvalidArgumentException When token ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function find(string $id): TokenResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->get("/token/$id");

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve tokens: %s', $response->json('message'))
                );
            }

            return TokenResponse::fromArray($response->json('data'));
        } catch (Throwable $e) {
            Log::error('Failed to find token', [
                'verified' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Validate token ID
     *
     * @param  string  $id  Token ID
     *
     * @throws InvalidArgumentException When ID is invalid
     */
    private function validateId(string $id): void
    {
        if (! $id) {
            throw new InvalidArgumentException('Token ID cant be null');
        }
    }

    /**
     * Create new token
     *
     * @param  TokenData  $data  Token data
     * @return TokenCreateResponse Created token details
     *
     * @throws Throwable
     */
    public function create(TokenData $data): TokenCreateResponse
    {
        try {
            $response = $this->client->post('/token', $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to create token: %s', $response->body())
                );
            }

            return TokenCreateResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to create token', [
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Update token
     *
     * @param  string  $id  Token ID to update
     * @param  TokenData  $data  Token data
     * @return TokenResponse Updated token details
     *
     * @throws InvalidArgumentException When token ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function update(string $id, TokenData $data): TokenResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->put("/token/$id", $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to update token: %s', $response->body())
                );
            }

            return TokenResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to update token', [
                'verified' => $id,
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Delete token
     *
     * @param  string  $id  Token ID to delete
     * @return bool True if deletion was successful
     *
     * @throws InvalidArgumentException When token ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws Throwable
     */
    public function delete(string $id): bool
    {
        $this->validateId($id);

        try {
            $response = $this->client->delete("/token/$id");

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('Failed to delete token', [
                'verified' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }
}
