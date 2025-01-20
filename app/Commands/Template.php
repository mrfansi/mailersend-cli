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

use App\Commands\Contracts\TemplateCommandInterface;
use App\Commands\Traits\HasHandle;
use App\Commands\Traits\HasHelpers;
use App\Contracts\MailersendFactoryInterface;
use App\Data\TemplateResponse;
use App\Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use RuntimeException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\spin;

/**
 * Template Command
 *
 * This command provides functionality to manage Mailersend templates
 * through the command line interface.
 */
class Template extends Command implements TemplateCommandInterface
{
    use HasHandle;
    use HasHelpers;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'template
                         {action=list : Action to perform (list/show/delete)}
                         {--id= : Template ID for edit/delete actions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Mailersend templates for your account';

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
     * List all templates
     */
    public function list(): void
    {

        /** @var Collection<TemplateResponse> $templates */
        $templates = spin(
            fn () => $this->mailersend->template()->all(),
            'Fetching templates...'
        );

        // Mask sensitive data
        $templates = $templates->map(function (TemplateResponse $template) {
            return [
                Str::upper('id') => $template->id,
                Str::headline('name') => $template->name,
                Str::headline('type') => $template->type,
                Str::headline('created_at') => Carbon::parse($template->created_at)->diffForHumans(),
            ];
        });

        $table = $this->generator->getTable($templates);
        $this->table(...$table);
    }

    /**
     * Show template details
     */
    public function show(): void
    {
        $id = $this->getTemplateID();

        /** @var TemplateResponse $template */
        $template = spin(
            fn () => $this->mailersend->template()->find($id),
            'Fetching template details...'
        );

        $this->displayDetails($template);
    }

    /**
     * Delete a template
     */
    public function delete(): void
    {
        $id = $this->getTemplateID();
        if (! $id) {
            throw new InvalidArgumentException('Template ID is required for delete action');
        }

        /** @var TemplateResponse $template */
        $template = spin(
            fn () => $this->mailersend->template()->find($id),
            'Fetching template details...'
        );

        $this->displayDetails($template);

        if (! confirm(
            "Do you want to delete the template ID $id?",
            hint: 'Deleting a template cannot be undone! Any emails being sent through this template will be immediately rejected.'
        )) {
            return;
        }

        $success = spin(
            fn () => $this->mailersend->template()->delete($id),
            'Deleting template...'
        );

        if ($success) {
            $this->info('Template deleted successfully!');
        } else {
            throw new RuntimeException('Failed to delete template');
        }
    }

    /**
     * Display template details in a table
     */
    private function displayDetails(TemplateResponse $template): void
    {
        $detail = $this->generator->getDetailTable(collect([
            Str::upper('id') => $template->id,
            Str::headline('name') => $template->name,
            Str::headline('type') => $template->type,
//            Str::headline('image_path') => $template->image_path,
            Str::headline('created_at') => Carbon::parse($template->created_at)->diffForHumans(),
        ]));

        $stats = $this->generator->getDetailTable(collect([
            Str::headline('total') => $template->stats->total,
            Str::headline('queued') => $template->stats->queued,
            Str::headline('sent') => $template->stats->sent,
            Str::headline('rejected') => $template->stats->rejected,
            Str::headline('delivered') => $template->stats->delivered,
            Str::headline('opened') => $template->stats->opened,
            Str::headline('open_rate') => $template->stats->open_rate . '%',
            Str::headline('clicked') => $template->stats->clicked,
            Str::headline('click_rate') => $template->stats->click_rate . '%',
            Str::headline('unsubscribed') => $template->stats->unsubscribed,
            Str::headline('unsubscribe_rate') => $template->stats->unsubscribe_rate . '%',
            Str::headline('complained') => $template->stats->complained,
            Str::headline('complain_rate') => $template->stats->complain_rate . '%',
            Str::headline('last_email_sent_at') => isset($template->stats->last_email_sent_at) ? Carbon::parse($template->stats->last_email_sent_at)->diffForHumans() : 'None',
        ]));

        $this->info('Template Detail:');
        $this->table(...$detail);
        $this->line('');

        $this->info('Template Stats:');
        $this->table(...$stats);
    }
}
