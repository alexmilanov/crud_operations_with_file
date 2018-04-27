<?php

class AutoLoader {
    public function __construct() {
        spl_autoload_register(array($this, 'loader'));
    }

    private function loader($class) {
        include "$class.php";
    }
}