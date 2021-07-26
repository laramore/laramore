<?php
/**
 * Define enum field.
 *
 * @author Samy Nastuzzi <samy@nastuzzi.fr>
 *
 * @copyright Copyright (c) 2021
 * @license MIT
 */

namespace Laramore\Contracts\Field;

use Laramore\Elements\{
    EnumElement, EnumManager
};

interface EnumField extends Field
{
    /**
     * Define all elements for this enum field.
     *
     * @param array<string>|array<EnumElement>|EnumManager $elements
     * @return self
     */
    public function elements($elements);

    /**
    * Return the element manager for this field.
    *
    * @return EnumManager
    */
   public function getElements(): EnumManager;

   /**
    * Return elements.
    *
    * @return array<EnumElement>
    */
   public function getValues(): array;

   /**
    * Return an element by its name.
    *
    * @param mixed $key
    *
    * @return EnumElement
    */
   public function getElement($key): EnumElement;

   /**
    * Return an element by its value.
    *
    * @param mixed $key
    *
    * @return EnumElement
    */
   public function findElement($key): EnumElement;

   /**
    * Indicate if an element exists.
    *
    * @param mixed $key
    *
    * @return boolean
    */
   public function hasElement($key): bool;

   /**
     * Return the default value.
     *
     * @return mixed
     */
    public function getDefaultValue();
}
