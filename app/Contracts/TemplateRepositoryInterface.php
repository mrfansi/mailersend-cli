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

use App\Data\TemplateResponse;
use Illuminate\Support\Collection;

/**
 * Interface for Template Repository
 *
 * This interface defines the contract for interacting with Mailersend Templates.
 */
interface TemplateRepositoryInterface
{
    /**
     * Get all senders with pagination
     *
     * @param  int  $limit  Items per page
     * @param  int  $page  Pagination offset
     * @return Collection<TemplateResponse>
     */
    public function all(int $limit = 10, int $page = 1): Collection;

    /**
     * Find sender by ID
     *
     * @param  string  $id  Template ID
     */
    public function find(string $id): TemplateResponse;


    /**
     * Delete sender
     *
     * @param  string  $id  Template ID
     */
    public function delete(string $id): bool;
}
