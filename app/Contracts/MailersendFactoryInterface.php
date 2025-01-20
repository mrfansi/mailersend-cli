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

use App\Mailersend\Domain;
use App\Mailersend\Email;
use App\Mailersend\Sender;
use App\Mailersend\SmtpUser;
use App\Mailersend\Template;
use App\Mailersend\Token;
use Illuminate\Contracts\Cache\Repository as CacheInterface;

/**
 * Interface for Mailersend Factory
 *
 * This interface defines the contract for creating various Mailersend API clients.
 */
interface MailersendFactoryInterface
{
    /**
     * Creates and returns a new Domain API instance
     *
     * @return Domain A configured Domain instance for making API requests
     */
    public function domain(): Domain;

    /**
     * Creates and returns a new Sender API instance
     *
     * @return Sender A configured Sender instance for making API requests
     */
    public function sender(): Sender;

    /**
     * Creates and returns a new Token API instance
     *
     * @return Token A configured Token instance for making API requests
     */
    public function token(): Token;

    /**
     * Creates and returns a new Email API instance
     *
     * @return Email A configured Email instance for making API requests
     */
    public function email(): Email;

    /**
     * Creates and returns a new Template API instance
     *
     * @return Template A configured Template instance for making API requests
     */
    public function template(): Template;

    /**
     * Creates and returns a new SMTP User API instance
     *
     * @param  string  $domain_id  Domain ID for the SMTP user
     * @return SmtpUser A configured SMTP User instance for making API requests
     */
    public function smtpUser(string $domain_id): SmtpUser;

    /**
     * Creates a new instance of the Mailersend factory
     *
     * @param  CacheInterface|null  $cache  Optional cache implementation
     * @return static New Mailersend factory instance
     */
    public static function create(?CacheInterface $cache = null): static;
}
