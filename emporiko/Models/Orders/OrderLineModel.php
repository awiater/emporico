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
 
namespace EMPORIKO\Models\Orders;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

class OrderLineModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Menu table name
	 * 
	 * @var string
	 */
	protected $table='orders_lines';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'olid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['ol_ref','ol_oepart','ol_partdesc','ol_partbrand','ol_qty','ol_price','ol_cusprice'
                                  ,'ol_addon','ol_addby','ol_status','ol_avalqty','ol_cusacc','ol_ourpart'
                                  ,'ol_commodity','ol_origin','enabled'];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'olid'=>           ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
		'ol_ref'=>         ['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE,'index'=>TRUE,'foreignkey'=>['orders','ord_ref','CASCADE','CASCADE']],
                'ol_oepart'=>      ['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE,'index'=>TRUE],
                'ol_ourpart'=>     ['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE,'index'=>TRUE],
                'ol_partdesc'=>    ['type'=>'VARCHAR','constraint'=>'200','null'=>TRUE],
                'ol_partbrand'=>    ['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
                'ol_qty'=>         ['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'ol_price'=>       ['type'=>'DOUBLE','null'=>FALSE],
                'ol_cusprice'=>    ['type'=>'DOUBLE','null'=>FALSE],
		'ol_commodity'=>   ['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
                'ol_status'=>      ['type'=>'TEXT','null'=>TRUE],
                'ol_avalqty'=>     ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
                'ol_cusacc'=>      ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE,'index'=>TRUE],
                'ol_origin'=>      ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE,'index'=>TRUE],
		'enabled'=>        ['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
	];
        
        function filtered(array $filters = [], $orderby = null, $paginate = null, $logeduseraccess = null, $Validation = TRUE) 
        {
            return $this->getView('vw_orders_lines')->filtered($filters, $orderby, $paginate, $logeduseraccess, $Validation);
        }
        
        /**
         * Returns array with order lines formatted for external API
         * 
         * @param string $orderRef
         * @param string $priceField
         * 
         * @return array | null
         */
        function getForApi(string $orderRef,string $priceField='ol_price')
        {
            $priceField= in_array($priceField, $this->allowedFields) ? $priceField : 'ol_price';
            $select=['ol_ourpart','ol_qty',$priceField,'ol_partdesc'];
            return $this->select(implode(',',$select))->where('ol_ref',$orderRef)->find();
            
        }
        
        /**
         * Returns array with lines for given order
         * 
         * @param string $order
         * @param bool   $validate
         * 
         * @param bool $paginate
         */
        function getForOrder(string $order,bool $validate=FALSE,bool $paginate=FALSE)
        {
            if ($validate)
            {
                $this->validateData($order);
            }
            $data=$this->filtered(['ol_ref'=>$order], 'ol_oepart', $paginate ? 10 : FALSE);
            return $paginate ? ['data'=>$data,'pagination'=>$this->pager->links()] : $data->find();
        }
        
        /**
         * Returns array with order lines details preset for product picker
         * 
         * @param string $order
         * 
         * @return null | array
         */
        function getForPicker(string $order,bool $forExternal=FALSE,bool $addRValue=TRUE)
        {
            $select=[];
            $select[]='`ol_oepart` as '.(!$forExternal ? '`prd_tecdocpart`':lang('products.prd_tecdocpart'));
            $select[]='`ol_partdesc` as '.(!$forExternal ? '`prd_description`':lang('products.prd_description'));
            $select[]= '`ol_partbrand` as '.(!$forExternal ? '`prd_brand`':lang('products.prd_brand'));
            $select[]='`ol_qty` as '.(!$forExternal ? '`qty`':lang('orders.opportunities_qty'));
            $select[]='`ol_cusprice` as '.(!$forExternal ? '`value`':lang('orders.opportunities_value'));
            if ($addRValue)
            {
                $select[]='`ol_price` as '.(!$forExternal ? '`rvalue`':lang('orders.opportunities_rvalue'));
            }
            $select[]='`ol_ourpart` as '.(!$forExternal ? '`prd_apdpartnumber`':lang('products.prd_apdpartnumber'));
            $select[]='`ol_commodity` as '.(!$forExternal ? '`prd_commodity`':lang('products.prd_commodity'));
            $select[]='`ol_origin` as '.(!$forExternal ? '`prd_origin`':lang('products.prd_origin'));
            return $this->filtered(['ol_ref'=>$order])->select(implode(',',$select))->find();
        }


        /**
         * Returns array with order lines columns for CSV file
         * 
         * @param string $order
         * @param bool   $forCustomer
         * @param bool   $isQuote
         * @param bool   $forApi
         * 
         * @return array
         */
        function getAsCSV(string $order,bool $forCustomer=FALSE,bool $isQuote=FALSE,bool $forApi=FALSE)
        {
            $select=[];
            if ($forCustomer)
            {
                $select[]='`ol_oepart` as '."'".lang('orders.ol_oepart')."'";
                $select[]='`ol_ourpart` as '."'".lang('orders.ol_ourpart')."'";
                $select[]='`ol_qty` as '."'".lang('orders.ol_qty')."'";
                $select[]='`ol_price` as '."'".lang('orders.ol_price')."'";
                if (!$isQuote)
                {
                    $select[]='`ol_status` as '."'".lang('orders.ol_status')."'";
                }
                $select[]='`ol_ref` as '."'".lang('orders.'.($isQuote ? 'ord_quoteref' : 'ord_refcus'))."'";
                
                
            }else
            {
                $select[]='ol_oepart';
                $select[]='ol_qty';
                $select[]='ol_price';
            }
            $select=implode(',',$select); 
            if ($forApi)
            {
                 $select= strtolower($select);  
            }
            return $this->select($select)->where('ol_ref',$order)->find();
        }
        
        function updateOrder(array $data,string $orderRef)
        {
            $fields=[];
            foreach($this->allowedFields as $field)
            {
                $fields[str_replace('ol_','',$field)]=$field;
            }
            $fields['id']='olid';
            $builder=$this->builder();
            $update=[];
            $insert=[];
            foreach($data as $line)
            {
                $row=[];
                foreach($line as $key=>$value)
                {
                    if (array_key_exists($key, $fields))
                    {
                        //$builder=$builder->set($fields[$key],$val);
                        $row[$fields[$key]]=$value;
                    }else
                    if (in_array($key, $fields))
                    {
                        //$builder=$builder->set($fields[$key],$val);
                        $row[$fields[$key]]=$value;
                    }
                }
                
                if (array_key_exists('id', $line))
                {
                    //$builder->where('olid',$line['id'])->update(); 
                    $update[]=$row;
                }else
                {
                    $row['ol_ref']=$orderRef;
                    $insert[]=$row;
                }
            }
            $result=TRUE;
            if (count($insert) > 0)
            {
                $result=$this->builder()->insertBatch($insert);
            }
            
            
            
            if (count($update) > 0)
            {
                $result=$this->builder()->updateBatch($update,'olid');
            }
            
            return TRUE;
        }
        
        function updateOrder1(array $data,string $orderRef)
        {
            $updt=$this->builder()->set(['ol_oepart'=>'{ol_oepart}','ol_ourpart'=>'{ol_ourpart}','ol_qty'=>'{ol_qty}'])->where('olid','{olid}')->getCompiledUpdate();
            $insArr=['ol_oepart','ol_ourpart','ol_qty','ol_price','ol_commodity','ol_origin','ol_ref'];
            $insArr=Arr::ParsePatern(array_combine($insArr, $insArr), '{value}');
            $ins=$this->builder()->set($insArr)->getCompiledInsert();
            $arr=[];
            foreach($data as $line)
            {
                if (Arr::KeysExists(['olid','ol_oepart','ol_ourpart','ol_qty','delete'],$line))
                {
                    $this->where('olid',$line['olid'])->delete();
                }else
                if(!array_key_exists('olid', $line) && Arr::KeysExists(array_keys($insArr), $line))
                {
                   if (strlen($line['ol_ref']) < 1)
                   {
                       $line['ol_ref']=$orderRef;
                   }
                   $this->db()->query(str_replace(Arr::ParsePatern(array_keys($line),'{value}'), $line, $ins));
                }else
                if (Arr::KeysExists(['olid','ol_oepart','ol_ourpart','ol_qty'],$line))
                {
                    $this->db()->query(str_replace(Arr::ParsePatern(array_keys($line),'{value}'), $line, $updt));
                }
            }
            return $arr;
        }
        
        function validateData($order)
        {
            if (is_string($order))
            {
                $order=$this->getModel('Order')->filtered(['ord_ref'=>$order])->first();
            }
            if (!is_array($order))
            {
                return FALSE;
            }
            if (!array_key_exists('ord_cus_price', $order))
            {
                return FALSE;
            }
            $priceTbl=$this->getModel('Products/PriceFilePart')->table;
            $prdTbl=$this->getModel('Products/Product')->table;
            $select=
            [
                $this->table.'.`olid`',
                $this->table.'.ol_ref',
                $this->table.'.ol_cusprice',
                $this->table.'.ol_ourpart',
                $this->table.'.enabled',
                $this->table.'.ol_qty',
                $prdTbl.'.prd_tecdocpart as `ol_oepart`',
                $prdTbl.'.prd_description as `ol_partdesc`',
                $prdTbl.'.prd_brand as `ol_partbrand`',
                $priceTbl.'.prf_price as `ol_price`',
                $prdTbl.'.prd_commodity as `ol_commodity`',
                $prdTbl.'.prd_origin as `ol_origin`',
                
            ];
            $lines=$this->select(implode(',',$select))->where('ol_ref',$order['ord_ref'])
                    ->join($priceTbl,$priceTbl.".prf_name='".$order['ord_cus_price']."' AND ".$priceTbl.'.prf_ourpart='.$this->table.'.ol_ourpart')
                    ->join($prdTbl,$prdTbl.'.prd_apdpartnumber='.$this->table.'.ol_ourpart')
                    ->find();
            $this->getModel('Order')->builder()->set('ord_source_ref','')->where('ordid', $order['ordid'])->update();
            return $this->updateBatch($lines,'olid');
        }
}
/*
 *  vw_orders_validate
SELECT 
`orl`.`ol_ref`,
`orl`.`olid`, 
`orl`.`ol_ref`, 
`prd`.`prd_tecdocpart` as `ol_oepart`,
`prd`.`prd_apdpartnumber` as `ol_ourpart`,
`orl`.`ol_qty`, 
`price`.prdpr_lvl_t1 as 'price_t1',
 price.prdpr_lvl_js2 as 'price_js2',
`price`.prdpr_lvl_js3 as 'price_js3',
`prd`.`prd_commodity` as `ol_commodity`,
`prd`.`prd_origin` as `ol_origin` 
FROM `orders_lines` as `orl` 
LEFT JOIN `products` as `prd` ON  `prd`.`prd_tecdocpart`=`orl`.`ol_oepart` OR `prd`.`prd_apdpartnumber`=`orl`.ol_oepart 
LEFT JOIN `products_prices` as `price` ON `price`.`prdpr_part`=`prd`.`prd_apdpartnumber`
WHERE orl.`enabled`=0

*/
/*
BEGIN
DECLARE term VARCHAR(80);
DECLARE markon double;
DECLARE level VARCHAR(50);
SELECT `cus`.`terms_price` FROM `orders` as `ord` LEFT JOIN `customers` as `cus` ON `ord`.`ord_cusacc`=`cus`.`code` WHERE `ord`.`ord_ref`=orderNr INTO term;
SELECT `cst`.`markon` FROM `customers_terms` as `cst` WHERE `cst`.`name`=term INTO markon;
SET @query=CONCAT('UPDATE orders_lines AS U1,(SELECT vw.ol_ref,vw.ol_commodity,vw.ol_oepart,vw.ol_ourpart,vw.ol_origin,(0) as `ol_price` FROM vw_orders_validate as vw WHERE vw.ol_ref=',"'",orderNr,"'",') AS U2 SET U1.ol_commodity = U2.ol_commodity,U1.ol_oepart = U2.ol_oepart,U1.ol_ourpart = U2.ol_ourpart,U1.ol_origin = U2.ol_origin,U1.ol_price = U2.ol_price
WHERE U2.ol_ref = U1.ol_ref');
PREPARE stmt_name FROM @query;
EXECUTE stmt_name;
SELECT COUNT(`enabled`) as 'failed' FROM `orders_lines` WHERE `enabled`=0;
END
 * 
 */
/*
BEGIN
DECLARE term VARCHAR(80);
DECLARE markon double;
DECLARE level VARCHAR(50);
SELECT `cus`.`terms_price` FROM `orders` as `ord` LEFT JOIN `customers` as `cus` ON `ord`.`ord_cusacc`=`cus`.`code` WHERE `ord`.`ord_ref`=orderNr INTO term;
SELECT `cst`.`markon` FROM `customers_terms` as `cst` WHERE `cst`.`name`=term INTO markon;
begin tran;
UPDATE `orders_lines` AS `U1`,(SELECT `vw`.`ol_ref`,`vw`.`ol_commodity` FROM `vw_orders_validate` as `vw` WHERE `vw`.`ol_ref`=orderNr) AS `U2` SET `U1`.`ol_commodity` = `U2`.`ol_commodity` WHERE `U2`.`ol_ref` = `U1`.`ol_ref`;
commit tran;
SELECT COUNT(`enabled`) as 'failed' FROM `orders_lines` WHERE `enabled`=0;
END
 * 
 * 
 vw_orders_lines
 select `orl`.*,
if(`orl`.`ol_cusprice` is not null AND `orl`.`ol_cusprice` > 0,`orl`.`ol_cusprice` <> `orl`.`ol_price`,0) AS `ol_iserror` 
from `orders_lines` AS `orl`
 * 
 * 
 */