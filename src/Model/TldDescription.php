<?php

namespace Ashio\Model;

class TldDescription
{
    public ?string $text = null;
    public ?string $url = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
