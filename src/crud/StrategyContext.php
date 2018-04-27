<?php

/**
 * Strategy Design Pattern is used to minimize the effort for adding/changing
 *  the way we manipulate the information (ex. changed storage from json in file to DB)
 */
class StrategyContext {
    private $instance = null;

    public function __construct($context, $additionalData) {
        require_once(dirname(__FILE__) . '/AutoLoader.php');
        $autoLoader = new AutoLoader();

        $this->instance = StorageFactory::createStorage($context, $additionalData);
    }

    public function getData($id = null) {
        return $this->instance->getData($id);
    }

    public function save($id) {
        $result = $this->instance->save($id);

        return $result;
    }

    public function delete($id) {
        return $this->instance->delete($id);
    }

    public function checkDuplicates() {
        $this->instance->checkDuplicates();
    }
}
