<?php

namespace Ashio\Tests\Model;

use Ashio\Model\Contact;
use Ashio\Model\ContactAddress;
use Ashio\Model\ContactName;
use Ashio\Model\ContactPhone;
use Ashio\Model\CustomerFax;
use Ashio\Model\Reseller;
use Ashio\Model\ResellerAdditionalData;
use Ashio\Model\RgpStatuses;
use PHPUnit\Framework\TestCase;

class ResellerModelsTest extends TestCase
{
    public function testResellerConstructor(): void
    {
        $reseller = (new Reseller())->fromArray([
            'id' => 123,
            'company_name' => 'Reseller Inc',
            'address' => ['street' => 'Keizersgracht', 'number' => '100', 'city' => 'Amsterdam', 'country' => 'NL'],
            'balance' => 250.75,
            'reserved_balance' => 20.0,
            'additional_data' => ['internal_reference' => 'REF-42'],
            'email' => 'billing@reseller.example',
            'fax' => ['country_code' => '+31', 'area_code' => '20', 'subscriber_number' => '1234567'],
            'locale' => 'nl_NL',
            'name' => ['first_name' => 'Anna', 'last_name' => 'de Vries'],
            'phone' => ['country_code' => '+31', 'area_code' => '20', 'subscriber_number' => '7654321'],
            'status' => 'ACT',
            'tier' => 3,
            'vat' => 'NL123456789B01',
            'vatperc' => 21.0,
            'contacts' => [['id' => 7, 'email' => 'tech@reseller.example']],
        ]);

        $this->assertSame(123, $reseller->id);
        $this->assertSame('Reseller Inc', $reseller->companyName);
        $this->assertInstanceOf(ContactAddress::class, $reseller->address);
        $this->assertSame('Amsterdam', $reseller->address->city);
        $this->assertSame(250.75, $reseller->balance);
        $this->assertSame(20.0, $reseller->reservedBalance);
        $this->assertInstanceOf(ResellerAdditionalData::class, $reseller->additionalData);
        $this->assertSame('REF-42', $reseller->additionalData->internal_reference);
        $this->assertSame('billing@reseller.example', $reseller->email);
        $this->assertInstanceOf(CustomerFax::class, $reseller->fax);
        $this->assertSame('1234567', $reseller->fax->subscriberNumber);
        $this->assertSame('nl_NL', $reseller->locale);
        $this->assertInstanceOf(ContactName::class, $reseller->name);
        $this->assertSame('Anna', $reseller->name->firstName);
        $this->assertInstanceOf(ContactPhone::class, $reseller->phone);
        $this->assertSame('7654321', $reseller->phone->subscriberNumber);
        $this->assertSame('ACT', $reseller->status);
        $this->assertSame(3, $reseller->tier);
        $this->assertSame('NL123456789B01', $reseller->vat);
        $this->assertSame(21.0, $reseller->vatperc);
        $this->assertCount(1, $reseller->contacts);
        $this->assertInstanceOf(Contact::class, $reseller->contacts[0]);
        $this->assertSame('tech@reseller.example', $reseller->contacts[0]->email);
        $this->assertNull($reseller->settings);

        $array = $reseller->toArray();
        $this->assertSame(123, $array['id']);
        $this->assertSame('Reseller Inc', $array['company_name']);
        $this->assertSame(20.0, $array['reserved_balance']);
        $this->assertSame(['internal_reference' => 'REF-42'], $array['additional_data']);
        $this->assertSame([['id' => 7, 'email' => 'tech@reseller.example']], $array['contacts']);
        $this->assertSame(
            ['country_code' => '+31', 'area_code' => '20', 'subscriber_number' => '1234567'],
            $array['fax']
        );
        $this->assertArrayNotHasKey('settings', $array);
    }

    public function testResellerAdditionalDataConstructor(): void
    {
        $data = new ResellerAdditionalData(cif_nif_number: 'B12345678', gstin: '22AAAAA0000A1Z5');

        $this->assertSame('B12345678', $data->cif_nif_number);
        $this->assertSame('22AAAAA0000A1Z5', $data->gstin);
        $this->assertNull($data->internal_reference);
        $this->assertSame(['cif_nif_number' => 'B12345678', 'gstin' => '22AAAAA0000A1Z5'], $data->toArray());
        $this->assertEquals($data, ResellerAdditionalData::fromArray($data->toArray()));
    }

    public function testRgpStatusesConstructor(): void
    {
        $statuses = new RgpStatuses(add_period: '2024-01-06 00:00:00', auto_renew_period: '2024-02-15 00:00:00');

        $this->assertSame('2024-01-06 00:00:00', $statuses->add_period);
        $this->assertSame('2024-02-15 00:00:00', $statuses->auto_renew_period);

        $data = ['add_period' => '2024-01-06 00:00:00', 'auto_renew_period' => '2024-02-15 00:00:00'];
        $this->assertSame($data, $statuses->toArray());
        $this->assertEquals($statuses, RgpStatuses::fromArray($data));
    }

    public function testEmptyReseller(): void
    {
        $reseller = new Reseller();

        $this->assertNull($reseller->id);
        $this->assertNull($reseller->companyName);
        $this->assertSame([], $reseller->toArray());
    }

    public function testEmptyRgpStatuses(): void
    {
        $statuses = new RgpStatuses();

        $this->assertNull($statuses->add_period);
        $this->assertNull($statuses->auto_renew_period);
        $this->assertSame([], $statuses->toArray());
    }
}
