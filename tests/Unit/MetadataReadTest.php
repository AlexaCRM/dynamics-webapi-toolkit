<?php

namespace Unit;

use AlexaCRM\WebAPI\OData\Client;
use AlexaCRM\WebAPI\OData\EntityMap;
use AlexaCRM\WebAPI\OData\Metadata;
use AlexaCRM\WebAPI\OData\OnlineAuthMiddleware;
use AlexaCRM\WebAPI\OData\OnlineSettings;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MetadataReadTest extends TestCase {

    const MS_CRM = 'Microsoft.Dynamics.CRM';

    protected Client $client;

    protected function setUp(): void {

        $this->onlineSettings = $this->createMock( OnlineSettings::class );
        $this->onlineSettings->logger = $this->createMock( LoggerInterface::class );
        $middleware = $this->createMock( OnlineAuthMiddleware::class );

        $this->client = $this->getMockBuilder( Client::class )
                             ->setConstructorArgs( [ $this->onlineSettings, $middleware ] )
                             ->getMock();

        $this->client->method( 'getMetadata' )->willReturn( Metadata::createFromXML( $this->loadFixture( 'metadata.xml' ) ) );
    }

    protected function loadFixture( $file ): false|string {
        return file_get_contents( __DIR__ . "/../Fixtures/{$file}" );
    }

    protected function tearDown(): void {

    }

    public function testMetadataRead() {
        $md = $this->client->getMetadata();
        $this->assertIsArray( $md->entityMaps );
        $this->assertInstanceOf( EntityMap::class, current( $md->entityMaps ) );
        $this->assertEquals( self::MS_CRM, $md->namespace );
    }
}
