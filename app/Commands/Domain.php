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

namespace App\Commands;

use App\Commands\Contracts\CommandInterface;
use App\Commands\Traits\HasHandle;
use App\Commands\Traits\HasHelpers;
use App\Contracts\MailersendFactoryInterface;
use App\Data\DomainData;
use App\Data\DomainResponse;
use App\Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

/**
 * Domain Command
 *
 * This command provides functionality to manage Mailersend domains
 * through the command line interface.
 */
class Domain extends Command implements CommandInterface
{
    use HasHandle;
    use HasHelpers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domain
                         {action=list : Action to perform (list/show/new/edit/delete)}
                         {--id= : Domain ID for edit/delete actions}
                         {--name= : Domain name for new/edit actions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Mailersend domains for your account';

    /**
     * Mailersend factory instance
     */
    private MailersendFactoryInterface $mailersend;

    /**
     * Generator instance for output formatting
     */
    private Generator $generator;

    /**
     * Create a new command instance.
     */
    public function __construct(MailersendFactoryInterface $mailersend, Generator $generator)
    {
        parent::__construct();

        $this->mailersend = $mailersend;
        $this->generator = $generator;
    }

    /**
     * List all domains
     */
    public function list(): void
    {

        /** @var Collection<DomainResponse> $domains */
        $domains = spin(
            fn () => $this->mailersend->domain()->all(),
            'Fetching domains...'
        );

        // Mask sensitive data
        $domains = $domains->map(function (DomainResponse $domain) {
            return [
                Str::upper('id') => $domain->id,
                Str::headline('name') => $domain->name,
                Str::upper('dkim') => $domain->dkim ? 'Yes' : 'No',
                Str::upper('spf') => $domain->spf ? 'Yes' : 'No',
                Str::headline('tracking') => $domain->tracking ? 'Yes' : 'No',
                Str::headline('is_verified') => $domain->is_verified ? 'Yes' : 'No',
                Str::headline('is_dns_active') => $domain->is_dns_active ? 'Yes' : 'No',
                Str::headline('created_at') => Carbon::parse($domain->created_at)->diffForHumans(),
                Str::headline('updated_at') => Carbon::parse($domain->updated_at)->diffForHumans(),
            ];
        });

        $table = $this->generator->getTable($domains);
        $this->table(...$table);
    }

    /**
     * Create a new domain
     */
    public function new(): void
    {
        $data = $this->getData();

        /** @var DomainResponse $domain */
        $domain = spin(
            fn () => $this->mailersend->domain()->create($data),
            'Creating domain...'
        );

        $this->info('Domain created successfully!');
        $this->displayDetails($domain);
    }

    /**
     * Show domain details
     */
    public function show(): void
    {
        $id = $this->getDomainID();

        /** @var DomainResponse $domain */
        $domain = spin(
            fn () => $this->mailersend->domain()->find($id),
            'Fetching domain details...'
        );

        $this->displayDetails($domain);
    }

    /**
     * Edit domain details
     */
    public function edit(): void
    {
        $id = $this->getDomainID();
        if (! $id) {
            throw new InvalidArgumentException('Domain ID is required for edit action');
        }

        /** @var DomainResponse $domain */
        $domain = spin(
            fn () => $this->mailersend->domain()->find($id),
            'Fetching domain details...'
        );

        $data = $this->getData($domain);

        /** @var DomainResponse $updatedDomain */
        $updatedDomain = spin(
            fn () => $this->mailersend->domain()->update($id, $data),
            'Updating domain...'
        );

        $this->info('Domain updated successfully!');
        $this->notify('Success', 'Domain updated successfully!');

        $this->displayDetails($updatedDomain);
    }

    /**
     * Delete a domain
     */
    public function delete(): void
    {
        $id = $this->getDomainID();
        if (! $id) {
            throw new InvalidArgumentException('Domain ID is required for delete action');
        }

        /** @var DomainResponse $domain */
        $domain = spin(
            fn () => $this->mailersend->domain()->find($id),
            'Fetching domain details...'
        );

        $this->displayDetails($domain);

        if (! confirm(
            "Do you want to delete the domain ID $id?",
            hint: 'Deleting a domain cannot be undone! Any emails being sent through this domain will be immediately rejected.'
        )) {
            return;
        }

        $success = spin(
            fn () => $this->mailersend->domain()->delete($id),
            'Deleting domain...'
        );

        if ($success) {
            $this->info('Domain deleted successfully!');
        } else {
            throw new RuntimeException('Failed to delete domain');
        }
    }

    /**
     * Get domain data from form input
     */
    private function getData(?DomainResponse $current = null): DomainData
    {
        $formData = form()
            ->text(
                label: 'Domain Name',
                default: $current->name ?? '',
                required: true,
                hint: 'Domain name (Must be unique and lowercase. Domain must be available and resolvable.)',
                name: 'name',
            )
            ->addIf(fn () => confirm('Do you want to add Return Path Subdomain?', false), function () {
                return text(
                    label: 'Return Path Subdomain',
                    hint: 'Subdomain for return path (Must be alphanumeric.)',
                );
            }, 'return_path_subdomain')
            ->addIf(fn () => confirm('Do you want to add Custom Tracking Subdomain?', false), function () {
                return text(
                    label: 'Custom Tracking Subdomain',
                    hint: 'Subdomain for custom tracking (Must be alphanumeric.)',
                );
            }, 'custom_tracking_subdomain')
            ->addIf(fn () => confirm('Do you want to add Inbound Routing Subdomain?', false), function () {
                return text(
                    label: 'Inbound Routing Subdomain',
                    hint: 'Subdomain for inbound routing (Must be alphanumeric.)',
                );
            }, 'inbound_routing_subdomain')
            ->submit();

        $formData = array_filter($formData);

        return new DomainData(
            ...$formData
        );
    }

    /**
     * Display domain details in a table
     */
    private function displayDetails(DomainResponse $domain): void
    {
        $detail = $this->generator->getDetailTable(collect([
            Str::upper('id') => $domain->id,
            Str::headline('name') => $domain->name,
            Str::upper('dkim') => $domain->dkim ? 'Yes' : 'No',
            Str::upper('spf') => $domain->spf ? 'Yes' : 'No',
            Str::headline('tracking') => $domain->tracking ? 'Yes' : 'No',
            Str::headline('is_verified') => $domain->is_verified ? 'Yes' : 'No',
            Str::headline('is_cname_verified') => $domain->is_cname_verified ? 'Yes' : 'No',
            Str::headline('is_dns_active') => $domain->is_dns_active ? 'Yes' : 'No',
            Str::headline('is_cname_active') => $domain->is_cname_active ? 'Yes' : 'No',
            Str::headline('is_tracking_allowed') => $domain->is_tracking_allowed ? 'Yes' : 'No',
            Str::headline('has_not_queued_messages') => $domain->has_not_queued_messages ? 'Yes' : 'No',
            Str::headline('not_queued_messages_count') => $domain->not_queued_messages_count,
            Str::headline('created_at') => Carbon::parse($domain->created_at)->diffForHumans(),
            Str::headline('updated_at') => Carbon::parse($domain->updated_at)->diffForHumans(),
        ]));

        $setting = $this->generator->getDetailTable(collect([
            Str::headline('send_paused') => $domain->domain_settings->send_paused ? 'Yes' : 'No',
            Str::headline('track_clicks') => $domain->domain_settings->track_clicks ? 'Yes' : 'No',
            Str::headline('track_opens') => $domain->domain_settings->track_opens ? 'Yes' : 'No',
            Str::headline('track_unsubscribe') => $domain->domain_settings->track_unsubscribe ? 'Yes' : 'No',
            Str::headline('track_unsubscribe_html') => $domain->domain_settings->track_unsubscribe_html,
            Str::headline('track_unsubscribe_html_enabled') => $domain->domain_settings->track_unsubscribe_html_enabled ? 'Yes' : 'No',
            Str::headline('track_unsubscribe_plain') => $domain->domain_settings->track_unsubscribe_plain,
            Str::headline('track_unsubscribe_plain_enabled') => $domain->domain_settings->track_unsubscribe_plain_enabled ? 'Yes' : 'No',
            Str::headline('track_content') => $domain->domain_settings->track_content ? 'Yes' : 'No',
            Str::headline('custom_tracking_enabled') => $domain->domain_settings->custom_tracking_enabled ? 'Yes' : 'No',
            Str::headline('custom_tracking_subdomain') => $domain->domain_settings->custom_tracking_subdomain,
            Str::headline('return_path_subdomain') => $domain->domain_settings->return_path_subdomain,
            Str::headline('inbound_routing_enabled') => $domain->domain_settings->inbound_routing_enabled ? 'Yes' : 'No',
            Str::headline('inbound_routing_subdomain') => $domain->domain_settings->inbound_routing_subdomain,
            Str::headline('precedence_bulk') => $domain->domain_settings->precedence_bulk ? 'Yes' : 'No',
            Str::headline('ignore_duplicated_recipients') => $domain->domain_settings->ignore_duplicated_recipients ? 'Yes' : 'No',
            Str::headline('show_dmarc') => $domain->domain_settings->show_dmarc ? 'Yes' : 'No',
        ]));

        $this->info('Domain Detail:');
        $this->table(...$detail);
        $this->line('');

        $this->info('Domain Setting:');
        $this->table(...$setting);
    }
}
