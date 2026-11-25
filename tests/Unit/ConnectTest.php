<?php

namespace Unit;

use AlexaCRM\WebAPI\OData\AuthenticationException;
use AlexaCRM\WebAPI\OData\Client;
use AlexaCRM\WebAPI\OData\ODataException;
use AlexaCRM\WebAPI\OData\OnlineAuthMiddleware;
use AlexaCRM\WebAPI\OData\OnlineSettings;
use AlexaCRM\WebAPI\OData\TransportException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;

class ConnectTest extends TestCase {

    protected stdClass $answerWhoAmI;

    protected OnlineSettings $onlineSettings;

    protected Client $client;

    protected function setUp(): void {
        $this->answerWhoAmI = new stdClass();
        $this->answerWhoAmI->BusinessUnitId = '4fea69e8-0000-1111-2222-000d3a3a86ef';
        $this->answerWhoAmI->UserId = 'fc4a7fe4-1111-2222-3333-000d3aad87ba';
        $this->answerWhoAmI->OrganizationId = '111eee11-0000-2222-a43e-1e4aaf1204b5';

        $this->onlineSettings = $this->createMock( OnlineSettings::class );
        $this->onlineSettings->logger = $this->createMock( LoggerInterface::class );
        $middleware = $this->createMock( OnlineAuthMiddleware::class );

        $this->client = $this->getMockBuilder( Client::class )
                             ->setConstructorArgs( [ $this->onlineSettings, $middleware ] )
                             ->getMock();

        $this->client->method( 'executeFunction' )->with( 'WhoAmI' )
                     ->willReturn( $this->answerWhoAmI );
    }

    protected function tearDown(): void {

    }

    /**
     * A basic test example.
     *
     * @return void
     * @throws AuthenticationException
     * @throws ODataException
     * @throws TransportException
     * @throws Exception
     */
    public function testConnection() {

        $userInfo = $this->client->executeFunction( 'WhoAmI' );
        static::assertNotEmpty( $userInfo );
    }
}
