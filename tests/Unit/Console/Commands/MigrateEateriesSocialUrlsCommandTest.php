<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\OneTime\MigrateEateriesSocialUrlsCommand;
use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MigrateEateriesSocialUrlsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itMigratesFacebookUrlToFacebookUrlColumnAndClearsWebsite(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'website' => 'https://www.facebook.com/someeatery',
        ]);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)->run();

        $eatery->refresh();

        $this->assertSame('https://www.facebook.com/someeatery', $eatery->facebook_url);
        $this->assertNull($eatery->website);
        $this->assertNull($eatery->instagram_url);
    }

    #[Test]
    public function itMigratesFbComShortUrlToFacebookUrlColumnAndClearsWebsite(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'website' => 'https://fb.com/someeatery',
        ]);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)->run();

        $eatery->refresh();

        $this->assertSame('https://fb.com/someeatery', $eatery->facebook_url);
        $this->assertNull($eatery->website);
        $this->assertNull($eatery->instagram_url);
    }

    #[Test]
    public function itMigratesInstagramUrlToInstagramUrlColumnAndClearsWebsite(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'website' => 'https://www.instagram.com/someeatery',
        ]);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)->run();

        $eatery->refresh();

        $this->assertSame('https://www.instagram.com/someeatery', $eatery->instagram_url);
        $this->assertNull($eatery->website);
        $this->assertNull($eatery->facebook_url);
    }

    #[Test]
    public function itLeavesNonSocialWebsiteUntouched(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'website' => 'https://www.someeatery.co.uk',
        ]);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)->run();

        $eatery->refresh();

        $this->assertSame('https://www.someeatery.co.uk', $eatery->website);
        $this->assertNull($eatery->facebook_url);
        $this->assertNull($eatery->instagram_url);
    }

    #[Test]
    public function itSkipsEateriesWithNoWebsite(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'website' => null,
        ]);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)->run();

        $eatery->refresh();

        $this->assertNull($eatery->website);
        $this->assertNull($eatery->facebook_url);
        $this->assertNull($eatery->instagram_url);
    }

    #[Test]
    public function itSkipsEateriesWithAnEmptyStringWebsite(): void
    {
        $eatery = $this->build(Eatery::class)->create([
            'website' => '',
        ]);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)->run();

        $eatery->refresh();

        $this->assertSame('', $eatery->website);
        $this->assertNull($eatery->facebook_url);
        $this->assertNull($eatery->instagram_url);
    }

    #[Test]
    public function itReportsTheCorrectFacebookMigrationCount(): void
    {
        $this->build(Eatery::class)->count(3)->create(['website' => 'https://www.facebook.com/eatery']);
        $this->build(Eatery::class)->count(2)->create(['website' => 'https://fb.com/eatery']);
        $this->build(Eatery::class)->count(2)->create(['website' => 'https://www.instagram.com/eatery']);
        $this->build(Eatery::class)->create(['website' => 'https://www.someeatery.co.uk']);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)
            ->expectsOutputToContain('Facebook URLs migrated: 5')
            ->run();
    }

    #[Test]
    public function itReportsTheCorrectInstagramMigrationCount(): void
    {
        $this->build(Eatery::class)->count(3)->create(['website' => 'https://www.facebook.com/eatery']);
        $this->build(Eatery::class)->count(2)->create(['website' => 'https://www.instagram.com/eatery']);
        $this->build(Eatery::class)->create(['website' => 'https://www.someeatery.co.uk']);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)
            ->expectsOutputToContain('Instagram URLs migrated: 2')
            ->run();
    }

    #[Test]
    public function itProcessesAllEateriesInASingleRun(): void
    {
        $facebook = $this->build(Eatery::class)->create(['website' => 'https://www.facebook.com/fb-eatery']);
        $instagram = $this->build(Eatery::class)->create(['website' => 'https://www.instagram.com/ig-eatery']);
        $plain = $this->build(Eatery::class)->create(['website' => 'https://www.plainsite.co.uk']);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)->run();

        $facebook->refresh();
        $instagram->refresh();
        $plain->refresh();

        $this->assertSame('https://www.facebook.com/fb-eatery', $facebook->facebook_url);
        $this->assertNull($facebook->website);

        $this->assertSame('https://www.instagram.com/ig-eatery', $instagram->instagram_url);
        $this->assertNull($instagram->website);

        $this->assertSame('https://www.plainsite.co.uk', $plain->website);
        $this->assertNull($plain->facebook_url);
        $this->assertNull($plain->instagram_url);
    }

    /** @return array<string, array{string}> */
    public static function facebookUrlProvider(): array
    {
        return [
            'full facebook.com URL' => ['https://www.facebook.com/eatery'],
            'facebook.com without www' => ['https://facebook.com/eatery'],
            'fb.com short URL' => ['https://fb.com/eatery'],
            'm.facebook.com mobile URL' => ['https://m.facebook.com/eatery'],
        ];
    }

    #[Test]
    #[DataProvider('facebookUrlProvider')]
    public function itDetectsVariousFacebookUrlFormats(string $url): void
    {
        $eatery = $this->build(Eatery::class)->create(['website' => $url]);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)->run();

        $eatery->refresh();

        $this->assertSame($url, $eatery->facebook_url);
        $this->assertNull($eatery->website);
    }

    /** @return array<string, array{string}> */
    public static function instagramUrlProvider(): array
    {
        return [
            'full instagram.com URL' => ['https://www.instagram.com/eatery'],
            'instagram.com without www' => ['https://instagram.com/eatery'],
        ];
    }

    #[Test]
    #[DataProvider('instagramUrlProvider')]
    public function itDetectsVariousInstagramUrlFormats(string $url): void
    {
        $eatery = $this->build(Eatery::class)->create(['website' => $url]);

        $this->artisan(MigrateEateriesSocialUrlsCommand::class)->run();

        $eatery->refresh();

        $this->assertSame($url, $eatery->instagram_url);
        $this->assertNull($eatery->website);
    }
}
