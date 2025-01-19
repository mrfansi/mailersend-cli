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

use App\Contracts\SenderRepositoryInterface;
use App\Data\SenderData;
use App\Data\SenderResponse;
use App\Services\SenderCacheService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Sender API Client
 *
 * This class handles all sender-related operations with the Mailersend API.
 * It implements the SenderRepositoryInterface for standardized sender operations.
 */
class Sender implements SenderRepositoryInterface
{
    /**
     * Maximum number of senders that can be retrieved
     */
    private const MAX_COUNT = 100;

    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Cache service for sender data
     */
    private SenderCacheService $cacheService;

    /**
     * The current sender data
     *
     * This property holds the current sender data obtained from the cache or the
     * API. It is an array of SenderResponse objects.
     *
     * @var array<int, SenderResponse>
     */
    private array $currentData = [];

    /**
     * Constructor for Sender API client
     *
     * @param  PendingRequest  $client  HTTP client for making API requests
     * @param  SenderCacheService  $cacheService  Cache service for sender data
     */
    public function __construct(PendingRequest $client, SenderCacheService $cacheService)
    {
        $this->client = $client;
        $this->cacheService = $cacheService;
    }

    /**
     * Retrieve a list of senders from Mailersend API
     *
     * @param  int  $limit  Number of senders to retrieve (1-500)
     * @param  int  $page  Pagination page (>= 0)
     * @return Collection<SenderResponse> Collection of sender objects
     *
     * @throws Throwable When API response is not successful
     */
    public function all(int $limit = 25, int $page = 1): Collection
    {
        $this->validatePaginationParams($limit, $page);

        $cacheKey = $this->cacheService->generateKey($limit, $page);

        try {
            if ($this->cacheService->has($cacheKey)) {
                return $this->cacheService->get($cacheKey);
            }

            $response = $this->client->get('/identities', [
                'limit' => $limit,
                'page' => $page,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve senders: %s', $response->body())
                );
            }

            $hasMore = (bool) $response->json('links')['next'] ?? false;

            $data = $response->json('data');
            $this->currentData = array_merge($this->currentData, $data);

            $totalData = count($this->currentData);

            Log::info("Successfully retrieved sender, page: $page, limit: $limit, data: {$totalData}");

            if ($hasMore) {
                return $this->all($limit, $page + 1);
            }

            $senders = collect($this->currentData)
                ->map(fn (array $sender) => SenderResponse::fromArray($sender));

            $this->cacheService->put($cacheKey, $senders);

            return $senders;
        } catch (Throwable $e) {
            Log::error('Failed to retrieve senders', [
                'limit' => $limit,
                'page' => $page,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Retrieve sender details by ID
     *
     * @param  string  $id  Sender ID to retrieve
     * @return SenderResponse Sender details
     *
     * @throws InvalidArgumentException When sender ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function find(string $id): SenderResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->get("/identities/$id");

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve sender details: %s', $response->body())
                );
            }

            return SenderResponse::fromArray($response->json('data'));
        } catch (Throwable $e) {
            Log::error('Failed to find sender', [
                'sender_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Retrieve sender details by email
     *
     * @param  string  $email  Sender Email to retrieve
     * @return SenderResponse Sender details
     *
     * @throws InvalidArgumentException When sender ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function findByEmail(string $email): SenderResponse
    {
        $this->validateEmail($email);

        try {
            $response = $this->client->get("/identities/email/$email");

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve sender details: %s', $response->body())
                );
            }

            return SenderResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to find sender', [
                'sender_email' => $email,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Create new sender
     *
     * @param  SenderData  $data  Sender data
     * @return SenderResponse Created sender details
     *
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function create(SenderData $data): SenderResponse
    {
        try {
            $response = $this->client->post('/identities', $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to create sender: %s', $response->body())
                );
            }

            return SenderResponse::fromArray($response->json('data'));
        } catch (Throwable $e) {
            Log::error('Failed to create sender', [
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Update sender
     *
     * @param  string  $id  Sender ID to update
     * @param  SenderData  $data  Sender data
     * @return SenderResponse Updated sender details
     *
     * @throws InvalidArgumentException When sender ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function update(string $id, SenderData $data): SenderResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->put("/identities/$id", $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to update sender: %s', $response->body())
                );
            }

            return SenderResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to update sender', [
                'sender_id' => $id,
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Update sender by email
     *
     * @param  string  $email  Sender email to update
     * @param  SenderData  $data  Sender data
     * @return SenderResponse Updated sender details
     *
     * @throws InvalidArgumentException When sender email is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function updateByEmail(string $email, SenderData $data): SenderResponse
    {
        $this->validateEmail($email);

        try {
            $response = $this->client->put("/identities/email/$email", $data->toArray());

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to update sender: %s', $response->body())
                );
            }

            return SenderResponse::fromArray($response->json());
        } catch (Throwable $e) {
            Log::error('Failed to update sender', [
                'sender_email' => $email,
                'data' => $data->toArray(),
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Delete sender
     *
     * @param  string  $id  Sender ID to delete
     * @return bool True if deletion was successful
     *
     * @throws InvalidArgumentException When sender ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws Throwable
     */
    public function delete(string $id): bool
    {
        $this->validateId($id);

        try {
            $response = $this->client->delete("/identities/$id");

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('Failed to delete sender', [
                'sender_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Delete sender by email
     *
     * @param  string  $email  Sender email to delete
     * @return bool True if deletion was successful
     *
     * @throws InvalidArgumentException When sender email is invalid
     * @throws ConnectionException When API connection fails
     * @throws Throwable
     */
    public function deleteByEmail(string $email): bool
    {
        $this->validateEmail($email);

        try {
            $response = $this->client->delete("/identities/email/$email");

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('Failed to delete sender', [
                'sender_email' => $email,
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
     * Validate sender ID
     *
     * @param  string  $id  Sender ID
     *
     * @throws InvalidArgumentException When ID is invalid
     */
    private function validateId(string $id): void
    {
        if (! $id) {
            throw new InvalidArgumentException('Sender ID cant be null');
        }
    }

    /**
     * Validate sender Email
     *
     * @param  string  $email  Sender Email
     *
     * @throws InvalidArgumentException When Email is invalid
     */
    private function validateEmail(string $email): void
    {
        // Validate email address
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf('Sender Email must be a valid email address, given: %s', $email)
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
