<?php

namespace AlexaCRM\Xrm;

/**
 * Describes how Dynamics 365 is accessed.
 */
interface IOrganizationService {

    /**
     * Creates a link between records.
     *
     * @param string $entityName
     * @param string $entityId Record ID.
     * @param Relationship $relationship
     * @param EntityReference[] $relatedEntities
     *
     * @return void
     */
    public function Associate( string $entityName, $entityId, Relationship $relationship, $relatedEntities );

    /**
     * Creates a record.
     *
     * @param Entity $entity
     *
     * @return string ID of the new record.
     */
    public function Create( Entity $entity );

    /**
     * Deletes a record.
     *
     * @param string $entityName
     * @param string $entityId Record ID.
     *
     * @return void
     */
    public function Delete( string $entityName, $entityId );

    /**
     * Deletes a link between records.
     *
     * @param string $entityName
     * @param string $entityId Record ID.
     * @param Relationship $relationship
     * @param EntityReference[] $relatedEntities
     *
     * @return void
     */
    public function Disassociate( string $entityName, $entityId, Relationship $relationship, $relatedEntities );

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
     * @param string $entityId Record ID.
     * @param ColumnSet $columnSet
     *
     * @return Entity
     */
    public function Retrieve( string $entityName, $entityId, ColumnSet $columnSet ) : Entity;

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
