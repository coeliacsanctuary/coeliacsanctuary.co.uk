<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Shop\AddressSearch;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    #[Test]
    public function itCallsTheGeoAddressService(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([]),
        ]);

        $this->getJson(route('api.shop.address-search.get', ['id' => 'foo']))->assertOk();

        Http::assertSent(function (Request $request) {
            $this->assertEquals('GET', $request->method());
            $this->assertStringContainsString(config('services.idealPostcodes.url'), $request->url());
            $this->assertStringContainsString('/autocomplete/addresses/foo', $request->url());

            return true;
        });
    }

    #[Test]
    public function itReturnsTheAddressFormatted(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'result' => [
                    'postcode' => 'NN1 3ER',
                    'postcode_inward' => '3ER',
                    'postcode_outward' => 'NN1',
                    'post_town' => 'Northampton',
                    'dependant_locality' => '',
                    'double_dependant_locality' => '',
                    'thoroughfare' => 'Watkin Terrace',
                    'dependant_thoroughfare' => '',
                    'building_number' => '10',
                    'building_name' => '',
                    'sub_building_name' => '',
                    'po_box' => '',
                    'department_name' => '',
                    'organisation_name' => '',
                    'udprn' => 123,
                    'postcode_type' => 'S',
                    'su_organisation_indicator' => '',
                    'delivery_point_suffix' => '1F',
                    'line_1' => '10 Watkin Terrace',
                    'line_2' => '',
                    'line_3' => '',
                    'premise' => '10',
                    'longitude' => -1.1,
                    'latitude' => 53.1,
                    'eastings' => 123,
                    'northings' => 456,
                    'country' => 'England',
                    'traditional_county' => 'Northamptonshire',
                    'administrative_county' => '',
                    'postal_county' => 'Northamptonshire',
                    'county' => 'Northamptonshire',
                    'district' => 'Northamptonshire',
                    'ward' => 'Northampton',
                    'uprn' => '123',
                    'id' => 'paf_123',
                    'country_iso' => 'GBR',
                    'country_iso_2' => 'GB',
                    'county_code' => '',
                    'language' => 'en',
                    'umprn' => '',
                    'dataset' => 'paf',
                ],
            ]),
        ]);

        $this->getJson(route('api.shop.address-search.get', ['id' => 'foo']))->assertOk()
            ->assertJson([
                'address_1' => '10 Watkin Terrace',
                'address_2' => '',
                'address_3' => '',
                'town' => 'Northampton',
                'county' => 'Northamptonshire',
                'postcode' => 'NN1 3ER',
            ]);
    }
}
