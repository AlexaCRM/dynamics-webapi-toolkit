<?php

declare( strict_types=1 );

namespace AlexaCRM\WebAPI;

use AlexaCRM\WebAPI\OData\Client as ODataClient;
use AlexaCRM\WebAPI\OData\EntityMap;
use AlexaCRM\WebAPI\OData\Metadata as ODataMetadata;
use AlexaCRM\Xrm\AttributeState;
use AlexaCRM\Xrm\Entity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase {

    /**
     * @var MockObject|ODataClient
     */
    protected MockObject $odataClient;

    /**
     * @var MockObject|ODataMetadata
     */
    protected MockObject $odataMetadata;

    /**
     * @var MockObject|EntityMap
     */
    protected MockObject $entityMap;

    public function setUp(): void {
        $this->odataMetadata = $this->createMock( ODataMetadata::class );
        $this->odataMetadata
            ->method( 'getEntityMap' )
            ->willReturn( $this->getEntityMap() );

        $this->odataMetadata
            ->method( 'getEntitySetName' )
            ->with( 'testentity' )
            ->willReturn( 'testentities' );

        $this->odataClient = $this->createMock( ODataClient::class );
        $this->odataClient
            ->method( 'getMetadata' )
            ->willReturn( $this->odataMetadata );
    }

    protected function getEntityMap(): EntityMap {
        $map = new EntityMap();

        $map->outboundMap = [
            'field1' => 'field1',
            'field2' => 'field2',
        ];

        return $map;
    }

    public function testCreate_willCreateSuccessfully(): void {
        $this->odataClient
            ->expects( $this->once() )
            ->method( 'create' )
            ->with( 'testentities', [ 'field1' => 42, 'field2' => 'value' ] )
            ->willReturn( '422950af-f524-4ece-a47e-3a66282b7b17' );

        $client = new Client( $this->odataClient );

        [ $record, $entityState ] = $this->getNewEntity( 'testentity' );

        $entityState
            ->expects( $this->once() )
            ->method( 'reset' );

        $record['field1'] = 42;
        $record['field2'] = 'value';

        $recordId = $client->Create( $record );

        $this->assertSame( '422950af-f524-4ece-a47e-3a66282b7b17', $recordId );
    }

    /**
     * @param string $entityName
     *
     * @return array{Entity, MockObject|AttributeState}
     */
    protected function getNewEntity( string $entityName ): array {
        $record = new Entity( $entityName );

        $state = $this->createTestProxy( AttributeState::class );

        $entityReflection = new \ReflectionClass( $record );
        $stateProp = $entityReflection->getProperty( 'attributeState' );
        $stateProp->setAccessible( true );
        $stateProp->setValue( $record, $state );

        return [ $record, $state ];
    }

}
