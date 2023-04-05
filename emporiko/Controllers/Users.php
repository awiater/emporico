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

use \EMPORIKO\Helpers\AccessLevel;
use \EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\UserInterface;

class Users extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'		=>AccessLevel::delete,
		'groups' 	=>AccessLevel::delete,
		'group'		=>AccessLevel::delete, 
		'profile'	=>AccessLevel::state,
		'mode'		=>AccessLevel::view,
                'autologoff'    =>AccessLevel::settings,
                'loginform'     =>AccessLevel::view,
                'forget'        =>AccessLevel::view,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'profile'=>'Auth/User',
		'user'=>'Auth/User',
		'usergroup'=>'Auth/UserGroup',
	];
        
        /**
         * Array with controller method remaps ($key is fake function name and $value is actual function name)
         */
        public $remaps = 
        [
            'all'=>'index',
            'edit'=>'index',
            'customers'=>['index',['customers']],
        ];
	
        function forget($key=null)
        {
            $post=$this->request->getPost();
            $get=$this->request->getGet();
            $data=['form_hidden'=>[],'_view'=>'Auth/login_forget'];
            if (array_key_exists('password', $post) && array_key_exists('key', $get))
            {
                
            }else
            if (array_key_exists('email', $post))
            {
                $key=$this->auth->generateForgetKey($post['email']);
               
                if (is_array($key) && Arr::KeysExists(['resetkey'], $key))
                {
                    $key['key_url']=url('Pages','forgetpassword',[],['key'=>$key['resetkey']]);
                    $this->sendNotification($this->model_Settings->get('users.users_forgetemailtpl'), $key, $post['email']);
                    
                }   
                return redirect()->route('login')->with('error',$this->createMessage('auth.forget','info'));
            }
            
            if (array_key_exists('key', $get))
            {
                if (!$this->auth->validateForgetKey($get['key']))
                {
                    $data['error']=$this->createMessage('auth.resetTokenExpired','danger');
                }else
                {
                    if (array_key_exists('password', $post))
                    {
                        $post=$this->auth->changePassword($get['key'],$post['password']);
                        if (is_bool($post) && $post==TRUE)
                        {
                            return redirect()->route('login')->with('refurl', site_url())->with('error',$this->createMessage('auth.passwordChangeSuccess','info'));
                        }
                        $data['error']=$this->createMessage($post,'danger');
                    }
                    $data['error_pass_len']=$this->createMessage('system.auth.recover_error_pass_len','danger');
                    $data['error_pass_equal']=$this->createMessage('system.auth.error_pass_equal','danger');
                    $data['_view']='Auth/login_recover';
                }
            }
            end_fnc:
            return view($data['_view'],$data);
        }
        
        function loginform()
        {
           $post=$this->request->getPost();
           if (auth()->isLoged())
           {
               return redirect()->to(site_url());
           }
           if (array_key_exists('login', $post))
           {
               $post['username']=$post['login'];
           }
           
           if (!array_key_exists('refurl', $post))
           {
               $post['refurl']=$this->getFlashData('refurl', site_url());
           }
           
           $auth=auth()->authenticate($post);
           
           $this->view->setFile('Auth/login_form');
           if (!$auth->isOK())
           {
                if (!is_array($post) || (is_array($post) && count($post) > 1))
                {
                    $this->view->addData('error',$this->createMessage($auth->reason(),'danger'));
                }
           }else
           {
             return redirect()->to($post['refurl']);
           }
           $this->view->addData('form_hidden',['refurl'=>$post['refurl']]);
           if ($this->model_Settings->get('users.users_forgeturl')==1)
           {
               $this->view->addData('url_forget',url('Pages','forgetpassword'));
           }
           //
           return $this->view->render('plainhtml');  //https://portal.apdcw.co.uk/menu/dash/settingsmenu/47        
        }
        
	function mode()
	{
		$refurl=$this->getRefUrl(site_url());
		
		$user=loged_user();
		if ($user['interface']==0)
		{
			$this->model_User->save(['userid'=>$user['userid'],'interface'=>1]);
			$refurl=site_url();
		}else
		{
			$this->model_User->save(['userid'=>$user['userid'],'interface'=>0]);
		}
		return redirect()->to($refurl);
	}
	
	function groups()
	{
		$this->setTableView('Users/index_groups')
                         ->setCustomViewEnable(FALSE)
			 ->setData('usergroup',['ugsettings','ugcreate','ugedit','ugmodify'],TRUE)
			 ->setPageTitle('system.auth.groups_page')
			 ->addFilterField('ugname %')
			 ->addFilterField('ugdesc %')
			 ->addColumn('system.auth.groups_ugname','ugname',TRUE)
			 ->addColumn('system.auth.groups_ugdesc','ugdesc')
			 ->addColumn('system.general.enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])//$model,$filters,$orderBy=null,$pagination=FALSE
			 ->addEditButton('system.auth.groups_editbtn','group',null,'btn-primary','fa fa-edit')
			
                         ->addBreadcrumbSubSettings()
                         ->addBreadcrumb('system.auth.group_menu',current_url())
                        
                         ->addNewButton('group/new') 
                         ->addDeleteButton()
                         ->addEnableButton()
			 ->addDisableButton()
                         ->addModuleSettingsButton(null,null,['margin'=>'ml-3'])
                        
                         ->addData('superadmin',$this->model_UserGroup->getSuperAdminsGroup(TRUE));
                         
		return $this->view->render();
	}
        
        function group($record)
        {
            $refurl=$this->getRefUrl(null);
            $isnew=FALSE;
            
            if (is_numeric($record))
            {
                $record=$this->model_UserGroup->getDataByID($record);
            }else
            {
		$record=null;
            }
            
            $record=$this->getFlashData('_postdata',$record);
            if ($record==null || $record=='new')
            {
                if (!$this->hasAccess(AccessLevel::create))
                {
                    return $this->getAccessError();
                }
                $isnew=TRUE;
		$record=$this->model_UserGroup->getNewRecordData(TRUE);
                $record['ugref']='@new';
            }
            $record['access_groups']=$this->model_UserGroup->getAccess($record['ugref']);
            $this->setFormView('Users/edit_group')
                //->setCustomViewEnable(FALSE)    
                ->setFormTitle('system.auth.group_edit',[$record['ugname']])
		->setPageTitle('system.auth.group_edit')
		->setFormAction($this,'save',['group'],['refurl'=>base64url_encode($refurl)])
		->setFormArgs(['autocomplete'=>'off'],[
                                $this->model_UserGroup->primaryKey=>$record[$this->model_UserGroup->primaryKey],
                                'ugref'=>$record['ugref']
                              ])
		->setFormCancelUrl($refurl)
                ->setCustomViewEnable(FALSE)
					
		->addBreadcrumb('system.auth.profile_indexbread',url($this))
		->addBreadcrumb('system.auth.groups_indexbread',url($this,'groups'))
		->addBreadcrumb($record['ugname'],'/')
			
		->setTab('general','system.general.tab_info')
                ->addFieldsFromModel($this->model_UserGroup->getFieldsForForm($record,$record['ugref']==$this->model_UserGroup->getSuperAdminsGroup() ? 'basic':null),$record,'system.auth.groups_-key-',FALSE)
                
                ->addData('record',$record)
                ->addData('access_levels', AccessLevel::Levels);
            if ($record['ugref']!=$this->model_UserGroup->getSuperAdminsGroup())
            {
                $this->view->setTab('access','system.general.tab_access');
            }
            return $this->view->render();
        }
        
	
	function index($record=null) 
	{
            $filters=[];
            $filters_groups=$this->model_Usergroup->getGroupsWithLevel(AccessLevel::create);
            if (count($filters_groups) > 0)
            {
                $filters['accessgroups In']=$filters_groups;
            }else
            {
                $filters['access']='@loged_user';
            }
            $filter_index='all';
            if ($record!=null)
            {
                if ($record=='customers')
                {
                    $filters['iscustomer']=1;
                    if (array_key_exists('access', $filters))
                    {
                        unset($filters['access']);
                    }
                    if (array_key_exists('accessgroups In', $filters))
                    {
                        unset($filters['accessgroups In']);
                    }
                    $filter_index='customers';
                }else
                if ($record=='employees')
                {
                    $filters['iscustomer']=0;
                    $filter_index='employees';
                }else
                {
                    return $this->profile($record);
                }
                
            }
            if ($this->hasAccess(AccessLevel::settings))
            {
                if (array_key_exists('iscustomer', $filters))
                {
                    $filters['iscustomer']=1;
                } else 
                {
                    $filters=[];
                } 
            }
            
            $this->setTableView('Users/index_profiles')
                        ->setCustomViewEnable(FALSE)  
			 ->setData('profile',null,TRUE,null,$filters)//'access'=>'@loged_user'
			 ->setPageTitle('system.auth.profiles.page')
			 ->addFilters($filter_index)
			 ->addFilterField('name %')
			 ->addFilterField('|| username %')
			 ->addFilterField('|| email')
                    
			 ->addColumn('system.auth.profile_name','name',TRUE)
			 ->addColumn('system.auth.profile_username','username')
			 ->addColumn('system.auth.profile_email','email')
                         ->addColumn('system.auth.profile_autologoff','autologoff',FALSE,'yesno')
			 ->addColumn('system.general.enabled','enabled',FALSE,'yesno')//$model,$filters,$orderBy=null,$pagination=FALSE
			 ->addEditButton('system.auth.profiles.editbtn','edit/-id-',null,'btn-primary','fa fa-edit')
                         
                         ->addBreadcrumbSubSettings()
                         ->addBreadcrumb('system.auth.profiles.page',current_url())
            
                         ->addNewButton('edit/new')
                         ->addDeleteButton()
                         ->addEnableButton()
			 ->addDisableButton()
                         ->addHeaderButton('autologoff/0',null,'button','btn btn-outline-danger btn-sm ml-3 tableview_def_btns','<i class="fas fa-door-open"></i>','system.auth.btn_profiles_autologoff_no',AccessLevel::settings,[]) 
                         ->addHeaderButton('autologoff/1',null,'button','btn btn-outline-dark btn-sm mr-2 tableview_def_btns','<i class="fas fa-door-closed"></i>','system.auth.btn_profiles_autologoff_yes',AccessLevel::settings,[]) 
                         ->addModuleSettingsButton()			 			 
                         
                         ->addData('admins',[]);//$this->model_Profile->getSuperAdminUsers('userid'));
            if (!array_key_exists('iscustomer', $filters))
            {
                $this->view->addFilterField('iscustomer',1,'system.auth.profiles.filt_iscustomer')
                           ->addFilterField('iscustomer','@0','system.auth.profiles.filt_iscustomer_0');
            }
            return $this->view->render();
	}
	
        function autologoff($mode)
        {
            $post=$this->request->getPost();
            $refurl=$this->getRefUrl();
            if (!array_key_exists('userid', $post))
            {
                return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.no_selected_items','warning'));
            }
            $post['userid']= is_array($post['userid']) ? $post['userid'] : [$post['userid']];
            if ($this->model_Profile->setAutoLogOff($post['userid'],intval($mode)==1))
            {
                return redirect()->to($refurl)->with('error',$this->createMessage('system.auth.profile_autologoff_ok','success'));
            }
            return redirect()->to($refurl)->with('error',$this->createMessage('system.auth.profile_autologoff_error','danger'));
        }
        
	function profile($user=null) 
	{
            
		if ($user==null)
		{
                    $user= loged_user('userid');
		}
                
		if ($user=='new')
		{
                    if (!$this->hasAccess(AccessLevel::create))
                    {
                        return $this->getAccessError();
                    }
			$user=array_combine($this->model_Profile->allowedFields, array_fill(0, count($this->model_Auth_User->allowedFields), ''));
			$user[$this->model_Profile->primaryKey]='';
			$user['menuaccess']=$this->model_Profile->getLogedUserMenuAccess();
			$user['menuaccess']=is_array($user['menuaccess']) ? json_encode($user['menuaccess']) : null;
			$user['dashboardaccess']=$this->model_Profile->getLogedUserDashAccess();
			$user['dashboardaccess']=is_array($user['dashboardaccess']) ? json_encode($user['dashboardaccess']) : null;
                        $user['avatar_name']=$this->model_Profile->getNextID();
                }else
		{
			$user=$this->model_Profile->getUserData(['userid'=>$user],TRUE);
		}
		$user=$this->getFlashData('_postdata',$user);
		if (!is_array($user) || (is_array($user) && count($user)<1))
		{
			return redirect()->to(url($this))->with('error',$this->createMessage(lang('system.errors.invalid_id',[lang('system.auth.profile_user')]),'danger'));
		}
		if (!array_key_exists('avatar_name', $user))
                {
                    $user['avatar_name']=$user[$this->model_Profile->primaryKey];
                }
                if (!array_key_exists('avatar', $user))
                {
                    $user['avatar']='';
                }
                $menuaccess_list=$this->model_Menu_MenuItems->getForProfileForm();
                
                $user['edit_acc']=$this->hasAccess(AccessLevel::edit);
		$menuaccess=$this->auth->hasAccess($this->model_Settings->get('users.modifymenuaccess'));
		$this->setFormView('Users/account')//'Users/profile')
					->setFormTitle('system.auth.profile_edit',[$user['name']])
					->setPageTitle('system.auth.profile_page')
					->setFormAction($this,'save',['profile'],['refurl'=> base64url_encode($this->getRefUrl())])
					->setFormCancelUrl($this->getRefUrl())
                                        ->setCustomViewEnable(FALSE)
					->setFormArgs(['autocomplete'=>'off','id'=>'profile_edit'],
                                                [
                                                    'userid'=>$user['userid']
                                                ])
                                        ->addData('curtab',$this->request->getGet('tab'))
                                        ->addData('record',$user)
					->addData('menuaccess_list',$menuaccess ? $this->model_Menu_MenuItems->getForProfileForm() : [])
				
                                        ->setTab('general','system.general.tab_info')
                                        
					->addFieldsFromModel('user',$user,'system.auth.profile_-key-');
            if ($user['edit_acc'])
            {
                $this->view->setTab('access','system.auth.tab_access')
                          ->setTab('other','system.auth.tab_other')
                          ->addBreadcrumb('system.auth.profile_indexbread',url($this))
                          ->addBreadcrumb($user['username'],'/')
                          ->addData('access_levels',$this->model_UserGroup->getAccessLevels())
                          ->addData('access_levels_values',$this->model_System_Module->getAccessForUser($user['userid']));
            }else
            {
                $this->view->addBreadcrumb('system.auth.profile_lowlevel',url($this));
            }
            return $this->view->render();
	}

	public function delete(array $post=[])
	{
		$refurl=$this->getRefUrl();
		
		$post=count($post) > 0 ? $post : $this->request->getPost();
		
		if (array_key_exists('model', $post) && $post['model']=='usergroup' && array_key_exists('ugid', $post) && is_array($post['ugid']))
		{
			$group=$this->model_usergroup->getSuperAdminsGroup(TRUE);
			if (!is_array($group))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no','danger'));
			}
			$group=array_search($group['ugid'],$post['ugid'],TRUE);
			if (!is_bool($group) && is_numeric($group))
			{
				unset($post['ugid'][$group]);
				if (count($post['ugid'])<1)
				{
					return redirect()->to($refurl)->with('error',$this->createMessage('system.auth.groups_sadmin_error','warning'));
				}
			}
		}
		return parent::delete($post);
	}
        
        function settings($tab,$record)
        {
            $settings=$this->model_Settings->get('users.*',FALSE,'*');
            $view=new Pages\FormView($this);
            if ($tab=='cfg')
            {
                $tpls=$this->model_Documents_Report->getTemplatesForForm();
                $args=['advanced'=>TRUE,'url'=>url('Reports','templates',['-id-'],['refurl'=> base64url_encode(current_url(FALSE,FALSE).'&tab=cfg')])];
                $view->addYesNoField('auth.settings_forgeturl', $settings['users_forgeturl']['value'],'settings[users_forgeturl]');
                $view->addDropDownEditableField('auth.settings_forgetemailtpl','settings[users_forgetemailtpl]',$tpls,$settings['users_forgetemailtpl']['value'],$args);
            }
            
            return view('System/form_fields',$view->getViewData());
        } 
        
	function save($type,$post=null)
	{
		$post=$this->request->getPost();
		$refurl=$this->getRefUrl();
		$refurl_ok=$refurl;
		$model=$this->model_Profile;
                
		if ($type=='profile')
		{
                    if (array_key_exists('userid', $post) && is_numeric($post['userid']))
                    {
                        $admins=$this->model_Profile->getSuperAdminUsers('userid');
                        $admingrp=$this->model_UserGroup->getSuperAdminsGroup();
                        if (in_array($post['userid'], $admins) && count($admins) < 2 && !in_array($admingrp, is_array($post['accessgroups']) ? $post['accessgroups'] : explode(',',$post['accessgroups'])))
                        {
                            //return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.nomoresadmins_error','danger'));
                        }
                    }         
                    if (array_key_exists('menuaccess', $post) && is_array($post['menuaccess']))
			{
                            $post['menuaccess']=implode(',', $post['menuaccess']);
			}else
                        {
                            $post['menuaccess']='';
                        }
			if (array_key_exists('pass', $post) && strlen($post['pass'])>0 && array_key_exists('password', $post) && $post['password']!=$post['pass'])
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.user_pass_no','danger'))->with('_postdata',$post);
			}
			
			if (array_key_exists('password', $post) && strlen($post['password'])<1)
			{
				unset($post['password']);
			}

			if (array_key_exists('pass', $post) && strlen($post['pass'])<1)
			{
				unset($post['pass']);
			}

			if (array_key_exists('accessgroups', $post) && is_array($post['accessgroups']))
			{
				$post['accessgroups']=implode(',', $post['accessgroups']);
			}
                        
			if (array_key_exists('dashboardaccess', $post) && is_array($post['dashboardaccess']))
			{
				$post['dashboardaccess']=json_encode($post['dashboardaccess']);
			}
			
                        if (Arr::KeysExists(['username','name','userid'], $post) && !is_numeric($post['userid']) && strlen($post['username']) < 4)
                        {
                            $post['username']=mb_url_title(strtolower($post['name']),'_');
                        }
                                
                        if (!is_numeric($post['userid']))
			{
				unset($post['userid']);
			}
                        
                        if (array_key_exists('customer', $post) && is_numeric($post['customer']) && intval($post['customer']) > 0)
			{
				$post['iscustomer']=1;
			}else
                        {
                            $post['iscustomer']=0;
                        }
                       
                        if (Arr::KeysExists(['userid','perms'], $post) && is_array($post['perms']))
                        {
                            $this->model_System_Module->setAccessForUser($post['userid'],$post['perms']);
                        }
		}else
		if($type=='group')	
		{
                    
			$refurl_ok=url($this,'groups');
                        if (array_key_exists('perm', $post))
                        {
                            /*$this->model_Auth_AuthAccess
                                 ->builder()
                                 ->set($post['perm'])
                                 ->where('acc_ref',$post['ugref'])
                                 ->update();*/
                        }
                        
			if (!is_numeric($post['ugid']) && array_key_exists('ugname', $post))
			{
				$post['ugref']='#'.mb_url_title($post['ugname'],'_',TRUE);
			}
                        
                        $model=$this->model_UserGroup;
                        
			
		}else
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_save_no','danger'))->with('_postdata',$post);
		}
                
		$this->flatenArrayValues($post);
                
		$this->uploadFiles($post);
		if ($model->save($post))
		{
                    $this->_after_save($type, $post, $refurl, $refurl_ok);
                    return redirect()->to($refurl_ok)->with('error',$this->createMessage('system.general.msg_save_ok','success'));
		}else
		{
                    return redirect()->to($refurl)->with('error',$this->createMessage($model->errors(),'danger'))->with('_postdata',$post);
		}
	}
        
        function _after_save($type, $post, $refurl, $refurl_ok) 
        {
            if ($type=='model_group' || $type=='group')
            {
                if (array_key_exists('perms', $post))
                {
                    if (array_key_exists('@new', $post['perms']))
                    {
                                $post['perms'][$post['ugref']]=$post['perms']['@new'];
                                unset($post['perms']['@new']);
                    }
                    $this->model_UserGroup->setAccess($post['perms']);
                    unset($post['perms']);
                }
            }
            return TRUE;
        }
	
}