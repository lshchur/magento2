<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write\Create;

use Magento\Framework\EntityManager\Operation\AttributePool;
use Magento\Framework\EntityManager\HydratorPool;

/**
 * Class CreateAttributes
 */
class CreateAttributes
{
    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var AttributePool
     */
    private $attributePool;

    /**
     * CreateAttributes constructor.
     *
     * @param HydratorPool $hydratorPool
     * @param AttributePool $attributePool
     */
    public function __construct(
        HydratorPool $hydratorPool,
        AttributePool $attributePool
    ) {
        $this->hydratorPool = $hydratorPool;
        $this->attributePool = $attributePool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     */
    public function execute($entityType, $entity, $data = [])
    {
        $hydrator = $this->hydratorPool->getHydrator($entityType);
        $entityData = array_merge($hydrator->extract($entity), $data);
        $actions = $this->attributePool->getActions($entityType, 'create');
        foreach ($actions as $action) {
            $action->execute($entityType, $entityData);
        }
        $entity = $hydrator->hydrate($entity, $entityData);
        return $entity;
    }
}
