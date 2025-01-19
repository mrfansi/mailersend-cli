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

use App\Data\SenderData;
use App\Data\SenderResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Sender Repository
 *
 * This interface defines the contract for interacting with Mailersend Senders.
 */
interface SenderRepositoryInterface
{
    /**
     * Get all senders with pagination
     *
     * @param  int  $limit  Items per page
     * @param  int  $page  Pagination offset
     * @return Collection<SenderResponse>
     */
    public function all(int $limit = 25, int $page = 1): Collection;

    /**
     * Find sender by ID
     *
     * @param  string  $id  Sender ID
     */
    public function find(string $id): SenderResponse;

    /**
     * Find sender by Email
     *
     * @param  string  $email  Sender ID
     */
    public function findByEmail(string $email): SenderResponse;

    /**
     * Create new sender
     *
     * @param  SenderData  $data  Sender data
     */
    public function create(SenderData $data): SenderResponse;

    /**
     * Update sender
     *
     * @param  string  $id  Sender ID
     * @param  SenderData  $data  Sender data
     */
    public function update(string $id, SenderData $data): SenderResponse;

    /**
     * Update sender by email
     *
     * @param  string  $email  Sender Email
     * @param  SenderData  $data  Sender data
     */
    public function updateByEmail(string $email, SenderData $data): SenderResponse;

    /**
     * Delete sender
     *
     * @param  string  $id  Sender ID
     */
    public function delete(string $id): bool;

    /**
     * Delete sender by email
     *
     * @param  string  $email  Sender Email
     */
    public function deleteByEmail(string $email): bool;
}
