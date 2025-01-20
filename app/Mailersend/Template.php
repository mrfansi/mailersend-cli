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

use App\Contracts\TemplateRepositoryInterface;
use App\Data\TemplateResponse;
use App\Services\TemplateCacheService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Template API Client
 *
 * This class handles all template-related operations with the Mailersend API.
 * It implements the TemplateRepositoryInterface for standardized template operations.
 */
class Template implements TemplateRepositoryInterface
{
    /**
     * Maximum number of templates that can be retrieved
     */
    private const MAX_COUNT = 100;

    /**
     * HTTP client instance for making API requests
     */
    private PendingRequest $client;

    /**
     * Cache service for template data
     */
    private TemplateCacheService $cacheService;

    /**
     * The current template data
     *
     * This property holds the current template data obtained from the cache or the
     * API. It is an array of TemplateResponse objects.
     *
     * @var array<int, TemplateResponse>
     */
    private array $currentData = [];

    /**
     * Constructor for Template API client
     *
     * @param  PendingRequest  $client  HTTP client for making API requests
     * @param  TemplateCacheService  $cacheService  Cache service for template data
     */
    public function __construct(PendingRequest $client, TemplateCacheService $cacheService)
    {
        $this->client = $client;
        $this->cacheService = $cacheService;
    }

    /**
     * Retrieve a list of templates from Mailersend API
     *
     * @param  int  $limit  Number of templates to retrieve (1-500)
     * @param  int  $page  Pagination page (>= 0)
     * @return Collection<TemplateResponse> Collection of template objects
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

            $response = $this->client->get('/templates', [
                'limit' => $limit,
                'page' => $page,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve templates: %s', $response->body())
                );
            }

            $hasMore = (bool) $response->json('links')['next'] ?? false;

            $data = $response->json('data');

            $this->currentData = array_merge($this->currentData, $data);

            $totalData = count($this->currentData);

            Log::info("Successfully retrieved template, page: $page, limit: $limit, data: $totalData");

            if ($hasMore) {
                return $this->all($limit, $page + 1);
            }

            $templates = collect($response->json('data'))
                ->map(fn (array $template) => TemplateResponse::fromArray($template));

            $this->cacheService->put($cacheKey, $templates);

            return $templates;
        } catch (Throwable $e) {
            Log::error('Failed to retrieve templates', [
                'limit' => $limit,
                'page' => $page,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Retrieve template details by ID
     *
     * @param  string  $id  Template ID to retrieve
     * @return TemplateResponse Template details
     *
     * @throws InvalidArgumentException When template ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws RuntimeException When API response is not successful
     * @throws Throwable
     */
    public function find(string $id): TemplateResponse
    {
        $this->validateId($id);

        try {
            $response = $this->client->get("/templates/$id");

            if (! $response->successful()) {
                throw new RuntimeException(
                    sprintf('Failed to retrieve templates: %s', $response->json('message'))
                );
            }

            return TemplateResponse::fromArray($response->json('data'));
        } catch (Throwable $e) {
            Log::error('Failed to find template', [
                'verified' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $this->handleException($e);
        }
    }

    /**
     * Delete template
     *
     * @param  string  $id  Template ID to delete
     * @return bool True if deletion was successful
     *
     * @throws InvalidArgumentException When template ID is invalid
     * @throws ConnectionException When API connection fails
     * @throws Throwable
     */
    public function delete(string $id): bool
    {
        $this->validateId($id);

        try {
            $response = $this->client->delete("/templates/$id");

            return $response->successful();
        } catch (Throwable $e) {
            Log::error('Failed to delete template', [
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
     * Validate template ID
     *
     * @param  string  $id  Template ID
     *
     * @throws InvalidArgumentException When ID is invalid
     */
    private function validateId(string $id): void
    {
        if (! $id) {
            throw new InvalidArgumentException('Template ID cant be null');
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
