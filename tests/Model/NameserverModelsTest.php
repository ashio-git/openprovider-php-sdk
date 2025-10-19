<?php

namespace Ashio\Tests\Model;

use Ashio\Model\NameserverGroup;
use Ashio\Model\NameserverSet;
use PHPUnit\Framework\TestCase;

class NameserverModelsTest extends TestCase
{
    public function testNameserverGroupFromArray(): void
    {
        $data = [
            'id' => 789,
            'ns_group' => 'Default NS Group',
            'ns_count' => 2,
        ];

        $group = (new NameserverGroup())->fromArray($data);

        $this->assertEquals(789, $group->id);
        $this->assertEquals('Default NS Group', $group->nsGroup);
        $this->assertEquals(2, $group->nsCount);
    }

    public function testNameserverGroupToArray(): void
    {
        $group = new NameserverGroup();
        $group->id = 100;
        $group->nsGroup = 'Test Group';
        $group->nsCount = 3;

        $array = $group->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(100, $array['id']);
        $this->assertEquals('Test Group', $array['ns_group']);
        $this->assertEquals(3, $array['ns_count']);
    }

    public function testNameserverSetFromArray(): void
    {
        $data = [
            'name' => 'ns1.example.com',
            'ip' => '192.168.1.1',
        ];

        $set = (new NameserverSet())->fromArray($data);

        $this->assertEquals('ns1.example.com', $set->name);
        $this->assertEquals('192.168.1.1', $set->ip);
    }

    public function testNameserverSetToArray(): void
    {
        $set = new NameserverSet();
        $set->name = 'ns2.example.com';
        $set->ip = '10.0.0.1';

        $array = $set->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('ns2.example.com', $array['name']);
        $this->assertEquals('10.0.0.1', $array['ip']);
    }

    public function testEmptyNameserverGroup(): void
    {
        $group = new NameserverGroup();

        $this->assertNull($group->id);
        $this->assertNull($group->nsGroup);
        $this->assertNull($group->nsCount);
    }

    public function testEmptyNameserverSet(): void
    {
        $set = new NameserverSet();

        $this->assertNull($set->name);
        $this->assertNull($set->ip);
    }
}
