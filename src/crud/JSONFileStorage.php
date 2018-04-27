<?php

class JSONFileStorage implements IStrategy {
    private $localFilePath;
    private $remoteFilePath;
    private $jsVarName;
    private $fields;
    private $jsonStartWith;
    private $uniqueFields;

    public function __construct($additionalData) {
        if(!isset($additionalData['localFilePath'])) throw new Excpetion("Local File Path is mandatory");

        $this->localFilePath = $additionalData['localFilePath'];

        if(!isset($additionalData['remoteFilePath'])) {
            $this->remoteFilePath = $this->localFilePath;
        }
        else {
            $this->remoteFilePath = $additionalData['remoteFilePath'];
        }

        if(!isset($additionalData['json']['jsVariableName'])) throw new Exception('jsVariableName is mandatory');
        $this->jsVarName = $additionalData['json']['jsVariableName'];

        if(!isset($additionalData['json']['fields'])) throw new Exception("at least one field should be set in fields setting");

        $this->fields = $additionalData['json']['fields'];

        $this->jsonStartWith = isset($additionalData['json']['jsonStartWith']) ? $additionalData['json']['jsonStartWith'] : '[';

        foreach($this->fields as $fieldName => $fieldSettings) {
            if(is_array($fieldSettings) && in_array('unique', $fieldSettings)) $this->uniqueFields[] = $fieldName;
        }

    }

    public function getData($id = null) {
        $fileContent = Common::getFileContent($this->localFilePath);

        $data = Common::getJSONDataAsArray($fileContent, $this->jsonStartWith);

        if(null === $id) {
            return $data;
        }

        if(!array_key_exists($id, $data)) throw new Exception("Index $id doesn't exists");

        return $data[$id];
    }

    public function save($id = null) {
        $dataRecords = $this->getData(null);

        $fieldsNameAndValue = array();

        foreach($this->fields as $fieldName => $fieldSettings) {
            if(is_array($fieldSettings)) {
                if(in_array('required', $fieldSettings) &&
                        (!isset($_REQUEST[$fieldName]) || empty($_REQUEST[$fieldName]))
                  )
                    throw new Exception("$fieldName is required");

                 $fieldsNameAndValue[$fieldName] = in_array('int', $fieldSettings) ? +$_REQUEST[$fieldName] : $_REQUEST[$fieldName];
                 continue;
            }

            $fieldsNameAndValue[$fieldSettings] = $_REQUEST[$fieldSettings];
        }

        if(!$id) {
            $dataRecords[] = $fieldsNameAndValue;
        }
        else {
            $dataRecords[$id] = array_merge($dataRecords[$id], $fieldsNameAndValue);
        }

        return $this->writeJSONToFile($dataRecords);
    }

    public function delete($id) {
        $dataRecords = $this->getData();
        if(!isset($dataRecords[$id])) {
            throw new Exception("Record with $id doesn't exists");
        }

        unset($dataRecords[$id]);

        /**
         * Reset the values to start from 0,
         * otherwise the encoded json would be something like
         * var a = {
         *  '1': {
         *
         *  }
         * }
         */

        $dataRecords = array_values($dataRecords);

        $this->writeJSONToFile($dataRecords);
    }

    public function checkDuplicates()
    {
        $dataResults = $this->getData(null);

        header("Content-Type: text/html; charset=utf-8");
        if ($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest")
        {
            $result = '{"error_code":0,"error_message":""}';

            foreach($dataResults as $dataKey => $dataResult) {
                foreach($this->uniqueFields as $fieldName) {

                    if($dataKey != $_REQUEST['id'] && $dataResult[$fieldName] == $_REQUEST[$fieldName]) {
                        echo
                            '{"error_code":"1",
                              "error_message":"Record with ' . $fieldName . ' = ' . $_REQUEST[$fieldName] . ' already exists"}';
                        exit;
                    }
                }

            }

            echo $result;
            exit;
        }
        exit;
    }

    private function writeJSONToFile($dataRecords) {
        $json = $this->formatDataToJSON($dataRecords);

        Common::putFileContent($json, $this->localFilePath);
    }

    private function formatDataToJSON($dataRecords) {
        $json = json_encode($dataRecords, JSON_PRETTY_PRINT);
        return Common::formatJSONAsJavascriptSyntax($json, $this->jsVarName);
    }
}
