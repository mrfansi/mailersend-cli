<?php

namespace App\Data;

/**
 * Template Response Data Transfer Object
 *
 * This class represents the response data structure from the Mailersend API
 * for template-related operations.
 */
readonly class TemplateResponse
{
    /**
     * Create a new TemplateResponse instance
     *
     * This constructor initializes a new instance of the TemplateResponse class
     * which represents the response data structure from the Mailersend API
     * for template-related operations.
     *
     * @param  string  $id  The unique identifier of the template
     * @param  string  $name  The name of the template
     * @param  string  $type  The type of the template
     * @param  string  $image_path  The URL path to the template image
     * @param  string  $created_at  The creation timestamp of the template
     * @param  TemplateStatsResponse|null  $stats  Template statistics
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public string $image_path,
        public string $created_at,
        public ?TemplateStatsResponse $stats,
    ) {
        // Initialize the TemplateResponse object with provided parameters
    }

    /**
     * Create a TemplateResponse instance from an array
     *
     * @param  array  $data  Template data from API response
     * @return self New TemplateResponse instance
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            type: $data['type'],
            image_path: $data['image_path'],
            created_at: $data['created_at'],
            stats: isset($data['template_stats']) ? TemplateStatsResponse::fromArray($data['template_stats']) : null,
        );
    }

    /**
     * Convert the DTO to an array
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'image_path' => $this->image_path,
            'created_at' => $this->created_at,
            'stats' => $this->stats->toArray(),
        ];
    }
}
