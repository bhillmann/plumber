<?php
/**
 * Created by IntelliJ IDEA.
 * User: bhillmann
 * Date: 09/01/15
 * Time: 09:25
 */

namespace Plumber\Custom;

abstract class Filter {

    public function __invoke($value) {
        return $this->apply($value);
    }

    public function apply($value) {
        throw new \Exception("Child class must implement the transform function");
    }
}
