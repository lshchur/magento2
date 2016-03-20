<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Operation\Write\Update;

use Magento\Framework\EntityManager\Operation\ValidatorPool;

/**
 * Class ValidateUpdate
 */
class ValidateUpdate
{
    /**
     * @var ValidatorPool
     */
    private $validatorPool;

    /**
     * ValidateUpdate constructor.
     *
     * @param ValidatorPool $validatorPool
     */
    public function __construct(
        ValidatorPool $validatorPool
    ) {
        $this->validatorPool = $validatorPool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     */
    public function execute($entityType, $entity)
    {
        $validators = $this->validatorPool->getValidators($entityType, 'update');
        foreach ($validators as $validator) {
            $validator->execute($entityType, $entity);
        }
        return $entity;
    }
}
