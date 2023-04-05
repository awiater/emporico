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
 
namespace EMPORIKO\Models\System;

use EMPORIKO\Helpers\Strings as Str;

class MovementsModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='movements_history';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'mhid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['mhtype','mhdate','mhuser','mhfrom','mhto','mhref','mhinfo','type','enabled'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'mhid'=>	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'mhtype'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'mhdate'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'mhuser'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'foreignkey'=>['users','username','CASCADE','NO ACTION']],
		'mhfrom'=>	['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE],
		'mhto'=>	['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE],
		'mhref'=>	['type'=>'VARCHAR','constraint'=>'300','null'=>FALSE],
		'mhinfo'=>	['type'=>'TEXT','null'=>TRUE],
		'type'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
                'enabled'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
	];
	
        /**
         * Add new movement type
         * 
         * @param string $name
         * @param string $tooltip
         * 
         * @return boolean
         */
        function addNewType($name,$tooltip)
        {
            $id=$this->getModel('Settings')->count(['paramsgroups'=>'movement_types']);
            return $this->getModel('Settings')->add('movement_types','movement_type_'.$id, $tooltip, 'text', $name);
        }
        
        /**
         * Remove movement type by name
         * 
         * @param string $name
         * 
         * @return boolean
         */
        function  removeTypeByName($name)
        {
            return $this->getModel('Settings')->where('paramsgroups','movement_types')->where('tooltip',$name)->delete();
        }
       
        
        /**
         * Add new movement
         * 
         * @param string|Int $mhtype
         * @param string     $mhuser
         * @param string     $mhfrom
         * @param string     $mhto
         * @param string     $mhref
         * @param string     $mhinfo
         * @param string     $mhdate
         * @param string     $type
         * @param Int        $enabled
         * 
         * @return boolean
         */
	function addItem($mhtype,$mhuser,$mhfrom,$mhto,$mhref,$mhinfo=null,$mhdate=null,$type=null,$enabled=1)
	{
		$mhdate=$mhdate==null ? formatDate() : $mhdate;
                $mhref=$mhref==null ? '' : $mhref;
		$mhref=is_array($mhref) ? $mhref : [$mhref];
		$arr=[];
                if (is_string($mhtype))
                {
                    $arr=$this->getModel('Settings')->filtered(['paramsgroups' => 'movement_types', '( param' => 'movement_type_' . $mhtype, '|| tooltip )' => $mhtype])->first();
                    if (!is_array($arr))
                    {
                        return FALSE;
                    }
                    $mhtype=Str::afterLast($arr['param'], '_');
                }
                $arr=[];
		foreach ($mhref as $value) 
		{
			$arr[]=[
				'mhtype'=>$mhtype,
				'mhdate'=>$mhdate,
				'mhuser'=>$mhuser,
				'mhfrom'=>$mhfrom,
				'mhto'=>$mhto,
				'mhref'=>$value,
				'mhinfo'=>$mhinfo,
				'type'=>$type,
                                'enabled'=>$enabled
			];
		}
		return $this->builder()->insertBatch($arr);
	}
	
        /**
         * Get movement data filtered by given filters
         * 
         * @param array $filters
         * @param mixed $orderby
         * @param mixed $paginate
         * 
         * @return array
         */
        function getData(array $filters = [],$paginate = null, $orderby = null) 
        {
            $orderby=$orderby==null ? 'mhdate DESC' : $orderby;
            $model=$this->getView('vw_movements');
            $results=$model->filtered($filters, $orderby, $paginate, null, FALSE);
            if ($paginate!=null)
            {
                return ['data'=>$results,'pagination'=>$model->pager->links()];
            }
            return $results->find();
        }
        
        /**
         * Return array with movements filtered data by reference
         * 
         * @param string $ref
         * @param bool   $addPagination
         * @param bool   $getLinks
         * 
         * @return array
         */
	function getByRefernce($ref,$addPagination=TRUE,$getLinks=FALSE)
	{
            $view=$this->getView('vw_movements');
            $results=$view->filtered(['mhref'=>$ref],'mhdate DESC',$addPagination);
            if (!$addPagination)
            {
                $results=$results->find();
            }
            return $getLinks ? ['movements'=>$results,'pagination'=>$view->pager->links()] : ['movements'=>$results];
	}
	
        /**
         * Return array with movements filtered data by type
         * 
         * @param string|Int $type
         * 
         * @return array
         */
	function getByType($type)
        {
            return $this->getView('vw_movements')->filtered(is_numeric($type) ? ['mhtype'=>$type] : ['mhtype_name'=>$type],'mhdate DESC',config('Pager')->perPage)->find();
        }
	
	function getUserDataByType($user,$from,$to,$filter=[],$status=[])
	{
		$filter=is_array($filter) ? $filter : [];
		$status=is_array($status) ? $status : [];
		$from=strlen($from)>8 ? substr($from,0,8) : $from;
		$to=strlen($to)>8 ? substr($to,0,8) : $to;
		$user=str_replace(['"'], [''],$user);
		$user=strlen($user) < 1 ? loged_user('username') : $user;
		$sel=[];
		$sel[]=$this->table.'.mhuser as operator';
		$sql=$this->where($this->table.'.mhdate >= ','%from')
				  ->where($this->table.'.mhdate <=','%to');
		if (strlen($user) > 0 && $user!='0')
		{
			$sql=$sql->where($this->table.'.mhuser',$user)->groupBy([$this->table.'.mhuser']);
		}else
		{
			$sql=$sql->groupBy([$this->table.'.mhuser'])->groupBy([$this->table.'.mhdate']);
		}
		$ssql='SUM((SELECT count(`mh1`.`mhtype`) FROM `movements_history` as `mh1` WHERE `mh1`.`mhuser`=`mh`.`mhuser` AND `mh1`.`mhtype`=%mht% AND `mh1`.`mhdate` >=%from AND `mh1`.`mhdate` <= %to)) as `%value%`';
		$ssql='SUM((SELECT count(`mh1`.`mhtype`) FROM `movements_history` as `mh1` WHERE `mh1`.`mhuser`=`mh`.`mhuser` AND `mh1`.`mhtype`=%mht% AND `mh1`.`mhdate` =%from)) as `%value%`';
		$ssql='SUM((IF (`mh`.`mhtype`=%mht%,1,0))) as %value%';
		$status=count($status) >0 ? $status :$this->getSettingsModel()->getMovementTypes();
		if (count($filter) > 0)
		{
			$filter_tmp=[];
			foreach ($filter as $key => $value)
			{
				if (array_key_exists($value, $status))
				{
					$filter_tmp[$value]=$status[$value];
				}
			}
			if (count($filter_tmp)>0)
			{
				$filters=$filter_tmp;
			}else
			{
				$filter=$status;
			}
		}else
		{
			$filter=$status;
		}
		
		foreach ($filter as $key => $value) 
		{
			$value=str_replace(' ', '_', $value);
			$tbl='`fld'.$key.'`';
			$sel[]=str_replace(['`mh1`','%mht%','`mh`','%value%','%from'], [$tbl,$key,'`'.$this->table.'`',$value,'`movements_history`.`mhdate`'],$ssql);
		}
		
		//$sel[]='(SELECT COUNT(`ord`.`oid`) FROM `orders` as `ord` WHERE `ord`.`created` >=%from AND `ord`.`created` <=%to ) as maxcustomer';
		//$sel[]='(SELECT COUNT(`vw`.`created`) FROM `vw_kpipalletsmoreinfo` as `vw` WHERE `vw`.`created` >=%from AND `vw`.`created` <=%to ) as maxpallets';
		$sql=$sql->select(implode(',',$sel));
		$sql=$sql->getCompiledSelect();
		
		$arr=[];//['labels'=>[],'data'=>[]];
		for ($i=0; $i < formatDate([$to,$from],'diff','Ymd')->getMonths(); $i++) 
		{ 
			$start=formatDate($from,'+ '.$i.' months','Ymd');
			$end=formatDate($start,'+ 1 months','Ymd');
			$data=str_replace(['%from','%to'],[$start,$end],$sql);
			$key=convertDate($start,'Ymd','M Y');
			$data=$this->db->query($data)->getResultArray();
			//$arr['labels'][]=$key;
			
			if (is_array($data) && array_key_exists(0, $data))
			{
				if (count($data)==1)
				{
					$data=$data[0];
					$arr[$key]=$data;
					$arr[$key]['month']=$key;
				}else
				{
					foreach ($data as $kkey=>$value) 
					{
						$data[$kkey]['month']=$key;
					}
					$arr=$data;
				}
				
			}
		}
		return $arr;
	}
	
        /**
         * Returns notification data
         * 
         * @param  Int $qty
         * @return array/null
         */
        public function getNotifications(int $qty=1,$mode='icon')
        {
            $filters=['_limit'=>$qty,'enabled'=>1];
            $user=loged_user();
            if (intval($user['iscustomer'])==1)
            {
                $cust=$this->getModel('Customers/Customer')->getEmailAdressesForCustomer('');
                if (!is_array($cust))
                {
                    return null;
                }
                $filters['mhto %']=$cust;
            }else
            {
                $filters['mhto']=$user['username'];
            }
            if ($mode=='icon')
            {
                [
                    'qty'=>$this->getView('vw_notifications')->count($filters),
                    'items'=>$this->getView('vw_notifications')->filtered($filters,'mhdate DESC',$qty)
                ];
            }
            return $this->getView('vw_notifications')->filtered($filters,'mhdate DESC',$qty);
        }
        
	public function installstorage($install=FALSE)
	{
		if ($install)
		{
			parent::installstorage();
		}
	}
}