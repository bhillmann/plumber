<?php
/**
 * Created by IntelliJ IDEA.
 * User: bhillmann
 * Date: 09/01/15
 * Time: 09:25
 */

namespace Plumber\Custom;

use Plumber\Pipe\TransformPipe;

abstract class Transform extends TransformPipe {

    public function __construct() {
        parent::__construct($this ,'transform');
    }

    public function transform() {
        throw new \Exception("Child class must implement the transform function");
    }
}
