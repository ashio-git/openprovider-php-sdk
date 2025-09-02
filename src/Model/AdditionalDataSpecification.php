<?php

namespace Ashio\Model;

class AdditionalDataSpecification
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var bool|null
     */
    public $required;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var AdditionalDataSelectOption[]|null
     */
    public $options;

    public function fromArray(array $data): self
    {
        $this->name = $data['name'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->required = $data['required'] ?? null;
        $this->type = $data['type'] ?? null;

        if (isset($data['options'])) {
            $this->options = array_map(function ($option) {
                return (new AdditionalDataSelectOption())->fromArray($option);
            }, $data['options']);
        }

        return $this;
    }
}