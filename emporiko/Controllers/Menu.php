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

  
namespace EMPORIKO\Controllers;

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\AccessLevel;

class Menu extends BaseController
{
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
            'index'     =>AccessLevel::settings,
            'item'      =>AccessLevel::settings,
            'htmlmenu'	=>AccessLevel::view,
            'dash'      =>AccessLevel::modify,
                
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'items'=>'Menu/MenuItems',
	];
	
	function dash($menuname,$callitem=0)
        {
            $items=$this->model_Items->getItems($menuname,'');
            $callitem=$this->model_Items->find($callitem);
            if (is_array($callitem) && array_key_exists('mtext', $callitem))
            {
                $callitem=$callitem['mtext'];
            }else
            {
                $callitem='system.menu.dashb';
            }
            
            $view=new Pages\DashBoardView($this, FALSE);//$this->setDashBoardView()
            $view->addBreadcrumb($callitem, current_url());
            foreach($items as $item)
            {
                $view->addBadgeLink($item['mtext'],'system.buttons.follow',url($item['mroute']),$this->getTileColor($item['morder']),$item['mimage'],$item['mid']);
            }
            return $view->setFile('Menu/menu_dash')->render();
        }
        
        private function getTileColor($order)
        {
            if ($order < 19)
            {
                return 'primary';
            }else
            if ($order > 19 && $order < 89)
            {
                return 'success';
            }else
            if ($order>89)
            {
                return 'danger';
            } else {
                return 'urltile';
            }
        }
	function index()
	{
		$this->setTableView()
			 ->setData('items',['mgroup','morder'],FALSE)
			 ->setPageTitle('system.menu.items_page')
			 ->addFilters('index')
			 ->addFilterField('mgroup %')
			 ->addFilterField('|| mroute %')
			 ->addColumn('system.menu.items_mgroup','mgroup',TRUE)
			 ->addColumn('system.menu.items_mtext','mtext',TRUE,[],'lang')
			 ->addColumn('system.menu.items_mroute','mroute',FALSE)
			 ->addColumn('system.menu.items_morder','morder',FALSE)
			 ->addColumn('system.menu.items_access','access',FALSE,$this->model_Auth_UserGroup->getForForm('ugref'))
			 ->addColumn('system.menu.items_enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])
			 ->addEditButton('system.menu.items_editbtn','item',null,'btn-primary','fa fa-edit')
			 
                         ->addNewButton('item/new')
			 ->addDeleteButton() 
                         ->addEnableButton()
			 ->addDisableButton()
                        
                         ->addModuleSettingsButton(null,null,['margin'=>'ml-3'])
                         ->addBreadcrumbSubSettings()
                         ->addBreadcrumb('system.menu.mainmenu_menuitems',current_url());
			 
                
                foreach($this->model_Items->getItemGroups() as $value)
                {
                    $this->view->addFilterField('mgroup',$value, ucwords(str_replace('_', ' ', $value)));
                }
                
		return $this->view->render();
	}
	
	function item($record=null)
	{
		$refurl=$this->getRefUrl(url($this));
		
		if ($record==null)
		{
			return redirect()->to(url($this));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		if ($record=='new')
		{
                    if (!$this->hasAccess(AccessLevel::create))
                    {
                        return $this->getAccessError();
                    }
			$record=array_combine($this->model_Items->allowedFields, array_fill(0, count($this->model_Items->allowedFields), ''));
			$record[$this->model_Items->primaryKey]='';
		}else
		{
			$record=$this->model_Items->find($record);
		}
		
		$record=$this->getFlashData('_postdata',$record);
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this))->with('error',$this->createMessage('system.pallets.stack_id_error','danger'));
		}
		//$routes=$this->model_Items->getControllersMethods(TRUE);
		$routes=$this->model_Modules->getModuleMenuItemsData($record['mroute']);
                $routes['SubMenu']='submenu';
		return $this->setFormView()//'Menu/item_edit')
					->setCustomViewEnable(FALSE)
					->setFormTitle('{0}',[$record['mroute']])
					->setPageTitle('system.menu.item_page')
					->setFormAction($this,'save',['items'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($this)
					->setFormArgs([],['mid'=>$record['mid'],'refurl_ok'=>$refurl])
					
					//->addInputField('system.menu.items_mgroup','mgroup',$record['mgroup'],['required'=>'true','maxlength'=>50])
					//->addInputListField('system.menu.items_mgroup','mgroup',$record['mgroup'],$this->model_Items->getItemGroups(),['required'=>'true','maxlength'=>50,'validation'=>FALSE])
                                        ->addDropDownField('system.menu.items_mgroup','mgroup',$this->model_Items->getItemGroups(TRUE),$record['mgroup'],['required'=>'true','advanced'=>TRUE])
					->addInputField('system.menu.items_mtext','mtext',$record['mtext'],['required'=>'true','maxlength'=>150])
					->addInputField('system.menu.items_mimage','mimage',$record['mimage'],['required'=>'true'])
					->addInputField('system.menu.items_mroute','mroute',$record['mroute'],['required'=>'true'])
					->addNumberField('system.menu.items_morder',$record['morder'],'morder',1000,0,['required'=>'true'])
					->addDropDownField('system.menu.items_mtarget','mtarget',['_self'=>'_self','_blank'=>'_blank'],null,['required'=>'true'])
					->addAcccessField('system.settings.customfield_access',$record['access'],'access',[],['required'=>'true'])
					->addYesNoField('system.settings.customfield_enabled',$record['enabled'],'enabled',['required'=>'true'])
					->addTextAreaField('system.menu.items_mkeywords','mkeywords',$record['mkeywords'],[])
					//New Item Form
					//->addDropDownField('system.menu.items_controller','wizard_route[controller]',array_flip($routes),[])
					//->addDropDownField('system.menu.items_action','routeact',[],[])
					//->addInputListField('system.menu.items_mgroup','menuname',null,$this->model_Items->getItemGroups(),['maxlength'=>50,'validation'=>FALSE])
					->addData('routes',$routes)
                                        ->addBreadcrumbSubSettings()
                                        ->addBreadcrumb('system.menu.mainmenu_menuitems',url('Menu'))
                                        ->addBreadcrumb(is_numeric($record['mid']) ? $record['mid'] : 'system.buttons.new',current_url())
                                        ->addSelect2('.select2')
					->render();
	}
	
	function isurlinmenu($menuname,$url=null)
	{
		$url=$url==null ? current_url() : $url;
		$url=uri_string($url);
		if (Str::contains($url,'?'))
		{
                    $url=Str::before($url,'?');
		}
                $url=explode('/',$url);
                if (count($url) > 2)
                {
                    $url= array_splice($url, 0,2); 
                }
                $url=implode('/',$url);
                $url='/'.$url;
                
                if ($menuname=='settingsmenu1'){
                     $url=$this->model_Items->count(['mgroup'=>$menuname,'mroute'=>$url]);
                    dump($url);exit;
                    
                }
                $url=$this->model_Items->count(['mgroup'=>$menuname,'mroute'=>$url]);
                //$url=$this->model_Items->count(['mgroup'=>$menuname,'( mroute'=>'/'.$url,'|| mkeywords'=>$menuname,'|| mkeywords In )'=>explode('/', $url)]);
		return $url>0;
	}
	
	function htmlmenu($menu,$class=null,$onlyLinks=FALSE)
	{
                $access= loged_user('accessgroups');
		$items=$this->model_Items->getItems($menu,$access);
		if ($onlyLinks==-1)
		{
			return $items;
		}
		$class=$class==null ? '' : $class;
		$ulclass='nav';
		$liclass='nav-item';
		$urlClass='nav-link';
		$imClass=null;
		$theme=$this->model_Settings->getTheme();
		if (is_array($class))
		{
			if (array_key_exists('ul', $class))
			{
				if ($class['ul']==FALSE)
				{
					$ulclass=FALSE;
				}else
				{
					$ulclass.=' '.$class['ul'];
				}
			}
			
			if (array_key_exists('li', $class))
			{
				$liclass=$class['li'];
			}
			
			if (array_key_exists('url', $class))
			{
				$urlClass=$class['url'];
			}
			
			if (array_key_exists('image', $class))
			{
				$imClass=$class['image'];
			}
		}else
		{
			$ulclass.=' '.$class;
		}
		
		if ($onlyLinks)
		{
			$urlClass=$ulclass=null ? $urlClass : $ulclass;
		}
		$html='';
		foreach ($items as  $value) 
		{
                    if (Arr::KeysExists(['access','mkeywords'], $value) && $value['mkeywords']=='@@' && strcmp($access,$value['access'])!==0)
                    {
                        goto end_loop;
                    }
			if (Str::isJson($value['mroute']))
			{
				$value['mroute']=json_decode($value['mroute'],TRUE);
			}
			
			if (is_array($value['mroute']))
			{
				$value['mroute']=url(
				$value['mroute']['controller']
					,array_key_exists('action', $value['mroute']) ? $value['mroute']['action'] : null
					,array_key_exists('params', $value['mroute']) ? $value['mroute']['params'] : []
					,array_key_exists('args', $value['mroute']) ? $value['mroute']['args'] : []
				);
			}else
			if(!Str::contains($value['mroute'],'/') && !Str::startsWith($value['mroute'],'['))
			{
				$value['mroute']=site_url();
			}
				
			$value['mtext']=lang($value['mtext']);
			$value['rmtext']=$value['mtext'];
				
			if ($value['mimage']!=null && strlen($value['mimage'])>2)
			{
				$imClass=$value['mimage'];
			}
			
                        if (is_array($class) && array_key_exists('image_only', $class) && $class['image_only'])
                        {
                            if (is_array($class) && array_key_exists('image_size', $class))
                            {
                                $imClass.=' '.$class['image_size'];
                            }
                            $value['mtext']='<i class="'.$imClass.'" data-toggle="tooltip" data-placement="bottom" title="'.$value['mtext'].'"></i>';
                        }else
			if ($imClass!=null && strlen($imClass)>2)
			{
				$value['mtext']='<i class="'.$imClass.'"></i><p>'.$value['mtext'].'</p>';
			}
			$otherMenu=null;
			if (Str::startsWith($value['mroute'],'[') && is_array($theme) && array_key_exists('submenuroutetpl', $theme) && strlen($theme['submenuroutetpl']) > 0)
			{
				$value['mroute']=str_replace(['[',']'], '', $value['mroute']);
				$value['mroute']=str_replace(['-menuame-','-menutext-','-menuimage-'],[$value['mroute'],$value['rmtext'],$value['mimage']],$theme['submenuroutetpl']);
				$otherMenu=json_decode($value['mroute'],true);
				$value['mroute']=url_tag('#',lang($value['mtext']),['class'=>'nav-link','data-noloader'=>'true']);
                                
                                if (!is_array($otherMenu[1]))
                                {
                                    $otherMenu[1]=['class'=>$otherMenu[1]];
                                }
                                if (!array_key_exists('ul', $otherMenu[1]))
                                {
                                    $otherMenu[1]['ul']='';
                                }
                                $otherMenu[1]['ul'].=' submenu';
                                
				$value['mroute']=$this->htmlmenu($otherMenu[0],$otherMenu[1],FALSE);
                                
				if ($this->isurlinmenu($otherMenu[0],null))
				{
                                    $liclass='nav-item menu-is-opening menu-open';
				} else 
                                {
                                    $liclass='nav-item';
                                }				
			}else
			{
                            
                            $value['mroute']=url_tag($value['mroute'],lang($value['mtext']),['target'=>$value['mtarget'],'class'=>$urlClass]);
                            /*$value['mroute']=Pages\HtmlItems\ToolbarButton::create()
                                    ->setName('button_'.$value['mid'])
                                    ->setID('button_'.$value['mid'])
                                    ->addArg('text',$value['rmtext'])
                                    ->addArg('class','btn btn-link p-0 nav-link')
                                    ->render();/**/
			}
			
			if (!$onlyLinks)
			{
				$html.='<li class="'.$liclass.'">'.$value['mroute'].'</li>';
			}else
			{
				$html.=$value['mroute'];
			}
                    end_loop:
		}
		if (!$onlyLinks)
		{
			$dropdown='';
			if (is_array($class) && array_key_exists('dropdown', $class))
			{
				$dropdown=is_array($class['dropdown']) ? $class['dropdown'] : ['text'=>$class['dropdown']];
				if (!array_key_exists('icon', $dropdown))
				{
					$dropdown['icon']='far fa-circle nav-icon';
				}
				if (!array_key_exists('icon_arrow', $dropdown))
				{
					$dropdown['icon_arrow']='fas fa-angle-left right';
				}
				$dropdown['icon_arrow']=$dropdown['icon_arrow']!=null ? '<i class="'.$dropdown['icon_arrow'].'"></i>' : null;
				$dropdown['icon']=$dropdown['icon']!=null ? '<i class="'.$dropdown['icon'].'"></i>' : null;
				$dropdown['class']=array_key_exists('class', $dropdown) ? $dropdown['class'] : 'nav-link';
				$dropdown['text']=lang($dropdown['text']);
				$dropdown['text']=$dropdown['icon'].'<p>'.$dropdown['text'].$dropdown['icon_arrow'].'</p>';
                                $dropdown['data-noloader']='true';
				$dropdown=url_tag('#',$dropdown['text'],$dropdown);
			}
			if (strlen($html)<1)
			{
				return null;
			}
			$ulclass=$ulclass!=FALSE ? ('<ul class="'.$ulclass.'">'.$html.'</ul>') : $html;
			$html=$dropdown.$ulclass;
		}
		return $html;
	}
}