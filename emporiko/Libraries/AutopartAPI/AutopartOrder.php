<?php
/*
 *  This file is part of EMPORIKO WMS
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W				
 *  @copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Libraries\AutopartAPI;

use EMPORIKO\Helpers\Arrays as Arr;
use EMPORIKO\Helpers\Strings as Str;
use CodeIgniter\HTTP\RequestInterface;

class AutopartOrder
{
    private $_xml=[];
    
    
    static function init()
    {
        return new AutopartOrder();
    }
    
    function __construct() 
    {
        $this->_xml= simplexml_load_file(parsePath('@app/Libraries/AutopartAPI/mam_order_template.xml',TRUE));
        $this->clearOrderLines();
        $this->setOrderDate();
    }
    
    function getOrderFile($format=null)
    {
        if ($format=='array')
        {
            return $this->toArray();
        }else
        if ($format=='object')
        {
            return $this->_xml;
        }else
        {
            return $this->_xml->asXml();
        }
    }
    
    function saveToFile($path,$format=null)
    {
        $path= parsePath($path,TRUE);
        file_put_contents($path, $this->getOrderFile($format));
    }
    
    function saveAsBundle($folder,$createZip=FALSE,$format=null)
    {
        $order=$this->getBuyerOrder();
        $order= (is_string($order) && strlen($order) < 1) || is_array($order) ? $this->getSupplierOrder() : $order;
        $folder=Str::endsWith($folder, '/') ? $folder : $folder.'/';
        $xml=parsePath($folder.$order.'.xml',TRUE);
        $this->saveToFile($xml);
        $ebu=parsePath($folder.$order.'.ebu',TRUE);
        file_put_contents($ebu, 'EMAIL~~email@email.com~~Purchase Order '.$order.'~~~~~'.$order.'.xml');
        if ($createZip!=FALSE)
        {
            $path=parsePath($folder.$createZip,TRUE);
            $zip = new \ZipArchive();
            $zip->open($path, \ZipArchive::CREATE);
            $zip->addFile($xml,$order.'.xml');
            $zip->addFile($ebu,$order.'.ebu');
            $zip->close();
            unlink($xml);
            unlink($ebu);
        }
        
    }
    
    function getBuyerOrder()
    {
        return dot_array_search('body.Order.OrderReferences.BuyersOrderNumber', $this->toArray());
    }
    
    function setBuyerOrder($orderNR)
    {
        $this->_xml->body->Order->OrderReferences->BuyersOrderNumber[0]=$orderNR;
        return $this;
    }
    
    function getSupplierOrder()
    {
        return dot_array_search('body.Order.OrderReferences.SuppliersOrderReference', $this->toArray());
    }
    
    function setSupplierOrder($orderNR)
    {
        $this->_xml->body->Order->OrderReferences->SuppliersOrderReference[0]=$orderNR;
        return $this;
    }
    
    function setBuyer($buyer,$consignee=null)
    {
        $consignee=$consignee==null ? $buyer : $consignee;
        $this->_xml->body->Order->Buyer->BuyerReferences->SuppliersCodeForBuyer[0]=$buyer;
        $this->_xml->body->Order->Delivery->DeliverTo->DeliverToReferences->BuyersCodeForDelivery[0]=$consignee;
        return $this;
    }
    
    function setSpecialInstruct(string $desc)
    {
        if (strlen($desc) > 0)
        {
            $this->_xml->body->Order->SpecialInstructions[0]=$desc;
        }
        return $this;
    }
    
    function setOrderDate(string $date='now')
    {
        $date=$date=='now' ? formatDate() : $date;
        $date= convertDate($date, null, 'Y-m-d\TH:i');
        $this->_xml->body->Order->OrderDate[0]=$date;
        return $this;
    }
    
    function addOrderLine($part,$qty,$price,$desc=null)
    {
        $id=count($this->_xml->body->Order->OrderLine);
        $this->_xml->body->Order->addChild('OrderLine');
        $this->_xml->body->Order->OrderLine[$id]->LineNumber[0]=$id+1;
        $this->_xml->body->Order->OrderLine[$id]->Product->BuyersProductCode[0]=$part;
        $this->_xml->body->Order->OrderLine[$id]->Product->Description[0]=$desc;
        $this->_xml->body->Order->OrderLine[$id]->Quantity->Amount[0]=$qty;
        $this->_xml->body->Order->OrderLine[$id]->Price->UnitPrice[0]=$price;
        $this->_xml->body->Order->OrderLine[$id]->OrderLineInformation[0]=null;
        return $this;
    }
    
    function addOrderLines(array $lines)
    {
        foreach($lines as $line)
        {
            $line= array_values($line);
            $this->addOrderLine($line[0], $line[1], $line[2]);
        }
        return $this;
    }
    
    function clearOrderLines()
    {
        unset($this->_xml->body->Order->OrderLine);
        return $this;
    }
    
    function toArray()
    {
        return json_decode(json_encode($this->_xml),TRUE);
    }
    
}