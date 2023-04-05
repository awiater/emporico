<?php
/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
 
namespace EMPORIKO\Models\Menu;

use EMPORIKO\Helpers\Strings as Str;

class MenuItemsModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Menu table name
	 * 
	 * @var string
	 */
	protected $table='menu_items';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'mid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['mgroup','mtext','mimage','mroute','morder','mkeywords','mtarget','enabled','access'];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'mid'=>	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
		'mgroup'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'mtext'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'mimage'=>		['type'=>'TEXT','null'=>FALSE],
		'mroute'=>		['type'=>'TEXT','null'=>FALSE],
		'morder'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'mkeywords'=>	['type'=>'TEXT','null'=>FALSE],
		'mtarget'=>		['type'=>'VARCHAR','constraint'=>'10','null'=>FALSE],
		'access'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];
	
	/**
	 *  Return all records for given menu
	 *  
	 * @param  array $filter Array with filters (key is field, value is field value)
	 * @return array
	 */
	public function getItems($menu,$access)
	{
		$arr=model('Auth/UserModel')->getLogedUserMenuAccess();
                
		$filters=['mgroup'=>$menu,'enabled'=>1,'access'=>'@loged_user'];//,'access'=>$access];
                $filters=$this->filtered($filters);
		if (is_array($arr))
		{
                    $filters=$filters->WhereNotIn('mid',$arr);
		}
		return $filters->orderby('morder')->find();
	}
	
        /**
         * Returns array with menu groups names
         * 
         * @param bool $forForm
         * 
         * @return array
         */
	public function getItemGroups(bool $forForm=FALSE)
	{
            $arr=[];
            foreach ($this->select('mgroup')->groupBy('mgroup')->find() as $value) 
            {
                if ($forForm)
                {
                   $arr[$value['mgroup']]=$value['mgroup'];     
                } else 
                {
                    $arr[]=$value['mgroup'];
                }
            }
            return $arr;
	}

	public function getForProfileForm()
	{
		$data=$this->filtered(['access'=>'@loged_user'])->find();
		$arr=[];
		foreach ($data as $value) 
		{
			if (!array_key_exists($value['mgroup'], $arr))
			{
				$arr[$value['mgroup']]=[];
			}
			$arr[$value['mgroup']][$value['mid']]=$value['mtext'];
		}
		return $arr;
	}
	
	/**
	 * Get route methods for all controllers
	 * 
	 * @param  bool $encrypt Optional if TRUE values will be flatten (json) and base64encode
	 * 
	 * @return array 
	 */
	function getControllersMethods($encrypt=FALSE)
	{
		$arr=[];
		foreach (directory_map(parsePath('@app/Controllers',TRUE)) as  $value) 
		{
			if (is_string($value) && Str::endsWith($value,'.php'))
			{
				$value=Str::before($value,'.');
				$arr[$value]=loadModule($value)->getAvaliableRoutes();
				if ($encrypt && is_array($arr[$value]))
				{
					$arr[$value]=base64_encode(json_encode($arr[$value]));
				}
			}
		}
		return $arr;
	}
        
        /**
         * Determines if user (loged user by deafult) have access to given menu item
         *  
         * @param  type $record
         * @param  type $user
         * @return boolean
         */
        function userHaveAccessToItem($record,$user=null)
        {
            if (!is_string($record))
            {
                return FALSE;
            }
            $user=$user==null ? loged_user('menuaccess') : $this->getModel('Auth/User')->filtered(['userid'=>$user,['|| username'=>$user]])->first();
            if (is_array($user))
            {
                if (array_key_exists('menuaccess', $user))
                {
                    $user=$user['menuaccess'];
                }else
                {
                    $user=null;
                }
            }
            if (!is_string($user) && $user==null)
            {
                return FALSE;
            }
            $record=$this->filtered(['mid'=>$record,'|| mroute'=>$record])->first();
            if (!is_array($record))
            {
                return FALSE;
            }
            if (!array_key_exists('mid', $record))
            {
                return FALSE;
            }
            return !Str::contains($user, $record['mid']);
             
        }
        
        function setUserAccessToItem($user,$item,$access)
        {
            if (is_numeric($item))
            {
                $mid=$this->getModel('Menu/MenuItems')->find($item);
            }else
            if(Str::contains($item, '/'))
            {
               $mid=$this->getModel('Menu/MenuItems')->where('mroute',$item)->first(); 
            } else 
            {
                return FALSE;
            }
            if (!is_array($mid) || (is_array($mid) && !array_key_exists('mid', $mid)))
            {
                return FALSE;
            }
            $mid=$mid['mid'];
            if (is_numeric($item))
            {
                $user=$this->getModel('Auth/User')->find($user);
            }else
            {
                $user=$this->getModel('Auth/User')->where('username',$user)->first();
            }
            if (!is_array($user) || (is_array($user) && !array_key_exists('menuaccess', $user)))
            {
                return FALSE;
            }
            $user['menuaccess']=explode(',',$user['menuaccess']==null ? '' : $user['menuaccess']);
            foreach($user['menuaccess'] as $key=>$menuacc)
            {
                if (intval($menuacc)==intval($mid))
                {
                    unset($user['menuaccess'][$key]);
                }
            }
            if (!$access)
            {
                $user['menuaccess'][]=$mid;  
            }
            return $this->getModel('Auth/User')->builder()->set('menuaccess',implode(',',$user['menuaccess']))->where('username',$user['username'])->update();
        }
        /**
         * Returns Settings sub menu Item or Link
         * 
         * @param bool $justLink
         * 
         * @return mixed
         */
        function getSettingsSubMenuItem(bool $justLink=FALSE)
        {
            $arr=$this->filtered(['mkeywords'=>'settings_submenu_link'])->first();
            if (!is_array($arr))
            {
                return null;
            }
            return $justLink ? site_url($arr['mroute']) : $arr;
        }
}
?>