<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write;

use Magento\Framework\EntityManager\Operation\Write\Delete\ValidateDelete;
use Magento\Framework\EntityManager\Operation\Write\Delete\DeleteMain;
use Magento\Framework\EntityManager\Operation\Write\Delete\DeleteAttributes;
use Magento\Framework\EntityManager\Operation\Write\Delete\DeleteExtensions;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;

/**
 * Class Delete
 */
class Delete
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var TransactionManagerInterface
     */
    private $transactionManager;

    private $validateDelete;

    /**
     * @var DeleteMain
     */
    private $deleteMain;

    /**
     * @var DeleteAttributes
     */
    private $deleteAttributes;

    /**
     * @var DeleteExtensions
     */
    private $deleteExtensions;

    /**
     * Delete constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param ManagerInterface $eventManager
     * @param TransactionManagerInterface $transactionManager
     * @param ValidateDelete $validateDelete
     * @param DeleteMain $deleteMain
     * @param DeleteAttributes $deleteAttributes
     * @param DeleteExtensions $deleteExtensions
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        ManagerInterface $eventManager,
        TransactionManagerInterface $transactionManager,
        ValidateDelete $validateDelete,
        DeleteMain $deleteMain,
        DeleteAttributes $deleteAttributes,
        DeleteExtensions $deleteExtensions
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->eventManager = $eventManager;
        $this->transactionManager = $transactionManager;
        $this->validateDelete = $validateDelete;
        $this->deleteMain = $deleteMain;
        $this->deleteAttributes = $deleteAttributes;
        $this->deleteExtensions = $deleteExtensions;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity)
    {
        $this->validateDelete->execute($entityType, $entity);
        $metadata = $this->metadataPool->getMetadata($entityType);
        $connection = $this->resourceConnection->getConnectionByName($metadata->getEntityConnectionName());
        $this->transactionManager->start($connection);
        try {
            $this->eventManager->dispatch('entity_delete_before', ['entity_type' => $entityType, 'entity' => $entity]);
            $entity = $this->deleteMain->execute($entityType, $entity);
            $entity = $this->deleteAttributes->execute($entityType, $entity);
            $entity = $this->deleteExtensions->execute($entityType, $entity);
            $this->eventManager->dispatch('entity_delete_before', ['entity_type' => $entityType, 'entity' => $entity]);
            $this->transactionManager->commit();
        } catch (\Exception $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
        return $entity;
    }
}
