<?php

namespace AlexaCRM\WebAPI;

use AlexaCRM\Xrm\ColumnSet;
use AlexaCRM\Xrm\Entity;
use AlexaCRM\Xrm\EntityReference;
use AlexaCRM\Xrm\IOrganizationService;
use AlexaCRM\Xrm\Relationship;
use Ramsey\Uuid\UuidInterface as Guid;
use AlexaCRM\WebAPI\OData\Client as ODataClient;

class Client implements IOrganizationService {

    /**
     * @var ODataClient
     */
    protected $client;

    public function __construct( ODataClient $client ) {
        $this->client = $client;
    }

    /**
     * Creates a link between records.
     *
     * @param string $entityName
     * @param Guid $entityId
     * @param Relationship $relationship
     * @param EntityReference[] $relatedEntities
     *
     * @return void
     */
    public function Associate( string $entityName, Guid $entityId, Relationship $relationship, $relatedEntities ) {
        // TODO: Implement Associate() method.
    }

    /**
     * Creates a record.
     *
     * @param Entity $entity
     *
     * @return Guid
     */
    public function Create( Entity $entity ) : Guid {
        // TODO: Implement Create() method.
    }

    /**
     * Deletes a record.
     *
     * @param string $entityName
     * @param Guid $entityId
     *
     * @return void
     */
    public function Delete( string $entityName, Guid $entityId ) {
        // TODO: Implement Delete() method.
    }

    /**
     * Deletes a link between records.
     *
     * @param string $entityName
     * @param Guid $entityId
     * @param Relationship $relationship
     * @param EntityReference[] $relatedEntities
     *
     * @return void
     */
    public function Disassociate( string $entityName, Guid $entityId, Relationship $relationship, $relatedEntities ) {
        // TODO: Implement Disassociate() method.
    }

    /**
     * Executes a function or action formed as a request.
     *
     * @param $request
     *
     * @return mixed
     */
    public function Execute( $request ) {
        return $this->client->ExecuteFunction( $request );
    }

    /**
     * Retrieves a record,
     *
     * @param string $entityName
     * @param Guid $entityId
     * @param ColumnSet $columnSet
     *
     * @return Entity
     */
    public function Retrieve( string $entityName, Guid $entityId, ColumnSet $columnSet ) : Entity {
        // TODO: Implement Retrieve() method.
    }

    /**
     * Retrieves a collection of records.
     *
     * @param $query
     *
     * @return mixed
     */
    public function RetrieveMultiple( $query ) {
        // TODO: Implement RetrieveMultiple() method.
    }

    /**
     * Updates an existing record.
     *
     * @param Entity $entity
     *
     * @return void
     */
    public function Update( Entity $entity ) {
        // TODO: Implement Update() method.
    }

    public function getClient() : ODataClient {
        return $this->client;
    }
}
