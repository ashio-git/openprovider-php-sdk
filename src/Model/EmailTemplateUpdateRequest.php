<?php

declare(strict_types=1);

namespace Ashio\Model;

class EmailTemplateUpdateRequest
{
    /** @var EmailField[]|null */
    public ?array $fields = null;
    public ?string $group = null;
    public ?bool $isActive = null;
    public ?bool $isDefault = null;
    /** @var string[]|null */
    public ?array $locale = null;
    public ?string $name = null;
    /** @var EmailTag[]|null */
    public ?array $tags = null;

    /**
     * @return array<string,mixed>
     */
    /**

     * @return array<string, mixed>

     */
    public function toArray(): array
    {
        $data = [
            'group' => $this->group,
            'is_active' => $this->isActive,
            'is_default' => $this->isDefault,
            'locale' => $this->locale,
            'name' => $this->name,
        ];
        if ($this->fields) {
            $data['fields'] = array_map(static function (EmailField $field) {
                return array_filter([
                    'name' => $field->name,
                    'value' => $field->value,
                ], static fn ($v) => $v !== null);
            }, $this->fields);
        }
        if ($this->tags) {
            $data['tags'] = array_map(static function (EmailTag $tag) {
                return array_filter([
                    'key' => $tag->key,
                    'value' => $tag->value,
                ], static fn ($v) => $v !== null);
            }, $this->tags);
        }

        return array_filter($data, static fn ($v) => $v !== null);
    }
}
