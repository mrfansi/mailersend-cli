<?php

namespace App\Contracts;

use Illuminate\Http\Client\PendingRequest;

/**
 * Interface for HTTP client factory
 *
 * This interface defines the contract for classes that provide HTTP client instances
 * for making API requests.
 */
interface HttpClientFactory
{
    /**
     * Returns a configured HTTP client instance
     *
     * @return PendingRequest The configured HTTP client
     */
    public function getClient(): PendingRequest;
}
