<?php
/**
 * Created by IntelliJ IDEA.
 * User: bhillmann
 * Date: 13/01/15
 * Time: 13:39
 */

namespace Plumber\Pipe;

// TODO: In the future, will be able to allow custom reduce functions
// The custom function would look for a key for each value and emit the new key
class ReducePipe extends TransformPipe {

    public function __construct() {
        parent::construct(array($this, 'reduce'));
    }

    public function reduce($value, $key) {
        return self::drain();
    }
}
