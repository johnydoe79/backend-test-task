<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class EntityExists extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public string $entityClass;
    public $message = 'The entity with id "{{ id }}" does not exist.';
    public function __construct($options = null)
    {
        parent::__construct($options);
        if (!isset($this->entityClass)) {
            throw new \InvalidArgumentException('The "entityClass" option is required.');
        }
    }

    public function getRequiredOptions()
    {
        return ['entityClass'];
    }
}
