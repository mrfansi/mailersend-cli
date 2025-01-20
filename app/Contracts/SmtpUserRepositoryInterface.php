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

use App\Data\SmtpData;
use App\Data\SmtpResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Smtp Repository
 *
 * This interface defines the contract for interacting with Mailersend SmtpUsers.
 */
interface SmtpRepositoryInterface
{
    /**
     * Get all senders with pagination
     *
     * @param  int  $limit  Items per page
     * @param  int  $page  Pagination offset
     * @return Collection<SmtpResponse>
     */
    public function all(int $limit = 10, int $page = 1): Collection;

    /**
     * Find sender by ID
     *
     * @param  string  $id  Smtp ID
     */
    public function find(string $id): SmtpResponse;

    /**
     * Create new sender
     *
     * @param  SmtpData  $data  Smtp data
     */
    public function create(SmtpData $data): SmtpResponse;

    /**
     * Update sender
     *
     * @param  string  $id  Smtp ID
     * @param  SmtpData  $data  Smtp data
     */
    public function update(string $id, SmtpData $data): SmtpResponse;

    /**
     * Delete sender
     *
     * @param  string  $id  Smtp ID
     */
    public function delete(string $id): bool;
}
