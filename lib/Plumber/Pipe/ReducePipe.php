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
use Plumber\Pipe;

class ReducePipe extends Pipe {
    protected $reduced = [];

    public function current() {
        while (parent::valid()) {
            $this->reduced[] = parent::current();
            parent::next();
        }
        return $this->reduced;
    }
}
