<?php

/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Libraries\Scheduler;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class SchedulerJob
{
    /**
     * Job unique ID
     * 
     * @var string
     */
    public $id;
    
    /**
     * Minutes (0 - 59)
     *
     * @var int
     *
     */
    public $minutes;
    
    /**
     * Hours (0 - 23)
     *
     * @var int
     */
    public $hours;
    
    /**
     * Day of month (1 - 31)
     *
     * @var String/int
     */
    public $dayOfMonth;
    
    /**
     * Month (1 - 12)
     *
     * @var int
     */
    public $month;
    
    /**
     * Command to run
     * 
     * @var type
     */
    public $command;
    
    /**
     * Day of week (0 - 6) (0 or 6 are Sunday to Saturday, or use names)
     *
     * @var int
     */
    public $dayOfWeek;
    
    /**
     * Scheduler
     * 
     * @var Scheduler
     */
    private $_scheduler;
    
    
    public static function init(Scheduler $scheduler)
    {
        return new SchedulerJob($scheduler);
    }
    
    function __construct(Scheduler $scheduler) 
    {
        $this->_scheduler=$scheduler;
    }
    
    function write()
    {
        return $this->_scheduler->saveJob($this);
    }
    
   
    function toJSON()
    {
       return json_encode($this);
    }
    
    function readFromExternal($data)
    {
       if (is_array($data))
       {
           return $this;
       }
       if (file_exists(parsePath($data)))
       {
          $data= file_get_contents(parsePath($data));
       }
       if (Str::isJson($data))
       {
           $data= json_decode($data,TRUE);
           foreach (is_array($data) ? $data : [] AS $key => $value)
           {
               if (property_exists($this, $key))
               {
                   $this->{$key}=$value;
               }
           }
       }else
       {
            $matches=explode(' ',$data);
            if (count($matches)>=5)
            {
           
                $this->setMinutes($matches[0])
                    ->setHours($matches[1])
                    ->setDayOfMonth($matches[2])
                    ->setMonths($matches[3])
                    ->setDayOfWeek($matches[4]);
                if (Str::contains($data, 'http'))
                {
                    $matches= Str::afterLast($data, site_url());
                    $matches= explode('/', $matches);
                    $params=[];
                    if (count($matches) > 2)
                    {
                        $matches=$params;
                        unset($params[0]);
                        unset($params[1]);
                    }
                    $this->setCommand($matches[0], $matches[1], $params)
                         ->setJobID($this->getDateTime());
                }
            }
       }
       return $this;
    }
    
    /**
     * Set Job ID
     * 
     * @param string $id
     */
    function setJobID($id)
    {
        $this->id=$id==null ? formatDate() : $id;
        return $this;
    }
    
    function setCustomTime($timeDef)
    {
         if (\EMPORIKO\Libraries\Scheduler\Expression::init($timeDef)->isValid())
         {
            $timeDef=explode(' ',$timeDef);
            $timeDef[0]= str_replace('*', '', $timeDef[0]);
            $timeDef[1]= str_replace('*', '', $timeDef[1]);
            $this->setMinutes(Str::contains('/', $timeDef[0]) ? Str::afterLast($timeDef[0], '/') : $timeDef[0],Str::contains('/', $timeDef[0]));
            $this->setHours(Str::contains('/', $timeDef[1]) ? Str::afterLast($timeDef[1], '/') : $timeDef[1],Str::contains('/', $timeDef[1]));
            $this->setDayOfMonth($timeDef[2]);
            $this->setDayOfWeek($timeDef[3]);
         }
        
    }
    
    function setDateTime($timeDate)
    {
        if (Str::startsWith($timeDate, '+'))
        {
            $timeDate=formatDate('now',$timeDate);
        }
        $this->setHours(convertDate($timeDate, 'DB','H'))
             ->setMinutes(convertDate($timeDate, 'DB','i'))
             ->setMonths(convertDate($timeDate, 'DB','m'))
             ->setDayOfMonth(convertDate($timeDate, 'DB','d'))
             ->setDayOfWeek(convertDate($timeDate, 'DB','w'));
        return $this;
    }
    
    function getDateTime()
    {
        return convertDate(formatDate(),'DB','Y')
                .(intval($this->month) < 10 ? '0' : '').intval($this->month)
                .(intval($this->dayOfMonth) < 10 ? '0' : '').intval($this->dayOfMonth)
                .(intval($this->hours) < 10 ? '0' : '').intval($this->hours)
                .(intval($this->minutes) < 10 ? '0' : '').intval($this->minutes)
                .'00';
    }
    
    /**
     * Sets the number of minutes.
     * 
     * @param Int $minutes
     * 
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setMinutes($minutes,$repeat=FALSE)
    {
        if (is_numeric($minutes) && ((int)$minutes < 0 || (int)$minutes > 59))
        {
            throw new \InvalidArgumentException(
                'The minutes value is not valid'
            );
        }
        
        $this->minutes = ($repeat ? '*/':'').$minutes;
        return $this;
    }
    
    
    /**
     * Sets the number of hours.
     * 
     * @param String $hours
     * 
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setHours($hours,$repeat=FALSE)
    {
        if (is_numeric($hours) && ((int)$hours < 0 || (int)$hours > 23))
        {
            throw new \InvalidArgumentException(
                'The hours value is not valid'
            );
        }
        
        $this->hours = ($repeat ? '*/':'').$hours;
        return $this;
    }
    
    
    /**
     * Sets the day of month.
     * 
     * @param Int $dayOfMonth
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setDayOfMonth($dayOfMonth)
    {
        if (is_numeric($dayOfMonth) && ((int)$dayOfMonth < 1 || (int)$dayOfMonth > 31))
        {
            throw new \InvalidArgumentException(
                'The day of month is not valid'
            );
        }
        
        $this->dayOfMonth = $dayOfMonth;
        return $this;
    }
    
    
    /**
     * Sets the month number.
     * 
     * @param Int $months
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setMonths($month)
    {
        if (is_numeric($month) && ((int)$month < 1 || (int)$month > 12))
        {
            throw new \InvalidArgumentException(
                'The month value is not valid'
            );
        }
        
        $this->month = $month;
        return $this;
    }
    
    
    /**
     * Sets the day of week.
     * 
     * @param Int $dayOfWeek
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setDayOfWeek($dayOfWeek)
    {
        // 0 and 7 are both valid for Sunday
        if (is_numeric($dayOfWeek) && ((int)$dayOfWeek < 0 || (int)$dayOfWeek > 7))
        {
            throw new \InvalidArgumentException(
                'The day of week is not valid'
            );
        }
        
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }
    
    public function setCommand($controller,$action,array $params=[])
    {
        if (is_object($controller))
        {
            $controller= get_class($controller);
        }
        $this->command=['controller'=>$controller,'action'=>$action,'params'=>$params];
        return $this;
    }
    
    public function setBasicCommand($command)
    {
        if (is_string($command) && Str::contains($command, '::'))
        {
            $arr=['controller'=>'','action'=>'','params'=>[]];
            $arr['controller']=Str::before($command, '::');
            $arr['action']=Str::afterLast($command, '::');
            if (Str::contains($command, '@'))
            {
                $arr['params']= explode(',', Str::afterLast($command, '@'));
                $arr['action']=Str::before($arr['action'], '@');
            }
            $this->command=$arr;
        }else
        if (is_array($command) && Arr::keysExists(['controller','action'],$command))
        {
            $this->command=$command;
        }else
        {
            $this->command=['controller'=>'system','action'=>$command];
        }
        return $this;
    }
    
    public function execute()
    {
        loadModuleFromArray($this->command);
    }
    
    public function exist()
    {
        $this->_scheduler->jobExists($this);
    }
    
    public function delete()
    {
        $this->_scheduler->deleteJob($this);
    }
    
    public function getHash()
    {
        return $this->_scheduler->createHash($this);
    }
    
 
}