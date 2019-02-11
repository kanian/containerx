<?php
namespace Kanian\ContainerX;

use ArrayAccess;
use Kanian\ContainerX\Container;

/**
 * A psr-11 contaimer that implements ArrayAccess to allow the use of the container 
 * following an array metaphore.
 */
class ContainerX extends Container implements ArrayAccess{
    /**
     * Tells if the offset in the container is set to anything
     *
     * @param string $offset
     * @return void
     */
    public function offsetExists ( $offset ){
        return $this->has($offset);
    }
    /**
     * Sets the dependency at the given offset.
     *
     * @param string $offset
     * @return mixed the dependency
     */
     public function offsetGet ( $offset){
        return $this->get($offset);
    }
    /**
     * Sets the dependency at the given offset.
     *
     * @param string $offset
     * @param mixed $dependency
     * @return void
     */
     public function offsetSet ( $offset , $dependency ){
        if (is_null($offset)) {
            $this->set($dependency);
        } else {
            $this->set($offset, $dependency);
        }
     }
     /**
      * Removes dependency from container
      *
      * @param string $offset
      * @return void
      */
     public function offsetUnset ( $offset ){
        $this->unset($offset);
     }

}
