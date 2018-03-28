<?php

namespace AlexaCRM\Xrm;

use Ramsey\Uuid\UuidInterface as Guid;

/**
 * Describes how Dynamics 365 is accessed.
 */
interface IOrganizationService {

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
    public function Associate( string $entityName, Guid $entityId, Relationship $relationship, $relatedEntities );

    /**
     * Creates a record.
     *
     * @param Entity $entity
     *
     * @return Guid
     */
    public function Create( Entity $entity ) : Guid;

    /**
     * Deletes a record.
     *
     * @param string $entityName
     * @param Guid $entityId
     *
     * @return void
     */
    public function Delete( string $entityName, Guid $entityId );

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
    public function Disassociate( string $entityName, Guid $entityId, Relationship $relationship, $relatedEntities );

    /**
     * Executes a function or action formed as a request.
     *
     * @param $request
     *
     * @return mixed
     */
    public function Execute( $request );

    /**
     * Retrieves a record,
     *
     * @param string $entityName
     * @param Guid $entityId
     * @param ColumnSet $columnSet
     *
     * @return Entity
     */
    public function Retrieve( string $entityName, Guid $entityId, ColumnSet $columnSet ) : Entity;

    /**
     * Retrieves a collection of records.
     *
     * @param $query
     *
     * @return mixed
     */
    public function RetrieveMultiple( $query );

    /**
     * Updates an existing record.
     *
     * @param Entity $entity
     *
     * @return void
     */
    public function Update( Entity $entity );

}
