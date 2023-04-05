<?php
/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  Arrays manipulation helper class
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
 
namespace EMPORIKO\Helpers;

class MathHelper
{
    
    public static function calcString($string)
    {
        $result = 0;
        $string = preg_replace("/[^a-z0-9+\-.*\/()%]/","",$string);
        
        $string = preg_replace("/([a-z])+/i", "\$$0", $string); 
        // convert percentages to decimal
        $string = preg_replace("/([+-])([0-9]{1})(%)/","*(1\$1.0\$2)",$string);
        $string = preg_replace("/([+-])([0-9]+)(%)/","*(1\$1.\$2)",$string);
        $string = preg_replace("/([0-9]{1})(%)/",".0\$1",$string);
        $string = preg_replace("/([0-9]+)(%)/",".\$1",$string);
        if ( $string != "" )
        {
            $result = @eval("return " . $string . ";" );
        }
        if ($result == null) 
        {
            throw new Exception("Unable to calculate equation");
        }
        return $result;
    }
    
   static function roundUP($value)
   {
       if (is_string($value))
       {
           $value= self::calcString($value);
       }
       return ceil($value);
   }
   
   static function roundDown($value)
   {
       if (is_string($value))
       {
           $value= self::calcString($value);
       }
       return floor($value);
   }
   
   static function round($value,$precision=2)
   {
       if (is_string($value))
       {
           $value= self::calcString($value);
       }
       return round($value,$precision);
   }
   
   static function roundVar($value)
   {
       if (is_string($value))
       {
           $value= self::calcString($value);
       }
       $value=round($value,2);
       $decimal=Strings::afterLast($value, '.');
       $decimal= strlen($decimal) > 1 ? $decimal : $decimal.'0';
       $decimal=[substr($decimal,0,1),substr($decimal,1,1)];
       if ($decimal[1] > 5)
       {
           $decimal[0]++;
           $decimal[1]=Strings::before($value,'.');
           if ($decimal[0]>9)
           {
               $decimal[1]++;
               $decimal[0]=0;
           }
           return round($decimal[1].'.'.$decimal[0],2);
       }
       return $value;
   }
   
}