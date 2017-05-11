<?php

namespace Unimatrix\Cake\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;

/**
 * Nullable
 * insert null to fields that default to null instead of ''
 *
 * Example:
 * ----------------------------------------------------------
 * // add in the initialize function from a model table file
 * $this->addBehavior('Unimatrix/Cake.Nullable');
 *
 * @author Flavius
 * @version 0.1
 */
class NullableBehavior extends Behavior
{
    public function beforeSave(Event $event, Entity $entity) {
        $schema = $this->_table->getSchema();
        foreach($entity->toArray() as $field => $value)
            if($schema->isNullable($field))
                if($value === '')
                    $entity->set($field, null);
    }
}
