<?php

/*
 *  This file is part of Emporico CRM
 * 
 * 
 *  @version: 1.1					
 * 	@author Artur W				
 * 	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Models\Auth;

use EMPORIKO\Helpers\Strings as Str;

class UserModel extends \EMPORIKO\Models\BaseModel {

    /**
     * Users table name
     * 
     * @var string
     */
    protected $table = 'users_old';

    /**
     * Table primary key
     * 
     * @var string
     */
    protected $primaryKey = 'userid';

    /**
     * Table fields
     * 
     * @var array
     */
    protected $allowedFields = ['name', 'username', 'password', 'accessgroups', 'menuaccess', 'dashboardaccess',
        'autologoff', 'enabled', 'email', 'avatar', 'startmodule', 'resetkey', 'lastlogin'
        ,'iscustomer','customer','language','apitoken'];
    
    protected $validationRules = [
        'username' => 'required|is_unique[users_old.username,userid,{userid}]',
        //'email' => 'required|is_unique[users.email,userid,{userid}]',
    ];
    
    protected $validationMessages = [
        'username' => [
            'is_unique' => 'system.auth.profile_error_unique'
        ],
    ];

    /**
     * Fields types declarations for forge
     * @var array
     */
    protected $fieldsTypes = 
    [
        'userid' =>             ['type' => 'INT', 'constraint' => '36', 'auto_increment' => TRUE, 'null' => FALSE],
        'name' =>               ['type' => 'VARCHAR', 'constraint' => '150', 'null' => FALSE],
        'username' =>           ['type' => 'VARCHAR', 'constraint' => '50', 'null' => FALSE],
        'password' =>           ['type' => 'TEXT', 'null' => FALSE],
        'accessgroups' =>       ['type' => 'TEXT', 'null' => FALSE],
        'menuaccess' =>         ['type' => 'TEXT', 'null' => FALSE],
        'dashboardaccess' =>    ['type' => 'TEXT', 'null' => FALSE],
        'enabled' =>            ['type' => 'INT', 'constraint' => '11', 'null' => FALSE],
        'email' =>              ['type' => 'TEXT', 'null' => FALSE],
        'avatar' =>             ['type' => 'TEXT', 'null' => FALSE],
        'resetkey' =>           ['type' => 'TEXT', 'null' => FALSE],
        'startmodule' =>        ['type' => 'TEXT', 'null' => FALSE],
        'iscustomer'=>          ['type' => 'INT', 'constraint' => '11', 'null' => FALSE,'default'=>1],
        'customer'=>            ['type' => 'INT', 'constraint' => '11', 'null' => FALSE,'default'=>0],
        'language'=>            ['type' => 'VARCHAR', 'constraint' => '10', 'null' => FALSE,'default'=>'en'],
        'lastlogin' =>          ['type' => 'VARCHAR', 'constraint' => '19', 'null' => FALSE],
        'autologoff' =>         ['type' => 'INT', 'constraint' => '11', 'null' => FALSE, 'default' => 1],
    ];

    /**
     *  Return all records from table
     *  
     * @param  array   $filters  		Array with filters (key is field, value is field value)
     * @param  string  $orderby  		Order by field name
     * @param  string  $paginate 		Pagination settings
     * @param  integer $logeduseraccess Loged user access level
     * @return array
     */
    public function filtered(array $filters = [], $orderby = null, $paginate = null, $logeduseraccess = null, $Validation = TRUE) 
    {
        $result = $this->parseFilters($filters, $this, [], $Validation);
        if ($orderby != null) 
        {
            if (is_string($orderby) && Str::startsWith($orderby, 'groupby:')) 
            {
                $orderby = substr($orderby, strlen('groupby:'));
                $result = $result->groupBy($orderby);
            } else 
            {
                $orderby = is_array($orderby) ? $orderby : [$orderby];
                foreach ($orderby as $orderbyValue) 
                {
                    $result = $result->orderBy($orderbyValue);
                }
            }
        }
        if ($paginate != null && $paginate != FALSE) 
        {
            if ($paginate == 0) 
            {
                return $result->find();
            }
            $result = $result->paginate($paginate);
        }

        if (array_key_exists('access', $filters)) 
        {
            $result = is_array($result) ? $result : $result->find();
            $accessgroups = loged_user('accessgroups');
            $accessgroups = is_array($accessgroups) ? null : $accessgroups;
            $arr = [];
            if ($filters['access'] == '@loged_user') 
            {
                $filters['access'] = explode(',', $accessgroups);
            } else 
            {
                $filters['accessgroups'] = [$filters['access']];
            }


            foreach ($result as $key => $value) 
            {
                $insert = TRUE;
                foreach (explode(',', $value['accessgroups']) as $acc) 
                {
                    if (!in_array($acc, $filters['access'])) 
                    {
                        goto endloopitem;
                    }
                }
                $arr[] = $value;
                endloopitem:
            }

            return $arr;
        }

        return $result;
    }
    
    /**
     * Returns array with users which belongs to given access groups
     * 
     * @param array $groups
     * 
     * @return array
     */
    function getUsersByAccessGroups(array $groups)
    {
        foreach($groups as $group)
        {
           $this->orLike('accessgroups',$group); 
        }
        return $this->find();
    }
    
    /**
     * Set user auto logoff option
     *  
     * @param mixed $userid
     * @param bool  $enable
     * 
     * @return boolean
     */
    function setAutoLogOff($userid,$enable=TRUE)
    {
        if (!is_string($userid) && !is_array($userid) && !is_numeric($userid))
        {
            return FALSE;
        }
        $userid= is_array($userid) ? $userid : [$userid];
        return $this->builder()->set('autologoff',$enable ? 1 :0)->whereIn('userid',$userid)->update();
    }
    
    function getFieldsForForm(array $record) 
    {
        $arr=parent::getFieldsForForm($record);
        if (is_numeric($record['userid']))
        {
            $arr['username']->setReadOnly();
        }
        
        $arr['email']=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('EmailField', $arr['email']);
        
        $arr['enabled']=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('YesNoField', $arr['enabled']);
        
        $arr['autologoff']=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('YesNoField', $arr['autologoff'])
                ->setTab('access');
        
        $arr['language']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::createField($arr['language'])
                    ->setTab('other')
                    ->setOptions($this->getModel('Settings')->getAvalLocales());
        
        $arr['startmodule']->setValue($this->getModel('Settings')->get('system.def_startupmodule'))
                ->addArg('type','hidden',TRUE);
        
        $arr['dashboardaccess']=\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('DropDownField', $arr['dashboardaccess'])
                ->setOptions($this->getModel('Settings/Dashboard')->getForForm('name','name'))
                ->setAsAdvanced()
                ->setTab('other');
        
        $arr['customer']= \EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem::createField('DropDownField', $arr['customer'])
                ->setOptions($this->getModel('Customers/Customer')->getCustomersForDropDown(null,lang('system.auth.profile_customer_def'),TRUE))
                ->setAsAdvanced()
                ->setTab('other');
        
        $arr['accessgroups']= \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::createField($arr['accessgroups'])
                ->setOptions($this->getModel('UserGroup')->getForProfile())
                ->setTab('access')
                ->setText('system.auth.profile_ugname')
                ->setName('accessgroups[]',TRUE);
        
        
        
        $arr['pass']= \EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                    ->setName('pass')
                    ->setID('pass')
                    ->setTab('access')
                    ->setValue('')
                    ->setAsPassword()
                    ->setText('system.auth.profile_password');
        
        unset($arr['password']);
        $arr['password']=\EMPORIKO\Controllers\Pages\HtmlItems\InputField::create()
                        ->setTab('access')
                        ->setName('password')
                        ->setID('password')
                        ->setValue('')
                        ->setAsPassword()
                        ->setText('system.auth.profile_password_confirm');
        
        $arr['accessgroups_module']= \EMPORIKO\Controllers\Pages\HtmlItems\ElementsListBoxField::create()
                ->setTab('access')
                ->setText('system.auth.profile_ugname')
                ->setName('accessgroups_module',TRUE)
                ->setID('accessgroups_module')
                ->setNewItemFunction('add_custom_module_access()')
                ->setInputField(
                        \EMPORIKO\Controllers\Pages\HtmlItems\DropDownField::create()
                        ->setName('accessgroups_modules_list')
                        ->setID('accessgroups_modules_list')
                        ->setOptions($this->getModel('System/Module')->getForAccessField())
                        ->setAsAdvanced()
                )
                ->setValue('');
        
        foreach(['menuaccess','resetkey','iscustomer','lastlogin','avatar'] as $field)
        {
            unset($arr[$field]);
        }
        if (array_key_exists('edit_acc', $record) && !$record['edit_acc'])
        {
            foreach(array_keys($arr) as $key)
            {
                if (!in_array($key, ['pass','password','email','username']))
                {
                    unset($arr[$key]);
                }else
                {
                    $arr[$key]->setTab('general');
                    if (in_array($key, ['email','username']))
                    {
                        $arr[$key]->setReadOnly();
                    }
                }
            }
        }
        return $arr;
    }
    
    /**
     * Get array with menu items which could be accessed by loged user
     * 
     * @return array
     */
    public function getLogedUserMenuAccess() 
    {
        $user=loged_user('username');
        if (is_array($user))
        {
            return null;
        }
        $arr = $this->where('username', $user)->first();
        
        if (is_array($arr) && array_key_exists('menuaccess', $arr) && strlen($arr['menuaccess']) > 0) 
        {
            if (Str::contains($arr['menuaccess'], ','))
            {
                $arr=explode(',',$arr['menuaccess']);
            }else
            {
                $arr=[$arr['menuaccess']];
            }
            if (is_array($arr)) 
            {
                return $arr;
            }
        }
        return null;
    }

    /**
     * Get array with dashboard items which could be accessed by loged user
     * 
     * @return array
     */
    public function getLogedUserDashAccess() 
    {
        $arr = $this->where('username', loged_user('username'))->first();
        if (is_array($arr) && array_key_exists('dashboardaccess', $arr) && strlen($arr['dashboardaccess']) > 0) 
        {
            $arr = json_decode($arr['dashboardaccess'], TRUE);
            if (is_array($arr)) 
            {
                return $arr;
            }
        }
        return null;
    }
    
    /**
     * Returns array with user data with given id
     * 
     * @param  int $id
     * @return array
     */
    public function getUserDataByID($id) 
    {
        $result = $this->find($id);
        unset($result['password']);
        unset($result['resetkey']);
        if (is_array($result) && count($result) > 0 && array_key_exists('access', $result)) 
        {
            $result['access'] = model('Users/LevelsModel')->where('level', $result['access'])->first();
            return $result;
        }
        return [];
    }
    
    /**
     * Save user status to db
     * 
     * @param  string  $email
     * @param  boolean $status
     * @return boolean
     */
    public function setUserStatus($email, $status) 
    {
        $this->builder()->set('lastlogin', $status ? formatDate() : '')->where('email', $email)->update();
        return TRUE;
    }
    
    public function setUserData(array $data,$findID)
    {
        if (array_key_exists('password', $data))
        {
            unset($data['password']);
        }
        if (array_key_exists('enabled', $data))
        {
            unset($data['enabled']);
        }
        return $this->builder()
                    ->set($data)
                    ->where('userid',$findID)
                    ->orWhere('email',$findID)
                    ->orWhere('username',$findID)
                    ->update();
    }
    
    /**
     * Set user as enabled or disabled
     * 
     * @param  string $findID
     * @param  mixed  $status
     * @return bool
     */
    public function setUserLoginStatus($findID,$status)
    {
        $status=$status==1 || $status ? 1 :0;
        return $this->builder()
                    ->set('enabled',$status)
                    ->where('userid',$findID)
                    ->orWhere('email',$findID)
                    ->orWhere('username',$findID)
                    ->update();
                        
    }
    
    
    /**
     * Set user avatar
     * 
     * @param  string $findID
     * @param  string $password
     * @return bool
     */
    public function setUserAvatar($findID,$avatarurl)
    {
        if (!parsePath($avatarurl))
        {
           $avatarurl='@assets/files/images/avatars/'.$avatarurl;
           if (!parsePath($avatarurl))
           {
               return FALSE;
           }
        }
        return $this->builder()
                    ->set('avatar',$avatarurl)
                    ->where('userid',$findID)
                    ->orWhere('email',$findID)
                    ->orWhere('username',$findID)
                    ->update();
    }


    /**
     * Returns with last n(5) logged users data from database
     * 
     * @param  int $interval
     * @param  int $limit
     * @return array
     */
    public function getUsersStatus($interval = 3600,$limit=5) 
    {
        $interval = ($interval / 3600);
        $current = formatDate();
        $users = $this->select('name,userid,avatar,lastlogin')->where('length(lastlogin)>', 0)->orderBy('lastlogin')->limit($limit)->find();
        return $users;
    }
    
    /**
     * Returns array with basic data for given user
     * 
     * @param  string $usernameOrEmail
     * @param  string $field
     * @return array
     */
    public function getUserBasicData($usernameOrEmail,$field=null) 
    {
        $result = $this->getUserData($usernameOrEmail, null, '||' . $usernameOrEmail, 2, null);
        if (is_array($result) && count($result) > 0) 
        {
            unset($result['password']);
            unset($result['resetkey']);
            unset($result['group_enabled']);
            return array_key_exists($field, $result) ? $result[$field] : $result;
        }
        return $field!=null ? null : [];
    }
    
    /**
     * Returns array with all data for given user
     * 
     * @param  string $username
     * @param  string $access
     * @param  string $email
     * @return array
     */
    public function getUserData1($username = null, $access = null, $email = null) 
    {
        $data = $this->getUsersData($username, $access, $email, 2, 1);
        return $data;
    }
    
    /**
     * Get single user data
     * 
     * @param array $filters
     * @param bool  $asArray
     * 
     * @return UserObject|array
     */
    public function getUserData(array $filters=[],bool $asArray=FALSE)
    {
        $arr=$this->filtered($filters)->first();
        return $asArray ? $arr : new UserObject($arr);
    }
    
    /**
     * Returns array with all users data
     * 
     * @param  string $username
     * @param  string $access
     * @param  string $email
     * @param  int  $enabled
     * @param  int  $limit
     * @return array
     */
    public function getUsersData($username = null, $access = null, $email = null, int $enabled = 2, int $limit = null) 
    {
        $limit = $limit == null && $limit != FALSE ? config('Pager')->perPage : $limit;
        $result = $this;
        $filters = [];
        $fields[] = 'users_old.*';
        $fields[] = "(select GROUP_CONCAT(`users_groups`.`ugperms`) FROM `users_groups` WHERE  FIND_IN_SET (`users_groups`.`ugref`,`users_old`.`accessgroups`)) as 'access'";
        $result->select(implode(',', $fields));
        if ($username != null) 
        {
            if (is_numeric($username)) 
            {
                $filters[$this->primaryKey] = $username;
            } else 
            {
                $filters['username'] = $username;
            }
        }

        if ($access != null) 
        {
            $filters['access <='] = $access;
        }

        if ($email != null && \EMPORIKO\Helpers\Strings::contains($email, '@')) 
        {
            if (\EMPORIKO\Helpers\Strings::startsWith($email, '||')) 
            {
                $filters['|| email'] = substr($email, 2);
            } else 
            {
                $filters['|| email'] = $email;
            }
        }

        if ($enabled != null && $enabled < 2) 
        {
            $filters['enabled'] = $enabled;
        }
        $result = $result->filtered($filters);

        if ($limit == FALSE) 
        {
            return $result->find();
        } else
        if ($limit == 1) 
        {
            return $result->first();
        } else
        if ($limit != null) 
        {
            return $result->paginate($limit);
        } else 
        {
            return $result->find();
        }
    }

    /**
     * Get users data for form
     * 
     * @param  String $field    Name of linked field (primary key by default)
     * @param  string $value    Text field name (showed to end user)
     * @param  bool   $addEmpty Determine if empty field will be added
     * @param  string $defValue Default value field name if $value is null or not exists in allowed fields array
     * @return Array
     */
    function getForForm($field = null, $value = null, $addEmpty = FALSE, $defValue = null, $encode = TRUE,array $filters=[]) 
    {
        $defValue = $defValue == null ? 'name' : $defValue;
        $field = $field == null ? $this->primaryKey : $field;
        $field = in_array($field, $this->allowedFields) ? $field : $this->primaryKey;
        $value = $value == null ? $defValue : $value;
        $value = in_array($value, $this->allowedFields) || $value == $this->primaryKey ? $value : $defValue;
        if (!in_array($field, $this->allowedFields)) 
        {
            $field = $this->primaryKey;
        }
        
        if ($field == 'email') 
        {
            $this->where('LENGTH(email) >', 0);
        }
        $filters['enabled']=1;
        $result=[];
        foreach ($this->filtered($filters)->orderby($value)->find() as $record) 
        {
            if ($encode) 
            {
                $result[base64url_encode($record[$field])] = $record[$value];
            } else 
            {
                $result[$record[$field]] = $record[$value];
            }
        }
        
        if ($addEmpty != FALSE) 
        {
            if (is_string($addEmpty)) 
            {
                $result[] = $addEmpty;
            } else 
            {
                $result[] = '';
            }
        }
        return $result;
    }
    
    /**
     * Returns array with users email addresses
     * 
     * @param  boolean $onlyAdmins
     * @param  string  $keyField
     * @param  string  $textField
     * @return array
     */
    function getUsersEmails($onlyAdmins=FALSE,$valueField=null,$textField=null)
    {
        $textField= in_array($textField, $this->allowedFields) ? $textField : 'email';
        $valueField= in_array($valueField, $this->allowedFields) ? $valueField : 'name';
        /**/
        if ($onlyAdmins)
        {
            $onlyAdmins=$this->getView('vw_adminusers')->find();
        } else 
        {
            $onlyAdmins=$this->find();
        }
        $arr=[];
        foreach($onlyAdmins as $value)
        {
            $arr[$value[$valueField]]=$value[$textField];
        }
        return $arr;
    }
   
    
    /**
     * Returns array with users data which have super admin rights
     * 
     * @param  string $field
     * @return array
     */
    function getSuperAdminUsers($field=null)
    {
        $data=$this->Like('accessgroups',$this->getModel('UserGroup')->getSuperAdminsGroup())->find();
        if ($field==null)
        {
            return $data;
        }
        $arr=[];
        foreach($data as $value)
        {
            if (array_key_exists($field, $value))
            {
                $arr[]=$value[$field];
            }
        }
        return count($arr) > 0 ? $arr : [];
    }
    
    /**
     * Determines if given (or logged) user is super admin
     * 
     * @param  string $user
     * @return bool
     */
    function isUserSuperAdmin($user=null)
    {
        $user=$user==null ? loged_user('username') : $user;
        $user=$this->where('username',$user)->Like('accessgroups',$this->getModel('UserGroup')->getSuperAdminsGroup())->first();
        return is_array($user) && array_key_exists('username', $user);
    }
   
    /**
     * Returns array with super admin users email addresses
     * 
     * @return array
     */
    function getAdminUsersEmails() 
    {
        $admin_user = $this->getAdminUsers(null, 'email');
        if (is_array($admin_user) && count($admin_user) > 0) 
        {
            $bcc = [];
            foreach ($admin_user as $value) 
            {
                $bcc[] = $value['email'];
            }
            return $bcc;
        }
        return [];
    }
    
    /**
     * Returns array with data of currently logged users
     * 
     * @return array
     */
    function getLogedUsers() 
    {
        $authTokenExpiry = config('APP')->authTokenExpiry;
        $authTokenExpiry = $authTokenExpiry / 60;

        $users = $this->filtered(['lastlogin <' => formatDate() + $authTokenExpiry, 'lastlogin >' => formatDate() - $authTokenExpiry])->find();
        $arr = [];
        foreach (is_array($users) ? $users : [] as $value) 
        {
            $arr[$value['name']] = convertDate($value['lastlogin'], 'DB', 'd M Y H:i');
        }
        return $arr;
    }

    /**
     * Clear reset key for user
     * 
     * @param  string $id          User record id
     * @param  string $newPassword User new password
     * @return bool
     */
    function clearResetKey($id, $newPassword = null) 
    {

        $model = $this->set('resetkey', '')->where(is_numeric($id) ? $this->primaryKey : 'username', $id)->update();
        if ($newPassword != null && strlen($newPassword) > 5) 
        {
            $this->setUserPassword($id, $newPassword);
        }
        return TRUE;
    }
    
    /**
     * Save model data to DB
     * 
     * @param  array $data
     * @return bool
     */
    function save($data): bool 
    {
        if (array_key_exists('password', $data)) 
        {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT, ['cost ' => 12]);
        }
        return parent::save($data);
    }

    /**
     * Returns default avatar path
     * 
     * @return string
     */
    function getDefaultAvatar($base64=TRUE,$filePath=TRUE) 
    {
        $avatar= 'data:image/png;base64,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';
        $avatar= parsePath('@assets/files/images/avatars/user_avatar.png',$filePath);
        return $base64 ? protected_link($avatar,TRUE) :$avatar;
    }
    
    function setUserPassword($userid,$pass)
    {
        return $this->builder()->set('password', password_hash($pass, PASSWORD_DEFAULT, ['cost ' => 12]))->where(is_numeric($userid) ? $this->primaryKey : 'username',$userid)->update();
    }
    /**
     * Install model table and insert data to storage (db)
     * 
     * @return bool
     */
    public function installstorage() 
    {
        if (!parent::installstorage()) 
        {
            return FALSE;
        }
        return $this->insert(
                        [
                            'name' => 'sadmin',
                            'username' => 'sadmin',
                            'access' => '1000',
                            'enabled' => '1',
                            'ugname' => 'Administrators'
                        ]
        );
    }

}
