<?php

namespace App\Factories;

use App\Contracts\MailersendFactoryInterface;
use App\Mailersend\Domain;
use App\Mailersend\Sender;
use App\Services\DomainCacheService;
use App\Services\SenderCacheService;
use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Factory for creating Mailersend API clients
 */
class MailersendFactory implements MailersendFactoryInterface
{
    /**
     * Cache service instance
     */
    private ?CacheInterface $cache;

    /**
     * Create a new factory instance
     */
    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Create a new Sender Identity API instance
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function sender(): Sender
    {
        $client = $this->createClient();

        return new Sender(
            $client,
            new SenderCacheService($this->cache)
        );
    }

    /**
     * Create a new Domain API instance
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    public function domain(): Domain
    {
        $client = $this->createClient();

        return new Domain(
            $client,
            new DomainCacheService($this->cache)
        );
    }

    /**
     * Create HTTP client with Mailersend configuration
     *
     * @throws InvalidArgumentException When required configuration is missing
     */
    private function createClient(): PendingRequest
    {
        $endpoint = config('services.mailersend.endpoint');
        $token = config('services.mailersend.api_key');

        if (empty($endpoint)) {
            throw new InvalidArgumentException(
                'Mailersend API endpoint is not configured. Please set MAILERSEND_ENDPOINT in your .env file.'
            );
        }

        if (empty($token)) {
            throw new InvalidArgumentException(
                'Mailersend account token is not configured. Please set MAILERSEND_API_KEY in your .env file.'
            );
        }

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->baseUrl($endpoint);
    }

    /**
     * Create a new factory instance
     */
    public static function create(?CacheInterface $cache = null): static
    {
        return new static($cache);
    }
}
