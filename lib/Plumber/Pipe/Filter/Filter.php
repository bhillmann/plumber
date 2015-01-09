<?php
/**
 * Created by IntelliJ IDEA.
 * User: bhillmann
 * Date: 09/01/15
 * Time: 09:30
 */

namespace Plumber\Pipe\Filter;

use Plumber\Pipe\FilterPipe;

abstract class Filter extends FilterPipe {

    public function __construct() {
        parent::construct($this, "filter");
    }

    public function filter() {
        throw new \Exception("Child class must implement the filter function");
    }
}
