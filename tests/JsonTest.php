<?php namespace Arcanedev\Json\Tests;

use Arcanedev\Json\Json;

/**
 * Class     JsonTest
 *
 * @package  Arcanedev\Support\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class JsonTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var Json */
    private $json;

    /** @var string */
    private $fixturePath;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->fixturePath = $this->getFixturesPath('file-1.json');
        $this->json        = new Json($this->fixturePath);
    }

    public function tearDown()
    {
        unset($this->json);

        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Json::class, $this->json);

        $this->assertInstanceOf(
            \Illuminate\Filesystem\Filesystem::class,
            $this->json->getFilesystem()
        );

        $this->assertEquals(
            $this->convertFixture($this->fixturePath),
            $this->json->toArray()
        );
    }

    /** @test */
    public function it_can_make()
    {
        $this->json = Json::make($this->fixturePath);

        $this->assertInstanceOf(Json::class, $this->json);

        $this->assertEquals(
            $this->getFixtureContent($this->fixturePath),
            $this->json->getContents()
        );

        $this->assertEquals(
            $this->getFixtureContent($this->fixturePath),
            (string) $this->json
        );

        $this->assertEquals(
            $this->convertFixture($this->fixturePath),
            $this->json->toArray()
        );

        $this->assertJson($this->json->toJson());
        $this->assertJson(json_encode($this->json));
    }

    /** @test */
    public function it_can_get_and_set_filesystem()
    {
        $this->assertInstanceOf(
            \Illuminate\Filesystem\Filesystem::class,
            $this->json->getFilesystem()
        );

        $mock = $this->prophesize(\Illuminate\Filesystem\Filesystem::class);

        /** @var \Illuminate\Filesystem\Filesystem $filesystem */
        $filesystem = $mock->reveal();
        $this->json->setFilesystem($filesystem);

        $this->assertInstanceOf(
            \Illuminate\Filesystem\Filesystem::class,
            $this->json->getFilesystem()
        );
    }

    /** @test */
    public function it_can_get_and_set_an_attribute()
    {
        $fixture = $this->convertFixture($this->fixturePath);

        $this->assertEquals($fixture['name'], $this->json->get('name'));
        $this->assertEquals($fixture['name'], $this->json->name);
        $this->assertEquals($fixture['name'], $this->json->name());

        $this->assertEquals($fixture['description'], $this->json->get('description'));
        $this->assertEquals($fixture['description'], $this->json->description);
        $this->assertEquals($fixture['description'], $this->json->description());

        $this->assertNull($this->json->get('url', null));
        $this->assertNull($this->json->url);
        $this->assertNull($this->json->url());

        $url = 'https://www.github.com';
        $this->json->set('url', $url);

        $this->assertEquals($url, $this->json->get('url'));
        $this->assertEquals($url, $this->json->url);
        $this->assertEquals($url, $this->json->url());
    }

    /** @test */
    public function it_can_set_and_get_path()
    {
        $this->assertEquals($this->fixturePath, $this->json->getPath());

        $this->fixturePath = $this->getFixturesPath('file-2.json');
        $this->json->setPath($this->fixturePath);

        $this->assertEquals($this->fixturePath, $this->json->getPath());
    }

    /** @test */
    public function it_can_save()
    {
        $path = $this->getFixturesPath('saved.json');

        $this->assertNotFalse($this->json->setPath($path)->save());

        $this->assertEquals(
            $this->getFixtureContent($path),
            $this->json->getContents()
        );

        unlink($path);
    }

    /** @test */
    public function it_can_update()
    {
        $path = $this->getFixturesPath('saved.json');

        $this->assertEquals(5, count($this->json->getAttributes()));
        $this->assertNotFalse($this->json->setPath($path)->save());
        $this->assertEquals(
            $this->getFixtureContent($path),
            $this->json->getContents()
        );

        $this->json->update(['url' => 'https://www.github.com']);
        $this->assertEquals(6, count($this->json->getAttributes()));

        unlink($path);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @param  string $path
     *
     * @return string
     */
    private function getFixtureContent($path)
    {
        return file_get_contents($path);
    }

    /**
     * Convert fixture file to array
     *
     * @param  string  $path
     *
     * @return array
     */
    private function convertFixture($path)
    {
        return json_decode($this->getFixtureContent($path), JSON_PRETTY_PRINT);
    }
}
