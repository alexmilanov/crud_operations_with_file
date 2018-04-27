<?php

class Common {

    public static function getFileContent($path) {
        if(!file_exists($path)) {
            throw new Exception("$path doesn't exists");
        }

        return file_get_contents($path);
    }

    public static function getJSONDataAsArray($content, $jsSyntaxStartWith = '[') {
        $content = strpbrk($content, $jsSyntaxStartWith); //Remove the var ... part of the JSON
        $content = rtrim($content);

        if(substr($content, -1) === ';') $content = substr($content, 0, -1);

        return json_decode($content, 1);
    }

    public static function formatJSONAsJavascriptSyntax($json, $variableName) {
        return "var $variableName = " . $json . ";\n";
    }

    public static function putFileContent($content, $path) {
        if(!file_exists($path) || !is_readable($path) || !is_writable($path)) {
            throw new Exception(basename($path) . " doesn't exists");
        }

        if(false === file_put_contents($path, $content)) {
            throw new Exception("Cannot put file content");
        }
    }

}
