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

namespace App\Contracts;

use App\Data\TokenCreateResponse;
use App\Data\TokenData;
use App\Data\TokenResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Token Repository
 *
 * This interface defines the contract for interacting with Mailersend Tokens.
 */
interface TokenRepositoryInterface
{
    /**
     * Get all senders with pagination
     *
     * @param  int  $limit  Items per page
     * @param  int  $page  Pagination offset
     * @return Collection<TokenResponse>
     */
    public function all(int $limit = 10, int $page = 1): Collection;

    /**
     * Find sender by ID
     *
     * @param  string  $id  Token ID
     */
    public function find(string $id): TokenResponse;

    /**
     * Create new sender
     *
     * @param  TokenData  $data  Token data
     */
    public function create(TokenData $data): TokenCreateResponse;

    /**
     * Update sender
     *
     * @param  string  $id  Token ID
     * @param  TokenData  $data  Token data
     */
    public function update(string $id, TokenData $data): TokenResponse;

    /**
     * Delete sender
     *
     * @param  string  $id  Token ID
     */
    public function delete(string $id): bool;
}
