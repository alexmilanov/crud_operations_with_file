<?php

class StorageFactory {
    public static function createStorage($context, $additionalData) {
        $instance = null;
        switch($context) {
            case 'json':
                $instance = new JSONFileStorage($additionalData);
                break;
            default:
                throw new Exception('Unknown strategy implementation class name');
        }

        if(!$instance instanceof IStrategy) {
            throw new Exception(get_class($instance) . " should implements IStrategy interface");
        }

        return $instance;
    }
}