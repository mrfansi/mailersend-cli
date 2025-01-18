<?php

namespace App\Contracts;

use App\Mailersend\Sender;
use Illuminate\Contracts\Cache\Repository as CacheInterface;

/**
 * Interface for Mailersend Factory
 *
 * This interface defines the contract for creating various Mailersend API clients.
 */
interface MailersendFactoryInterface
{
    /**
     * Creates and returns a new Sender API instance
     *
     * @return Sender A configured Sender instance for making API requests
     */
    public function sender(): Sender;

    /**
     * Creates a new instance of the Mailersend factory
     *
     * @param  CacheInterface|null  $cache  Optional cache implementation
     * @return static New Mailersend factory instance
     */
    public static function create(?CacheInterface $cache = null): static;
}
