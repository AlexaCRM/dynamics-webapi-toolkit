<?php
/**
 * Copyright 2018 AlexaCRM
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 * associated documentation files (the "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
 * OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

namespace AlexaCRM\Xrm;

use AlexaCRM\Xrm\Query\QueryBase;

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
    public function Associate( string $entityName, string $entityId, Relationship $relationship, array $relatedEntities ): void;

    /**
     * Creates a record.
     *
     * @param Entity $entity
     *
     * @return string ID of the new record.
     */
    public function Create( Entity $entity ): string;

    /**
     * Deletes a record.
     *
     * @param string $entityName
     * @param string $entityId Record ID.
     *
     * @return void
     */
    public function Delete( string $entityName, string $entityId ): void;

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
    public function Disassociate( string $entityName, string $entityId, Relationship $relationship, array $relatedEntities ): void;

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
     * @return Entity|null
     */
    public function Retrieve( string $entityName, string $entityId, ColumnSet $columnSet ): ?Entity;

    /**
     * Retrieves a collection of records.
     *
     * @param QueryBase $query
     *
     * @return EntityCollection
     */
    public function RetrieveMultiple( QueryBase $query ): EntityCollection;

    /**
     * Updates an existing record.
     *
     * @param Entity $entity
     *
     * @return void
     */
    public function Update( Entity $entity ): void;

}
