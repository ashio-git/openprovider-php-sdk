<?php

declare(strict_types=1);

namespace Vexura\Model;

class EmailTemplateCreateRequest
{
    /** @var EmailField[]|null */
    public ?array $fields = null;
    public string $group;
    public ?bool $isActive = null;
    public ?bool $isDefault = null;
    /** @var string[]|null */
    public ?array $locale = null;
    public string $name;
    /** @var EmailTag[]|null */
    public ?array $tags = null;

    public function __construct(string $group, string $name)
    {
        $this->group = $group;
        $this->name = $name;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        $data = [
            'group' => $this->group,
            'name' => $this->name,
        ];
        if ($this->fields) {
            $data['fields'] = array_map(static function (EmailField $field) {
                return array_filter([
                    'name' => $field->name,
                    'value' => $field->value,
                ], static fn($v) => $v !== null);
            }, $this->fields);
        }
        if ($this->isActive !== null) {
            $data['is_active'] = $this->isActive;
        }
        if ($this->isDefault !== null) {
            $data['is_default'] = $this->isDefault;
        }
        if ($this->locale !== null) {
            $data['locale'] = $this->locale;
        }
        if ($this->tags) {
            $data['tags'] = array_map(static function (EmailTag $tag) {
                return array_filter([
                    'key' => $tag->key,
                    'value' => $tag->value,
                ], static fn($v) => $v !== null);
            }, $this->tags);
        }
        return $data;
    }
}
