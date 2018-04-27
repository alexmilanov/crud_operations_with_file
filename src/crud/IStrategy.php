<?php

interface IStrategy {
    public function getData($id);
    public function save($id);
    public function delete($id);
    public function checkDuplicates();
}