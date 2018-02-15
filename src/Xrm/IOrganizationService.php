<?php

namespace AlexaCRM\Xrm;

use AlexaCRM\Guid;

/**
 * Describes how Dynamics 365 is accessed.
 */
interface IOrganizationService {

    /**
     * Creates a link between records.
     *
     * @param string $entityName
     * @param Guid $entityId
     * @param $relationship
     * @param EntityReference[] $relatedEntities
     *
     * @return void
     */
    public function Associate( string $entityName, Guid $entityId, $relationship, $relatedEntities ) : void;

    /**
     * Creates a record.
     *
     * @param Entity $entity
     *
     * @return mixed
     */
    public function Create( Entity $entity );

    /**
     * Deletes a record.
     *
     * @param string $entityName
     * @param Guid $entityId
     *
     * @return void
     */
    public function Delete( string $entityName, Guid $entityId ) : void;

    /**
     * Deletes a link between records.
     *
     * @param string $entityName
     * @param Guid $entityId
     * @param $relationship
     * @param EntityReference[] $relatedEntities
     *
     * @return void
     */
    public function Disassociate( string $entityName, Guid $entityId, $relationship, $relatedEntities ) : void;

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
    public function Update( Entity $entity ) : void;

}
