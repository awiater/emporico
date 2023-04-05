<?php

/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  Arrays manipulation helper class
 * 
 *  @version: 1.1					
 * 	@author Artur W				
 * 	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Helpers;

class Arrays {

    /**
     * Sort array by column value
     * 
     * @param  Array  $array Array to be sorted
     * @param  String $key   Column name
     * @return Array
     */
    static function Sort($array, $key) {
        ksort($array);
        foreach ($array as $akey => $val) {
            ksort($array[$akey]);
        }

        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            if (is_array($va) && array_key_exists($key, $va)) {
                $sorter[$ii] = $va[$key];
            } else {
                if (array_key_exists(0, $va)) {
                    $sorter[$ii] = $va[0][$key];
                }
            }
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        return $ret;
    }
    
    /**
     * Fetch array defined in file
     * 
     * @param  String $fileName  Path to file
     * @param  String $arrayName Array variable name
     * @return Array 
     */
    static function FetchArrayFromFile($fileName, $arrayName) {
        if (!file_exists($fileName)) {
            throw new \Exception($fileName . ' is not valid file name');
        }
        if ($arrayName == null) {
            return require $fileName;
        } else {
            require $fileName;
            return $$arrayName;
        }
    }
    
    /**
     * Calculate the sum of values in an array
     * 
     * @param array  $array
     * @param string $field
     * 
     * @return int|float
     */
    static function SumValues(array $array,string $field=null)
    {
        $sum =0;
        foreach($array as $key=>$child)
        {
            if (is_array($child) && array_key_exists($field, $child))
            {
               $sum+= floatval($child[$field]); 
            }else
            if (!is_array($child) && $field!=null && $field==$key)
            {
                $sum+= floatval($child);
            }else
            if (is_numeric($key))
            {
                $sum+= floatval($child);
            }
        }
        return $sum;
    }
    
    /**
     * Calculate the sum of values in an array
     * 
     * @param array  $array
     * @param string $field
     * 
     * @return int|float
     */
    static function TimesValues(array $array,$fields=[])
    {
        $sum =1;
        $fields= is_array($fields) ? $fields : [$fields];
        foreach($array as $key=>$child)
        {
            if (count($fields) > 0 && in_array($key, $fields) && is_numeric($child)) 
            {
                $sum=$sum* floatval($child);
            }else
            if (count($fields) < 1  && is_numeric($child)) 
            {
                $sum=$sum* floatval($child);
            }
        }
        return $sum;
    }
    
    /**
     * Join array elemests using delimiter
     * 
     * @param  string $delimiter
     * @param  array  $array
     * @param  string $key
     * 
     * @return string
     */
    static function implode($delimiter, array $array, $key = null) {
        $str = '';
        foreach ($array as $value) {
            if (is_array($value) && count($value) > 0) {

                if (array_key_exists($key, $value)) {
                    $value = $value[$key];
                } else {
                    $value = array_values($value);
                    $value = $value[0];
                }
            }
            $str .= $delimiter . $value;
        }
        return substr($str, 1);
    }

    /**
     * Change object (stdClass) to array
     * 
     * @param  Object $object Object which will be converted
     * @return Array
     */
    static function ObjectToArray($object) {
        return json_decode(json_encode($object), true);
    }

    /**
     * Print given array in nice way
     * 
     * @param  Array   $arr    Array to be printed
     * @param  Boolean $return Determine if dumped array will be returned or printed
     * @return String
     */
    static function Dump($arr, $return = FALSE) {
        if (!$return) {
            print("<pre>" . print_r($arr, true) . "</pre>");
        } else {
            return "<pre>" . print_r($arr, true) . "</pre>";
        }
    }

    /**
     * Parse array values against patern
     * 
     * @param  array $arr       Array to be parsed
     * @param  string $patern   Patern used in value change
     * @param  string $valueTag Tag (string) in patern which will be replaced by array item value 
     * 
     * @return array
     */
    static function ParsePatern(array $arr, $patern, $valueTag = 'value') 
    {
        foreach ($arr as $key => $value) 
        {
            if (is_string($value)) 
            {
                $arr[$key] = str_replace($valueTag, $value, $patern);
            } else {
                $arr[$key] = null;
            }
        }
        return $arr;
    }
    
    /**
     * Parse keys in array using given pattern
     * 
     * @param array  $arr
     * @param string $patern
     * @param bool   $onlyStringValues
     * @param bool   $returnKeys
     * 
     * @return array
     */
    static function parsePaternOnKeys(array $arr,string $patern,bool $onlyStringValues=FALSE,bool $returnKeys=FALSE)
    {
        foreach ($arr as $key => $value) 
        {
            if ($onlyStringValues)
            {
                if (is_string($value))
                {
                    $arr[str_replace('key',$key, $patern)]=$value;
                }
            }else
            {
                $arr[str_replace('key',$key, $patern)]=$value;
            }
            unset($arr[$key]);
        }
        return $returnKeys ? array_keys($arr) : $arr;
    }
    /**
     * Check if given keys exists in array
     * 
     * @param  Array $keys Array with keys to check
     * @param  Array $arr  Array against which check will be done
     * @return Boolean     TRUE if given key exists in array, FALSE if not
     */
    static function KeysExists(array $keys, array $arr) {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $arr)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Check if given keys exists in array and associated value is not null
     * 
     * @param  array $keys
     * @param  array $arr
     * @return boolean
     */
    static function KeysValuesExists(array $keys, array $arr)
    {
        foreach ($keys as $key) 
        {
            if (!array_key_exists($key, $arr)||(array_key_exists($key, $arr) && ($arr[$key]==null || ( is_string($arr[$key]) && strlen($arr[$key])<1)))) 
            {
                return false;
            }
        }
        return true;
    }
    
    static function searchMulti(array $arr,string $key,string $value)
    {
        $res=[];
        foreach($arr as $akey=>$avalue)
        {
            if (is_numeric($akey) && is_array($avalue))
            {
                $akey=self::searchMulti($avalue,$key,$value);
                if ($akey!=FALSE)
                {
                    $res[]=$akey;
                }
            }else
            {
                if ($akey==$key && ((is_numeric($value) && $avalue==$value)|| (!is_numeric($value) && \EMPORIKO\Helpers\Strings::contains($avalue, $value))))
                {
                    return $arr;
                }
            }
        }
        return count($res) > 0 ? $res : FALSE;
    }
    
    /**
     * Returns array with values of given keys
     * 
     * @param array $arr
     * @param mixed $keys
     * 
     * @return array
     */
    static function getValuesOfKeys(array $arr,$keys)
    {
        $keys= is_array($keys) ? $keys : [$keys];
        $r=[];
        foreach($arr as $key=>$val)
        {
            if (in_array($key, $keys))
            {
                $r[$key]=$val;
            }
        }
        return $r;
    }
    
    /**
     * Prefixes Array keys and / or values
     *
     * @param  Array  $array    Array to be transformed
     * @param  String $keyChar  Optional prefix for each array key
     * @param  String $valChar  Optional prefix for each array value
     * @return Array           
     */
    static function Prefix(array $array, $keyChar = null, $valChar = null) {
        $result = array();
        foreach ($array as $key => $val) {
            $result[$keyChar . $key] = is_string($val) ? $valChar . $val : $val;
        }

        return $result;
    }

    /**
     * Suffix Array keys and / or values
     *
     * @param  Array   $array    Array to be transformed
     * @param  String  $keyChar  Optional suffix for each array key
     * @param  String  $valChar  Optional suffix for each array value
     * @return Array 
     */
    static function Suffix(array $array, $keyChar = null, $valChar = null) {
        $result = array();
        foreach ($array as $key => $val) {
            $result[$key . $keyChar] = is_string($val) ? $valChar . $val : $val;
        }

        return $result;
    }

    /**
     * Replace string in subject with array values (keys are used as placeholders)
     * 
     * @param  array  $array   Array used for replacement
     * @param  string $subject String which will be replaced
     * @param  string $patern  Optional patern to change keys value
     * @return string
     */
    static function Replace(array $array, $subject, $patern = 'value') 
    {
        foreach ($array as $key => $val) 
        {
            if (is_string($val))
            {
               $key = str_replace('value', $key, $patern);
               $subject = str_replace($key, $val, $subject); 
            }
            
        }
        return $subject;
    }

    /**
     * Checking if given string  exists in array as key or value 
     *
     * @param  Array          $array  Array to be checked against
     * @param  String         $data   Search string 
     * @param  String|Boolean $error  Optional error message shown if string is not found
     * @return Boolean
     */
    static function Exists(array $array, $data, $result = 'bool') {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                return self::Exists($array, $data, $result);
            } else
            if ($key == $data) {
                if ($result == 'type') {
                    return 'KEY';
                } else {
                    return TRUE;
                }
            } else
            if ($value == $data) {
                if ($result == 'type') {
                    return 'VALUE';
                } else {
                    return true;
                }
            }
        }
        return FALSE;
    }

    /**
     * Delete given keys from array
     *
     * @param  Array        $array Array from which keys will be deleted
     * @param  String|Array $data  Array with keys or single key
     * @return Array        New array without given keys
     */
    static function Trim(array $array, $data) {
        foreach (array_keys(is_array($data) ? $data : array($data)) as $value) {
            if (array_key_exists($value, $array)) {
                unset($array[$value]);
            }
        }
        return $array;
    }

    /**
     * Changes values of array to given one
     *
     * @param  Array          $array   Array which will be filled
     * @param  String|Array   $filler  String or array with filling value(s)
     * @param  String|Boolean $error   Optional error message if filler array have less keys than input array
     * @return Array                   New array with new values
     */
    static function Fill(array $array, $filler, $error = null) {
        $error = $error == null ? 'ARRAY ITEMS COUNT NOT MATCH WITH FILLER ARRAY COUNT' : $error;
        if (is_array($filler) && count($array) !== count($array)) {
            if ($error !== false) {
                throw new \Exception($error);
            } else {
                return false;
            }
        }
        $keysA = array_keys($array);
        $keysB = is_array($filler) ? array_keys($filler) : null;
        for ($i = 0; $i < count($array); $i++) {
            $array[$keysA[$i]] = $keysB !== null ? $filler[$keys[$i]] : $filler;
        }
        return $array;
    }

    /**
     * Returning array keys values
     *
     * @param  Array         $array  Input array from which values will be returned
     * @param  String|Array  $data   String or array with key(s) names
     * @param  String        $error  Optional error message shown if key is not found
     * @return Array|Object          Array with key(s) value(s)
     */
    static function Get(array $array, $data, $error = null) {
        $result = [];
        $data = is_array($data) ? $data : [$data];
        foreach (is_array($data) ? $data : array($data) as $key => $value) {
            switch (self::Exists($array, $value, $error)) {
                case 'KEY': $result[is_numeric($key) ? $value : $key] = $array[$value];
                    break;
                case 'VALUE': $result[] = array_search($value, $array);
                    break;
                default: $result[] = null;
                    break;
            }
        }

        return is_array($result) && count($result) == 1 ? $result[self::getFirst($result, true, false)] : $result;
    }

    /**
     * Return array without given keys
     *
     * @param  Array        $array Input array
     * @param  String|Array $data  Array or String with key(s) name(s)
     * @return Array
     */
    static function getDiff(array $array, $data) {
        foreach (is_array($data) ? $data : [$data] as $value) {
            if (array_key_exists($value, $array)) {
                unset($array[$value]);
            }
        }
        return $array;
    }

    /**
     * Returning array Key/Value by given Integer index
     * 
     * @param  Array          $array       Input array
     * @param  String         $keyOrValue  Return type (KEY,VALUE)
     * @param  Integer        $index       Index in array
     * @param  String|Boolean $error       Error message showed if user use invalid return type
     * @return Object
     */
    static function getFromIndex(array $array, $keyOrValue, $index, $error = null) {
        $error = $error == null ? '' : $error;
        if ($keyOrValue == 'KEY') {
            $array = array_keys($array);
        } else
        if ($keyOrValue == 'VALUE') {
            $array = array_values($array);
        } else {
            if ($error !== false) {
                throw new \Exception($error);
            } else {
                return false;
            }
        }
        return count($array) <= $index ? $array[$index] : false;
    }

    /**
     * Returning type of array (ASSOC,INDX)
     * 
     * @param  Array  $array  Input array
     * @return String         Type of array as string (ASSOC,INDX)
     */
    static function getType(array $array) {
        $array = array_keys($array);
        return is_numeric($array[0]) ? 'INDX' : 'ASSOC';
    }
    
    /**
     * Determines if array is associative
     *  
     * @param  array $array
     * @return bool
     */
    static function  isAssoc(array $array){
        return self::getType($array)=='ASSOC';
    }
    
    /**
     * Change array (and sub arrays) to indexed
     * 
     * @param array $array
     * 
     * @return array
     */
    static function toIndexed(array $array)
    {
        $arr=[];
        foreach($array as $value)
        {
            if (is_array($value))
            {
                $arr[]=self::toIndexed($value);
            } else
            {
               $arr[]=$value; 
            }
        }
        return $arr;
    }

    /**
     * Get last Key or/and value from array
     * 
     * @param  Array   $array  Input array
     * @param  Boolean $key    Optional if TRUE key name will be returned
     * @param  Boolean $value  Optional if TRUE value will be returned
     * @return Object
     */
    static function getLast(array $array, $key = true, $value = true) {
        $key = $key == false && $value == false ? true : $key;
        $value = $key == false && $value == false ? true : $value;
        $array_key = array_keys($array);
        $array_key = $array_key[count($array_key) - 1];
        if ($key == true && $value == false) {
            return $array_key;
        } else
        if ($key == false && $value == true) {
            return $array[$array_key];
        } else
        if ($key == true && $value == true) {
            return array($array_key => $array[$array_key]);
        }
    }

    /**
     * Get first Key or/and value from array
     * 
     * @param  Array   $array  Input array
     * @param  Boolean $key    Optional if TRUE key name will be returned
     * @param  Boolean $value  Optional if TRUE value will be returned
     * @return Object
     */
    static function getFirst(array $array, $key = true, $value = true) {
        $key = $key == false && $value == false ? true : $key;
        $value = $key == false && $value == false ? true : $value;
        $array_key = array_keys($array);
        $array_key = $array_key[0];
        if ($key == true && $value == false) {
            return $array_key;
        } else
        if ($key == false && $value == true) {
            return $array[$array_key];
        } else
        if ($key == true && $value == true) {
            return array($array_key => $array[$array_key]);
        }
    }

    /**
     * Insert item in array position
     *
     * @param Array    $array     Input array
     * @param Integer  $position  Position (index) in array from which item will be inserted
     * @param Object   $insert    Insert (item) value
     */
    static function Insert($array, int $position, $insert) {
        $res = [];
        foreach ($array as $key => $value) {
            if ($key == $position) {
                $res[] = $insert;
            }
            $res[] = $value;
        }
        return $res;
    }

    /**
     * Insert element into array after given key
     * 
     * @param type $array
     * @param type $arrKey
     * @param type $insert
     * 
     * @return boolean
     */
    static function InsertAfter(&$array, $arrKey, $insert) {
        $insert = !is_array($insert) ? [$insert] : $insert;
        if (count($insert) == 1) {
            $insert = [array_key_first($insert), (array_values($insert))[0]];
        }
        if (!array_key_exists($arrKey, $array)) {
            return FALSE;
        }
        $arr = [];
        $arr_key = 0;
        $arr_keys = array_keys($array);

        foreach ($array as $key => $value) {
            $arr[] = $value;
            $arr_key++;
            if ($key == $arrKey) {
                $arr[] = $insert[1];
                array_splice($arr_keys, $arr_key, 0, $insert[0]);
            }
        }
        $array = array_combine($arr_keys, $arr);
    }

    /**
     * Insert element into array before given key
     * 
     * @param type $array
     * @param type $arrKey
     * @param type $insert
     * 
     * @return boolean
     */
    static function InsertBefore(&$array, $arrKey, $insert) {

        $insert = !is_array($insert) ? [$insert] : $insert;
        if (count($insert) == 1) {
            $insert = [array_key_first($insert), (array_values($insert))[0]];
        }
        if (!array_key_exists($arrKey, $array)) {
            return FALSE;
        }
        $arr = [];
        $arr_key = 0;
        $arr_keys = array_keys($array);

        foreach ($array as $key => $value) {
            if ($key == $arrKey) {
                $arr[] = $insert[1];
                array_splice($arr_keys, $arr_key, 0, $insert[0]);
            }
            $arr[] = $value;
            $arr_key++;
        }
        $array = array_combine($arr_keys, $arr);
    }

    /**
     * Export array to flat string
     * 
     * @param  array  $array
     * @param  bool   $noKeys
     * @param  string $keysJoiner
     * @param  string $rowsJoiner
     * @return string
     */
    static function Flatten($array, $noKeys = FALSE,$keysJoiner='=',$rowsJoiner=' | ') {
        if (is_object($array)) {
            $array = json_decode(json_encode($array), true);
        }
        if (is_array($array)) {
            if ($noKeys) {
                return implode($rowsJoiner, array_values($array));
            }
            $str = '';
            foreach ($array as $key => $value) {
                $str .= $key . $keysJoiner . $value . $rowsJoiner;
            }
            return substr($str, 0, strlen($str) - 3);
        }
        return '';
    }

    /**
     * Create array from valid Flatten string
     * 
     * @param  String $arrStr      Flattened array string
     * @param  String $keyValueSep Character which seperate keys from values
     * 
     * @return Array
     */
    static function fromFlatten($arrStr, $keyValueSep = '=') {
        $arr = [];
        foreach (explode('|', $arrStr) as $value) {
            if (Strings::contains($value, $keyValueSep)) {
                $value = explode($keyValueSep, $value);
                $arr[$value[0]] = $value[1];
            } else {
                $arr[] = $value;
            }
        }
        return $arr;
    }
    
    /**
     * Generate Array from string
     * 
     * @param string       $string
     * @param string|array $delimiters
     * 
     * @return array
     */
    static function fromString(string $string,$delimiters=[])
    {
        $delimiters= is_array($delimiters) ? $delimiters : [$delimiters];
        if (count($delimiters) < 1)
        {
            $delimiters=[',',';'];
        }
        $arr=[];
        if (Strings::isJson($string))
        {
            $arr= json_decode($string,TRUE);
        }else
        {
            foreach($delimiters as $delimiter)
            {
                if (Strings::contains($string, $delimiter))
                {
                    $arr=explode($delimiter,$string);
                    goto export_array;
                }
            }
        }
        export_array:
        if (strlen($string) > 0 && count($arr) < 1)
        {
            $arr=[$string];
        }
        return is_array($arr) ? $arr : [];    
    }
    
    /**
     * Create array or stdClass  from valid JSON string
     * 
     * @param  String         $jsonStr Valid JSON string
     * @param  Boolean        $isobj   Determine if function return Array (False) or stdClass(True)
     * @return Array|stdClass
     */
    static function fromJson($jsonStr, $isobj = false) {
        return json_decode($jsonStr, $isobj);
    }

    /**
     * Determine if array have string keys or not
     * 
     * @param  Array   $arr Array to check
     * @return Boolean 
     */
    static function hasStringKeys(array $arr) {
        return count(array_filter(array_keys($arr), 'is_string')) > 0;
    }

    /**
     * Parse Array to Json String
     * 
     * @param  Array $array Input array
     * @return String       Valid JSON string
     */
    static function toJson(array $array) {
        return json_encode($array);
    }
    
    /**
     * Convert given array to CSV File
     * 
     * @param  array  $data
     * @param  string $fileName
     * @param  bool   $includeColumnNames
     * @return type
     */
    static function toCSVFile(array $data,$fileName=null,$includeColumnNames=TRUE)
    {
        $fileName=$fileName==null ? '@storage/temp/'.\EMPORIKO\Helpers\Strings::createUID().'.csv' : $fileName;
        $file=fopen(parsePath($fileName,TRUE), 'w');
        
	foreach (array_values($data) as $key=> $lines) 
	{
            if ($includeColumnNames && $key==0)
            {
                fputcsv($file, array_keys($lines));
            }
            if (is_array($lines))
            {
                fputcsv($file, $lines);
            }	
        }
        fclose($file);
        return $fileName;
    }
    
    /**
     * Convert given CSV file to array
     * 
     * @param string $fileName
     * @param bool   $firstRowAsColumnNames
     * 
     * @return array
     */
    static function fromCSVFile(string $fileName,bool $firstRowAsColumnNames=TRUE)
    {
        $fileName= parsePath($fileName,TRUE);
        $arr=[];
        if (file_exists($fileName))
        {
            $file = fopen($fileName, 'r');
            $lineNr=0;
            $headers=[];
            while (($line = fgetcsv($file)) !== FALSE) 
            {
                if ($lineNr==0 && $firstRowAsColumnNames)
                {
                    $headers=$line;
                    $lineNr=1;
                    goto next_item;
                }
                
                if ($firstRowAsColumnNames && count($headers)==count($line))
                {
                    $arr[]= array_combine($headers, $line);
                } else 
                {
                    $arr[]=$line;
                }
                next_item:
            }
            fclose($file);
        }
        return $arr;
    }
    /**
     * Parse Array to INI String
     * 
     * @param  Array $array Input array
     * @return String
     */
    static function toINI(array $array, array $parent = []) {
        $out = '';
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $sec = array_merge((array) $parent, (array) $k);
                $out .= '[' . join('.', $sec) . ']' . PHP_EOL;
                $out .= self::toINI($v, $sec);
            } else {
                $out .= "$k=$v" . PHP_EOL;
            }
        }
        return $out;
    }

    /**
     * Parse Array to Object
     *
     * @param  Array   $array
     * @return Object
     */
    static function toObject(array $array) {
        return json_decode(json_encode($array));
    }
    
    /**
     * Generates HTML table from given array
     * 
     * @param array $data
     * @param array $heading
     * 
     * @return string
     */
    static function toTable(array $data,array $heading=[]){
        if (count($data) < 1)
        {
            return null;
        }
        $table = new \CodeIgniter\View\Table();
        if (count($heading) > 0)
        {
            $table->setHeading($heading);
        }
        foreach($data as $row)
        {
            $table->addRow($row);
        }
        return $table->generate();
    }
    
    /**
     * Parse Array to String using delimiter
     *
     * @param Array    $array     Input Array
     * @param Optional $delimiter Items delimiter
     */
    static function toString(array $array, $delimiter = null) {
        return implode($delimiter, $array);
    }

    /**
     *  Merge 2 or more arrays
     * 
     *  @param  Array $arrays Collection of arrays to be merged togehther
     *  @return Array
     */
    static function Merge(... $arrays) {
        $override = is_bool($override) ? $override : FALSE;
        if ($arrays == null) {
            throw new \Exception('Arguments are not set');
        }
        if (count($arrays) < 1) {
            throw new \Exception('Arguments are not set');
        }
        if (!is_array($arrays[0])) {
            throw new \Exception('Arguments are not arrays');
        }
        $array = $arrays[0];
        for ($i = 1; $i < count($arrays); $i++) {
            if (is_array($arrays[$i])) {
                foreach ($arrays[$i] as $key => $value) {
                    $array[$key] = $value;
                }
            }
        }
        return $array;
    }

}

?>