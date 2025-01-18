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

namespace App\Contracts;

use App\Data\DomainData;
use App\Data\DomainResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Domain Repository
 *
 * This interface defines the contract for interacting with Postmark Domains.
 */
interface DomainRepositoryInterface
{
    /**
     * Get all senders with pagination
     *
     * @param  int  $limit  Items per page
     * @param  int  $page  Pagination offset
     * @return Collection<DomainResponse>
     */
    public function all(int $limit = 10, int $page = 0): Collection;

    /**
     * Find sender by ID
     *
     * @param  string  $id  Domain ID
     */
    public function find(string $id): DomainResponse;

    /**
     * Create new sender
     *
     * @param  DomainData  $data  Domain data
     */
    public function create(DomainData $data): DomainResponse;

    /**
     * Update sender
     *
     * @param  string  $id  Domain ID
     * @param  DomainData  $data  Domain data
     */
    public function update(string $id, DomainData $data): DomainResponse;

    /**
     * Delete sender
     *
     * @param  string  $id  Domain ID
     */
    public function delete(string $id): bool;
}
