<?php
/*
 *  This file is part of EMPORIKO ERP
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */
 
namespace EMPORIKO\Models\Products;

use EMPORIKO\Helpers\Arrays as Arr;

class BrandUpdateModel extends \EMPORIKO\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='products_brands_updates';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'prbuid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['prbu_name','prbu_updt'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
            'prbuid'=>      ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
            'prbu_name'=>   ['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'foreignkey'=>['products_brands','prb_name','CASCADE','NO ACTION']],
            'prbu_updt'=>   ['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE]
	];	
	
        function filtered(array $filters = [], $orderby = null, $paginate = null, $logeduseraccess = null, $Validation = TRUE) 
        {
            return $this->getView('vw_products_brandsupdates')->filtered($filters, $orderby, $paginate, $logeduseraccess, $Validation);
        }
        
        /**
         * Add brand update date
         * 
         * @param string $brand
         * @param string $date
         * 
         * @return bool
         */
        function addUpdate(string $brand,string $date)
        {
            if (is_numeric($brand))
            {
                $brand=$this->getModel('Brand')->find($brand);
                if (!is_array($brand) || (is_array($brand) && !array_key_exists('prb_name', $brand)))
                {
                    return FALSE;
                }
                $brand=$brand['prb_name'];
            }
            $date= convertDate($date, null, 'Ymd0000');
            return $this->save(['prbu_name'=>$brand,'prbu_updt'=>$date]);
        }
        
}
/*
 * vw_products_brands_withupdates
SELECT
`pb`.*,
(SELECT lupd.prbu_updt FROM products_brands_updates as `lupd` WHERE lupd.prbu_name=`pb`.`prb_name` AND lupd.prbu_updt<=DATE_FORMAT(CURDATE(),"%Y%m%d") ORDER BY lupd.prbu_updt DESC LIMIT 1) as `lastupdt`,
(SELECT nupd.prbu_updt FROM products_brands_updates as `nupd` WHERE nupd.prbu_name=`pb`.`prb_name` AND nupd.prbu_updt > DATE_FORMAT(CURDATE(),"%Y%m%d") ORDER BY nupd.prbu_updt LIMIT 1) as `nextupdt`
FROM `products_brands` as `pb`
 *  */