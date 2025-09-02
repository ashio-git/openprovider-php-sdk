<?php

namespace Vexura\Model;

class EmailTemplate
{
    /** @var int|null */
    public $id;

    /** @var string|null */
    public $name;

    /** @var string|null */
    public $group;

    /** @var bool|null */
    public $is_active;

    /** @var bool|null */
    public $is_default;

    /** @var string[]|null */
    public $locale;

    /** @var EmailField[]|null */
    public $fields;

    /** @var EmailTag[]|null */
    public $tags;

    public function fromArray(array $data): self
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->group = $data['group'] ?? null;
        $this->is_active = $data['is_active'] ?? null;
        $this->is_default = $data['is_default'] ?? null;
        $this->locale = $data['locale'] ?? null;

        if (isset($data['fields'])) {
            $this->fields = array_map(function ($field) {
                return (new EmailField())->fromArray($field);
            }, $data['fields']);
        }

        if (isset($data['tags'])) {
            $this->tags = array_map(function ($tag) {
                return (new EmailTag())->fromArray($tag);
            }, $data['tags']);
        }

        return $this;
    }
}