<?php

/*
 *  This file is part of EMPORIKO WMS
 * 
 * 
 *  @version: 1.1					
 * 	@author Artur W				
 * 	@copyright Copyright (c) 2022 All Rights Reserved				
 *
 *  @license https://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

namespace EMPORIKO\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use EMPORIKO\Helpers\UserInterface;
use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\AccessLevel;
use \EMPORIKO\Helpers\MovementType;
use \EMPORIKO\Helpers\ApiCallMethod;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 * 
 * Front Access Levels : modaccview,modaccedit,modaccamend,modaccdel
 * Admin Access Levels : modaccviewadmin,modacceditadmin,modaccdeladmin
 */
class BaseController extends Controller {

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Array with function names and access levels from which they can be accessed
     * @var Array
     */
    protected $access = [];

    /**
     * Access module class name if different that current controller
     * @var String
     */
    protected $access_controller;

    /**
     * Array with methods which are excluded from authentication check
     * @var array
     */
    protected $no_access = [];

    /**
     * Determines if authentication is enabled
     * @var bool
     */
    private $_noauth = FALSE;

    /**
     * Array with access levels
     * @var Array
     */
    private $_access_levels;

    /**
     * Array with function names which can be accessed only on POST
     * @var Array
     */
    protected $postactions = [];

    /**
     * Array with function names which are excluded from routes actions
     * @var Array
     */
    protected $routerexlude = [];
   
    /**
     * Array with function names which are enabled when accessing from mobile device
     * @var Array
     */
    protected $mobilenebaled=[];


    /**
     * Array with function names and linked models names
     */
    public $assocModels = [];

    /**
     * Array with controller method remaps ($key is fake function name and $value is actuall function name)
     */
    public $remaps = [];
    
    /**
     * Array with available menu items (keys as function names and values as description)
     * @var Array
     */
    public $availablemenuitems = [];
    
    /**
     * Storage engine
     * @var type
     */
    protected  $storage=null;

    /**
     * Constructor.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param LoggerInterface   $logger
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        
        if (config('Cookies')->enabled)
        {
            helper('\EMPORIKO\Libraries\CookiesManager\Helpers\gdpr_cookie');
        }
        
        if (method_exists($this, 'beforeInit')) {
            $this->{'beforeInit'}($request, $response, $logger);
        }
        $this->helpers = array_merge($this->helpers, ['html', 'date', 'filesystem', 'text']);
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->session = \Config\Services::session();

        //$this->auth=$this->load(config('App')->authMethodControllerClass);

        $this->cookies = service('CookieManager', TRUE);

        $this->access['index'] = !array_key_exists('index', $this->access) ? AccessLevel::view : $this->access['index'];

        $this->access['enable'] = !array_key_exists('enable', $this->access) ? AccessLevel::state : $this->access['enable'];
        $this->access['enablesingle'] = !array_key_exists('enablesingle', $this->access) ? AccessLevel::state : $this->access['enablesingle'];

        $this->access['save'] = !array_key_exists('save', $this->access) ? AccessLevel::edit : $this->access['save'];

        $this->access['delete'] = !array_key_exists('delete', $this->access) ? AccessLevel::delete : $this->access['delete'];
        $this->access['deletesingle'] = !array_key_exists('deletesingle', $this->access) ? AccessLevel::delete : $this->access['deletesingle'];

        if (method_exists($this, 'afterInit')) {
            $this->{'afterInit'}();
        }


        $this->_access_levels = $this->model_Auth_UserGroup->getForForm('ugref', 'name');
        $this->view = new Pages\View($this);
        $this->auth=auth();
        /*if (!$this->_noauth) {
            $user = $this->auth->user();
            if (!$user->isActive()) 
            {
                $this->view->addData('msg', lang('system.errors.no_acces'))
                        ->addData('type', 'danger');
                $this->View('errors/html/exception');
                exit;
            }
        }*/

        $this->assocModels['settings'] = 'Settings/Settings';
        $this->assocModels['movements'] = 'System/Movements';
        $this->assocModels['modules'] = 'System/Module';
        
        $this->mobilenebaled[]='loginform';
        
        $this->postactions[] = 'save';
        $this->postactions[] = 'delete';
        $this->routerexlude[]='list_records';
        $this->routerexlude[]='edit_record';
        if ($this->storage=='local' || $this->storage=='temp')
        {
            $this->storage= \EMPORIKO\Libraries\StorageEngine\StorageEngine::class;
        }
        $this->storage=storage($this->storage);
        $locale=loged_user('language');
        service('language')->setLocale($locale==null ? config('APP')->defaultLocale : $locale);
    }
   
    /**
     * Set current view as FormView
     * 
     * @param  string $viewName Name of view (default System/form)
     * @param  bool   $iscached Determines if view is cached (TRUE as default)
     * 
     * @return \EMPORIKO\Libraries\Pages\FormView
     */
    public function setFormView($viewName = 'System/form', $iscached = FALSE) {
        $this->view = new Pages\FormView($this, $iscached);
        if($viewName!=='System/form' && !file_exists(parsePath('@views/'.$viewName.'.php',TRUE)))
        {
            $viewName='System/form';
        }
        return $this->view->setFile($viewName)->addData('_formview_custom', $viewName != 'System/form');
    }
    
    /**
     * Returns access level for method or FALSE if no access is linked to method (null if method is not specified in access array or is invalid)
     * 
     * @param string $method
     * 
     * @return boolean|string|null
     */
    public function getAccessForMethod(string $method)
    {
        if (in_array($method, $this->no_access))
        {
            return FALSE;
        }
        
        if (array_key_exists($method, $this->access))
        {
            return  $this->access[$method];
        }
        return null;
    }
    
    /**
     * Set current view as FormView
     * 
     * @param  string $viewName Name of view (default System/table)
     * @param  bool   $iscached Determines if view is cached (TRUE as default)
     * 
     * @return \EMPORIKO\Libraries\Pages\TableView
     */
    public function setTableView($viewName = 'System/table', $iscached = FALSE) 
    {
        $this->view = new Pages\TableView($this, $iscached);
        if($viewName!=='System/table' && !file_exists(parsePath('@views/'.$viewName.'.php',TRUE)))
        {
            $viewName='System/table';
        }
        return $this->view->setFile($viewName)->addData('_tableview_custom', $viewName != 'System/table');
    }

    /**
     * Set current view as DashBoardView
     * 
     * @param  string $viewName Name of view (default System/dashboard)
     * @param  bool   $iscached Determines if view is cached (TRUE as default)
     * 
     * @return \EMPORIKO\Libraries\Pages\DashBoardView
     */
    public function setDashBoardView($viewName = 'System/dashboard', $iscached = TRUE) {
        $this->view = new Pages\DashBoardView($this, $iscached);
        return $this->view->setFile($viewName)->addData('_dashview_custom', $viewName != 'System/dashboard');
    }

    /**
     * Set current view as error message
     * 
     * @param  string $errorMSG
     * @param  bool   $render
     * 
     * @return \EMPORIKO\Libraries\Pages\View
     */
    public function setErrorView($errorMSG, $render = FALSE) {
        $this->view->addData('type', 'danger')
                ->addData('msg', lang($errorMSG))
                ->setFile('errors/html/exception');
        return $render ? $this->view->render() : $this->view;
    }
    
    /**
     * Check if given field and value validate (returns result as json string)
     * 
     * @param string $model
     * @param string $field
     * @param string $value
     * 
     * @return string
     */
    function validatefield($model,$data=null)
    {        
        if (!array_key_exists($model, $this->assocModels))
        {
            return json_encode(['error'=>'Invalid Model']);
        }
        $model='model_'.$model;
        /*$field=$field==null ? $this->request->getGet('field') : $field;
        $field=$field==null ? $this->request->getPost('field') : $field;
        if ($field==null || ($field!=null && !in_array($field, $this->{$model}->allowedFields)))
        {
            return json_encode(['error'=>'Invalid Field']);
        }
        
        $value=$value==null ? $this->request->getGet('value') : $value;
        $value=$value==null ? $this->request->getPost('value') : $value;
        
        $check_field=$check_field==null ? $this->request->getGet('check_field') : $check_field;
        $check_field=$check_field==null ? $this->request->getPost('check_field') : $check_field;
        
        $check_value=$check_value==null ? $this->request->getGet('check_value') : $check_value;
        $check_value=$check_value==null ? $this->request->getPost('check_value') : $check_value;
        */
        $data=$data==null ? $this->request->getGet() : $data;
        $data=$data==null ? $this->request->getPost() : $data;
        if (!is_array($data))
        {
            goto end_of_fnc;
        }
        $rules=$this->{$model}->validationRules;
        if (!is_array($rules))
        {
            goto end_of_fnc;
        }
        $messages=$this->{$model}->validationMessages;
        if (!is_array($messages))
        {
            goto end_of_fnc;
        }
        $validation= \CodeIgniter\Config\Services::validation();
        if (!$validation->setRules($rules/*[$field=>$rules[$field]]*/, $messages)->run($data))//[$field=>$value,$check_field=>$check_value]))
        {
            return json_encode(['error'=>$validation->getErrors()]);
        }
        end_of_fnc:
        return json_encode(['result'=>'valid']);
    }
    
    /**
     * Returns array with avaliable routes (method names)
     * 
     * @return Array
     */
    function getAvaliableRoutes() {
        $arr = [];
        $routerexlude = $this->routerexlude;
        $routerexlude[] = 'enable';
        $routerexlude[] = 'save';
        $routerexlude[] = 'delete';
        foreach ($this->access as $key => $value) {
            if (!in_array($key, $routerexlude)) {
                $arr[] = $key;
            }
        }
        return $arr;
    }
    
    /**
     * Return address of API service call
     * @param ApiCallMethod $callMethod
     * @return type
     */
    function getApiUrl($callMethod= ApiCallMethod::POST)
    {
        return url('Api',$callMethod);
    }
    
    /**
     * Enable or disable item in DB
     * 
     * @param type $model
     * @param type $id
     * @param type $value
     * @param type $field
     * 
     * @return bool
     */
    public function enablesingle($model, $id, $value, $field = null) {
        $refurl = $this->getRefUrl();
        if (!array_key_exists($model, $this->assocModels)) {
            return redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_enbale_no', 'danger'));
        }
        $model = 'model_' . $model;
        $model = $this->{$model};
        $field = $field == null ? $model->primaryKey : $field;
        if ($model->builder()->set('enabled', $value)->where($field, $id)->update()) {
            return redirect()->to($refurl)->with('error', $this->createMessage('system.general.msg_enbale_ok', 'success'));
        } else {
            return redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_enbale_no', 'danger'));
        }
    }

    /**
     * Enable or disable items in DB
     * 
     * @param array   $post
     * @param string  $msgYes
     * @param string  $msgNo
     * 
     * @return bool
     */
    public function enable($post = null,$msgYes=null,$msgNo=null) 
    {
        $refurl = $this->getRefUrl();
        $msgYes=$msgYes==null ? 'system.general.msg_enbale_ok' : $msgYes;
        $msgNo=$msgNo==null ? 'system.errors.msg_enbale_no' : $msgNo;
        $post = $post == null ? $this->request->getPost() : $post;
        if (array_key_exists('model', $post)) {
            $model = $post['model'];
            unset($post['model']);
        } else {
            return redirect()->to($refurl)->with('error', $this->createMessage($msgNo, 'danger'));
        }

        if (!array_key_exists($model, $this->assocModels)) {
            return redirect()->to($refurl)->with('error', $this->createMessage($msgNo, 'danger'));
        }


        if (is_array($post) && count($post) > 0) {
            $tkey = array_keys($post);
            $post = array_values($post);
            $enable = $this->request->getGet('enable');

            if (is_array($post[0]) && count($post[0]) > 0 && is_numeric($enable)) {
                $model = 'model_' . $model;
                $model = $this->{$model};

                foreach ($post[0] as $key => $value) {
                    $model = $model->orWhere($tkey[0], $value);
                }

                $model = $model->builder->set('enabled', $enable == 1 ? 1 : 0);

                if ($model->update()) {
                    return redirect()->to($refurl)->with('error', $this->createMessage($msgYes, 'success'));
                } else {
                    return redirect()->to($refurl)->with('error', $this->createMessage($msgNo, 'danger'));
                }
            }
        }
        return redirect()->to($refurl)->with('error', $this->createMessage($msgNo, 'danger'));
    }

    /**
     * Delete single record
     * 
     * @param type $model
     * @param type $value
     * @param type $field
     * @return type
     */
    public function deletesingle($model, $value, $field = null) {
        $refurl = $this->getRefUrl();
        if (!array_key_exists($model, $this->assocModels)) {
            return redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_delete_no', 'danger'));
        }
        $model = 'model_' . $model;
        $model = $this->{$model};
        $field = $field == null ? $model->primaryKey : $field;
        if ($model->where($field, $value)->delete()) {
            return redirect()->to($refurl)->with('error', $this->createMessage('system.general.msg_delete_ok', 'success'));
        } else {
            return redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_delete_no', 'danger'));
        }
    }

    /**
     * Delete Item from database
     */
    public function delete(array $post = []) {
        $refurl = $this->getRefUrl();

        $post = count($post) > 0 ? $post : $this->request->getPost();

        if (array_key_exists('model', $post)) {
            $model = $post['model'];
            unset($post['model']);
        } else {
            return redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_delete_no', 'danger'));
        }

        if (!array_key_exists($model, $this->assocModels)) {
            return redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_delete_no', 'danger'));
        }


        if (is_array($post) && count($post) > 0) {
            $tkey = array_keys($post);
            //$post=array_values($post);

            $model = 'model_' . $model;
            $model = $this->{$model};

            if (array_key_exists($model->primaryKey, $post) && is_array($post[$model->primaryKey]) && count($post[$model->primaryKey]) > 0) {
                foreach ($post[$model->primaryKey] as $key => $value) {
                    $model = $model->orWhere($model->primaryKey, $value);
                }

                if ($model->delete()) {
                    return redirect()->to($refurl)->with('error', $this->createMessage('system.general.msg_delete_ok', 'success'));
                } else {
                    return redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_delete_no', 'danger'));
                }
            }
        }
        return redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_delete_no376', 'danger'));
    }

    /**
     * Save data to database
     * 
     * @param string $type
     */
    function save($type, $post = null) {
        $post = !is_array($post) ? $this->request->getPost() : $post;
        $refurl = $this->getRefUrl();
        $refurl_ok = $refurl;
        $type = is_array($type) && count($type) > 0 ? $type[0] : $type;
        
        
        
        if (array_key_exists('refurl_ok', $post)) 
        {
            $refurl_ok = $post['refurl_ok'];
        }
        $type = 'model_' . $type;
        $model = $this->{$type};
        if ($model == null) {
            return array_key_exists('_nomsg', $post) && $post['_nomsg'] ? $this->createMessage('system.errors.msg_invalid_save_model', 'danger') : redirect()->to($refurl)->with('error', $this->createMessage('system.errors.msg_invalid_save_model', 'danger'))->with('_postdata', $post);
        }
        
        if (!is_array($post) || (is_array($post) && count($post) < 1))
        {
            goto save_error;
        }
        
        if (array_key_exists($model->primaryKey, $post) && !is_numeric($post[$model->primaryKey])) 
        {
            unset($post[$model->primaryKey]);
        }
        if (count($post) < 1)
        {
            goto save_error;
        }
        $this->flatenArrayValues($post);
        $this->saveAsOtherModel($post);
        $this->uploadFiles($post);
        if ($model->save($post)) 
        {
            if (array_key_exists('@', $post) && (is_array($post['@']) || Str::isJson($post['@']))) {
                $this->saveExternal(Str::isJson($post['@']) ? json_decode($post['@'], TRUE) : $post['@']);
            }
            //Log movements
            $this->addMovementsFromArray($post);

            if (array_key_exists('customfields', $post) && is_array($post['customfields'])) {
                foreach ($post['customfields'] as $value) 
                {
                    if (!array_key_exists('target', $value) && array_key_exists($model->primaryKey, $post)) {
                        $value['target'] = $post[$model->primaryKey];
                    } else
                    if (!array_key_exists('targetid', $value)) {
                        $value['target'] = $model->db->insertID();
                    }
                    $this->model_Settings_CustomFields->save($value);
                }
            }
            
            if (method_exists($this, '_after_save')) 
            {
                $post['_id']=$model->getLastID();
                $ret = $this->{'_after_save'}($type, $post, $refurl, $refurl_ok);
                if ($ret == FALSE) {
                    return false;
                } else
                if (!is_bool($ret)) {

                    return $ret;
                }
            }
            if (array_key_exists('print_pall', $post)) {
                $post['print_pall'] = str_replace('%id%', $model->getLastID(), $post['print_pall']);
            }
            if (!Str::startsWith(strtolower($refurl_ok), 'http')) {
                $refurl_ok = base64url_decode($refurl_ok);
            }
            if (!array_key_exists('_msg_ok', $post))
            {
                $post['_msg_ok']='system.general.msg_save_ok';
            }
            return array_key_exists('_nomsg', $post) && $post['_nomsg'] ? TRUE : redirect()->to($refurl_ok)->with('error', $this->createMessage($post['_msg_ok'], 'success'))->with('print_pall', array_key_exists('print_pall', $post) ? $post['print_pall'] : null);
        } else {
            save_error:
            return array_key_exists('_nomsg', $post) && $post['_nomsg'] ? $this->createMessage($model->errors(), 'danger') : redirect()->to($refurl)->with('error', $this->createMessage($model->errors(), 'danger'))->with('_postdata', $post);
        }
    }
    
    /**
     * Return view with pages setup
     * 
     * @param string         $mode
     * @param Pages\FormView $view
     * @param array          $data
     * 
     * @return \EMPORIKO\Controllers\Pages\FormView
     */
    function pages(string $mode, Pages\FormView $view,array $data)
    {
        return $view;
    }
    
    protected function flatenArrayValues(&$post)
    {
         if (array_key_exists('_check_array_fields', $post)) 
         {
            $post['_check_array_fields'] = json_decode($post['_check_array_fields'], TRUE);
            if (is_array($post['_check_array_fields'])) {
                $post['_check_array_fields'] = array_intersect_key($post, array_flip($post['_check_array_fields']));
            } else {
                $post['_check_array_fields'] = $post;
            }

            foreach ($post['_check_array_fields'] as $key => $value) {
                if (is_array($value)) {
                    $post[$key] = json_encode($value);
                }
            }
            unset($post['_check_array_fields']);
        }
    }
    
    /**
     * Save Data in other specified model
     * 
     * @param type $post
     */
    protected function saveAsOtherModel(&$post)
    {
        if (array_key_exists('_model', $post) && is_array($post['_model']))
        {
            $uploads = $this->request->getFiles();
            
            foreach($post['_model'] as $model=>$data)
            {
                $this->uploadFiles($data);
                foreach(array_keys($uploads) as $key)
                {
                    if (array_key_exists($key, $data))
                    {
                        unset($_FILES[$key]);
                    }
                }
                $data['_check_array_fields']=1;
                if (array_key_exists('_model', $data) )
                {
                    $model=$data['_model'];
                }
                $model=!Str::contains($model, '/')&&!strtolower($model)=='settings' ? base64_decode($model) : $model;
                if (strtolower($model)=='settings')
                {
                    foreach($data as $key=>$value)
                    {
                        $this->model_Settings->write($key, $value);
                    }
                    goto endloop;
                }
                if (!Str::contains($model, '/'))
                {
                    goto endloop;               
                }
                if (!Str::endsWith($model, 'Model'))
                {
                    $model.='Model';
                }
                $model=model($model);
               
                if (is_subclass_of($model,'\EMPORIKO\Models\BaseModel'))
                {
                    $model->save($data);
                }
                endloop:
            }
            unset($post['_model']);
        }
    }


    /**
     * Function run after successful save
     * 
     * @param type $type
     * @param type $post
     * @param type $refurl
     * @param type $refurl_ok
     * 
     * @return boolean
     */
    protected function _after_save($type, $post, $refurl, $refurl_ok) {
        return TRUE;
    }

    /**
     * Init save method from external controller
     * 
     * @param array $post
     * @return boolean
     */
    protected function saveExternal(array $post = []) {
        foreach ($post as $key => $value) {
            if (is_string($key) && Str::contains($key, '.') && is_array($value)) {
                $key = explode('.', $key);
                foreach ($value as $row) {
                    $row['_nomsg'] = TRUE;
                    $value = loadModule($key[0], 'save', [$key[1], $row]);
                }
            }
        }
        return TRUE;
    }

    protected function addMovementsFromArray(array $post) {
        if (array_key_exists('movements_logger', $post)) {
            if (is_string($post['movements_logger'])) {
                if (Str::isJSON($post['movements_logger'])) {
                    $post['movements_logger'] = json_decode($post['movements_logger'], TRUE);
                } else {
                    $post['movements_logger'] = json_decode(base64_decode($post['movements_logger']), TRUE);
                }
            }

            foreach (is_array($post['movements_logger']) ? $post['movements_logger'] : [] as $value) {
                if (Arr::KeysExists(['mhtype', 'type', 'mhref', 'mhfrom', 'mhto', 'mhinfo'], $value)) {
                    $this->addMovementHistory($value['mhtype'], $value['mhfrom'], $value['mhto'], $value['mhref'], $value['mhinfo'], $value['type']);
                }
            }
        }
    }
    
    /**
     * Download file from server
     * 
     * @param string $filename
     * @param type   $data
     * @param bool   $setMime
     * 
     * @return \EMPORIKO\Libraries\DownloadResponse
     */
    protected function downloadFileFromServer(string $filename = '', $data = '', bool $setMime = false)
    {
        return \EMPORIKO\Libraries\DownloadResponse::downloadFile($filename,$data,$setMime);
    }
    
    protected function uploadFiles(&$post,$keyToExtract=null) 
    {
        $uploads = $this->request->getFiles();
        $storage=$this->storage;
        
        $uploads= array_key_exists($keyToExtract, $uploads) ? $uploads[$keyToExtract] : $uploads;
        $uploads_dir = array_key_exists('_uploads_dir', $post) && ($storage->fileExists($post['_uploads_dir']) || file_exists(parsePath($post['_uploads_dir'],TRUE))) ? $post['_uploads_dir'] : '@temp';
        
        foreach ($uploads as $fieldName => $file) 
        {
            if (is_a($file, '\CodeIgniter\HTTP\Files\UploadedFile') && $file->isValid() && !$file->hasMoved()) 
            {
                $fileName = $file->getClientName();
                if (array_key_exists('_upload_filename', $post))
                {
                    if ($post['_upload_filename']=='@')
                    {
                        $newFileName = $fileName; 
                    }else
                    {
                        $newFileName = $post['_upload_filename'].'.'.$file->getExtension();
                        $fileName=$newFileName;
                    }           
                }else
                {
                    $newFileName =$file->getRandomName();
                }
                if (array_key_exists('_storage_engine', $post))
                {
                    if (is_string($post['_storage_engine']))
                    {
                       $storage= storage($post['_storage_engine']); 
                    }else
                    if (is_object($post['_storage_engine']))
                    {
                        $interfaces = class_implements($post['_storage_engine']);
                        if (isset($interfaces['EMPORIKO\Libraries\StorageEngine\EngineInterface']))
                        {
                            $storage=$post['_storage_engine']; 
                        } else 
                        {
                            goto local_engine;
                        }
                       
                    }else
                    {
                        goto local_engine;
                    }
                    $uploads_dir = array_key_exists('_uploads_dir', $post) && ($storage->fileExists($post['_uploads_dir']) || file_exists(parsePath($post['_uploads_dir'],TRUE))) ? $post['_uploads_dir'] : config('Storage')->deffolderid;
                    
                }else
                if (Str::startsWith($uploads_dir, '@'))
                {
                   local_engine:
                   $storage= $this->storage;
                }
                $status=$storage->upload($uploads_dir,$file,$newFileName, array_key_exists('_upload_options', $post) ? $post['_upload_options'] : []);
                
                if (is_bool($status) && $status==TRUE) 
                {
                   $nfilePath=$storage->getRelativePath();
                   if (array_key_exists('_fieldname', $post) && array_key_exists($post['_fieldname'], $post))
                   {
                       $fieldName=$post['_fieldname'];
                   }
                   $post[$fieldName] = array_key_exists('_export_justname', $post) ? $nfilePath : json_encode([$fileName => $nfilePath,'ext'=>Str::afterLast($fileName, '.'),'name'=>$fileName]);
                }else
                {
                    return $status;
                }
            }
        }
    }

    function _remap($method, ...$params) {

        $access = AccessLevel::view;
        $orig_method=$method;
        if (is_array($this->remaps) && array_key_exists($method, $this->remaps)) 
        {
            $remaps = $this->remaps[$method];
            if (is_array($remaps)) 
            {
              if (count($remaps) > 1 && is_array($remaps[1]))
              {
                  $method=$remaps[0];
                  foreach($remaps[1] as $key=>$item)
                  {
                      if (is_string($item) && Str::startsWith($item,'$'))
                      {
                          $kkey=substr($item, 1);
                          if (is_numeric($kkey))
                          {
                              $kkey=intval($kkey)-1;
                          }
                          if (array_key_exists($kkey, $params))
                          {
                              $remaps[1][$key]=$params[$kkey];
                          }else
                          {
                              $remaps[1][$key]=null;
                          }
                      }
                  }
                  $params=$remaps[1];
              }else
              if (count($remaps) == 3 && is_string($remaps[1]) && is_array($remaps[2]))
              {
                  foreach($remaps[2] as $key=>$item)
                  {
                      if (Str::startsWith($item,'$'))
                      {
                          $kkey=substr($item, 1);
                          if (is_numeric($kkey))
                          {
                              $kkey=intval($kkey)-1;
                          }
                          if (array_key_exists($kkey, $params))
                          {
                              $remaps[2][$key]=$params[$kkey];
                          }else
                          {
                              $remaps[2][$key]=null;
                          }
                      }
                  }
                  loadModule($remaps[0],$remaps[1],$remaps[2]);
              }
              if (count($remaps) > 2 && is_string($remaps[1]))
              {
                  loadModule($remaps[0],$remaps[1]);
              }
            }else
            if (is_string($remaps)) 
            {
                $method = $remaps;
            }          
        }
        if ($this->isMobile() && !in_array($method, $this->mobilenebaled))
        {
            $this->view->setFile('errors/html/exception')
                    ->addData('msg', lang('system.errors.invalidmobilerequest'))
                    ->addData('type', 'warning')
                    ->render();
            exit;
        }
        
        if (in_array($orig_method, $this->postactions) && $this->request->getMethod() != 'post') 
        {
            $this->view->setFile('errors/html/exception')
                    ->addData('msg', lang('system.errors.invalidrequest'))
                    ->addData('type', 'danger')
                    ->render();
            exit;
        }
        $uri = service('uri');
        if ($uri->getTotalSegments() > 1 && in_array($uri->getSegment(2), $this->routerexlude)) 
        {
            $this->view->setFile('errors/html/exception')
                    ->addData('msg', lang('system.errors.noroutemethod'))
                    ->addData('type', 'danger')
                    ->render();
            exit;
        }

        $access = 'view';
       
        if (is_array($this->access) && array_key_exists($orig_method, $this->access)) 
        {
            if (array_key_exists($this->access[$orig_method], $this->_access_levels)) 
            {
                $access = $this->_access_levels[$this->access[$orig_method]];
            } else {
                $access = $this->access[$orig_method];
            }
        }
        if (!method_exists($this, $method)) 
        {
            error_404:
            $this->getNoPageError();
            exit;
        }
        return $this->$method(...$params);
        if (!$this->_noauth && $this->auth->hasAccess($this->getModuleAccess($access))) 
        {
            if (!method_exists($this, $method)) 
            {
                $this->getNoPageError();
                exit;
            }
        } else
        if (!$this->_noauth) 
        {
            access_error:
            $this->getAccessError();
            exit;
        }
        return $this->$method(...$params);
    }
    
    /**
     * Get module unique name
     * 
     * @return type
     */
    function getModuleName()
    {
      return strtolower(Str::afterLast(get_class($this), '\\'));  
    }
    
    function getModuleAccess($access) 
    {
        if ($this->access_controller != null && strlen($this->access_controller) > 0) 
        {
            $class = $this->access_controller;
        } else 
        {
            $class = str_replace('home', 'menu', $this->getModuleName());
        }
        return $class.'.'.$access;
    }

    /**
     * Install tables etc
     */
    function install() {
        $msg = 'warning: no models';
        if (is_array($this->assocModels) && count($this->assocModels) > 0) {
            foreach ($this->assocModels as $key => $value) {
                if (!in_array($key, ['settings', 'movements', 'modules'])) {
                    $model = 'model_' . $key;
                    $model = $this->{$model};
                    if ($model != null) {
                        $msg = $model->installstorage();
                    } else {
                        $msg = 'error: ' . $value . ' is not valid model';
                    }
                }
            }
        }
        end_func:
        return $this->response->setJson([$msg]);
    }

    /**
     * Returns session temporary data
     * 
     * @param  string $key         Session temp data key
     * @param  mixed  $defaultData Default data returned if session temp data not exists
     * 
     * @return mixed
     */
    public function getFlashData($key, $defaultData = null) {
        if (is_array($this->session->getFlashdata()) && array_key_exists($key, $this->session->getFlashdata())) {
            $data = $this->session->getFlashdata($key);
            if ($data != null) {
                return is_string($data) ? lang($data) : $data;
            }
            return $data;
        }
        return $defaultData;
    }

    /**
     * Create html message container
     * 
     * @param  String $message Message text (if prefix with @ it will be used as language tag name)
     * @param  String $type    Type of message (danger,info,success)
     * @param  mixed  $encode  Determine if html code is base64 (or base64url) encoded
     * @return String
     */
    public function createMessage($message, $type = 'info', $encode = FALSE) {
        return createErrorMessage($message, $type, $encode);
    }
    
    /**
     * Clears all error messages from session
     */
    function clearErrorMsgData()
    {
        $this->session->setFlashdata('error','');
    }
    
    /**
     * Add error message to current session
     * 
     * @param string $message
     * @param bool   $encode
     */
    public function addErrorMsgData($message, $encode = FALSE) {
        $this->session->setFlashdata('error', createErrorMessage($message, 'danger', $encode));
    }

    /**
     * Add warning message to current session
     * 
     * @param string $message
     * @param bool   $encode
     */
    public function addWarningMsgData($message, $encode = FALSE) {
        $this->session->setFlashdata('error', createErrorMessage($message, 'warning', $encode));
    }

    /**
     * Add info message to current session
     * 
     * @param string $message
     * @param bool   $encode
     */
    public function addMsgData($message, $encode = FALSE) {
        $this->session->setFlashdata('error', createErrorMessage($message, 'info', $encode));
    }
    
    /**
     * Trigger rule 
     * 
     * @param string $trigger
     * @param array  $args
     */
    function triggerRule(string $trigger,array $args)
    {
        $this->model_Tasks_Rule->actionRuleByTrigger($trigger,$args,$this);
    }
    
    /**
     * Add movement item to audit table
     * 
     * @param  int 	  $mhtype
     * @param  string $mhfrom
     * @param  string $mhto
     * @param  string $mhref
     * @param  string $mhuser
     * @param  string $mhinfo
     * @param  string $mhdate
     * 
     * @return bool
     */
    function addMovementHistory($mhtype, $mhfrom, $mhto, $mhref, $mhinfo = null, $type = null, $user = null) {
        if ($mhtype == MovementType::status) {
            $pallet_types = $this->model_Settings->get('pallets.pallet_types', TRUE);
            if (array_key_exists($mhfrom, $pallet_types)) {
                $mhfrom = $pallet_types[$mhfrom];
            }
            if (array_key_exists($mhto, $pallet_types)) {
                $mhto = $pallet_types[$mhto];
            }
        }
        $user = $user == null ? loged_user('username') : $user;
        $filters = ['paramsgroups' => 'movement_types', '( param' => 'movement_type_' . $mhtype, '|| tooltip )' => $mhtype];
        $mhtype = $this->model_Settings->filtered($filters)->first();
        $type = $type == null ? strtolower(Str::afterLast(get_class($this), '\\')) : $type;
        if (is_array($mhtype) && array_key_exists('param', $mhtype) && Str::contains($mhtype['param'], '_')) {
            $mhtype = Str::afterLast($mhtype['param'], '_');
            return $this->model_Movements->addItem($mhtype, $user, $mhfrom, $mhto, $mhref, $mhinfo, null, $type);
        }
        return FALSE;
    }
    
    /**
     * Determines if application is open on mobile device
     * 
     * @return bool
     */
    function isMobile() 
    {
        return html_isMobile();
    }

    function __get($param) {
        if (Str::startsWith($param, 'model_')) {
            $param = Str::afterLast($param, 'model_');
            if (array_key_exists(strtolower($param), $this->assocModels)) {
                $param = $this->assocModels[strtolower($param)];
                $param = model(Str::endsWith($param, 'Model') ? $param : $param . 'Model');
            } else {
                $param = str_replace('_', ' ', $param);
                $param = ucwords($param);
                $param = explode(' ', $param);
                if (count($param) < 2) {
                    $param[] = $param[0];
                }
                $param[1] = $param[1] . 'Model';
                $param = model(implode('/', $param));
            }

            if (is_subclass_of($param, '\CodeIgniter\Model')) {
                return $param;
            }
            return null;
        }
    }

    /**
     * Return access level for current module for given access name
     * 
     * @param  string $name           Access level name
     * @param  bool   $checkLogedUser Determine if returned access level will be check against loged user access
     * @return Int
     */
    protected function getModuleAccessLevel_todelete($name, $checkLogedUser = FALSE) 
    {
        $controller = str_replace(['VCMS\\Controllers\\', 'Controller', 'Admin'], '', get_class($this));
        $controller = $this->model_Settings_Modules->where('modclass', $controller)->first();
        if (is_array($controller) && array_key_exists($name, $controller)) {
            return $checkLogedUser ? $controller[$name] <= $this->auth->getLogedUserInfo('access') : $controller[$name];
        }
        return !$checkLogedUser ? 9999 : false;
    }
    
    /**
     * Determines if user have access to given module access level
     * 
     * @param type $access
     * @return boolean
     */
    public function hasAccess($access)
    {
        if (!is_string($access))
        {
            return FALSE;
        }
        
        if (Str::startsWith($access, '#'))
        {
            return  $this->auth->hasAccess($access);
        }
        
        if (!array_key_exists(strtolower($access), AccessLevel::Levels))
        {
            return FALSE;
        }
        $access=$this->getModuleAccess($access);
        return  $this->auth->hasAccess($access);
    }
    
    
    /**
     * Returns access error view
     * 
     * @param  bool $redirect
     * 
     * @return mixed
     */
    protected function getAccessError($redirect=FALSE)
    {
        return $this->getError('system.errors.no_acces', $redirect);
    }
    
   /**
    * Returns page not found error view
    * 
    * @param bool $justView
    */
    function getNotFoundError(bool $justView=FALSE)
    {
        $this->view->setFile('errors/html/error_404')->addBreadcrumb('system.general.error','/');
        return $justView ? view($this->view->getFile(),$this->view->getViewData()) : $this->view->render();
    }    
    
    /**
     * Returns error view
     * 
     * @param string $body
     * @param bool   $redirect
     * @param bool   $justView
     * 
     * @return mixed
     */
    protected function getError($body,bool $redirect=FALSE,bool $justView=FALSE)
    {
        if ($redirect!=FALSE)
        {
            return redirect()->to(is_bool($redirect)? $this->getRefUrl():$redirect)->with('error',$this->createMessage($body,'danger'));
        }
        $this->view->setFile('errors/html/exception')
                    ->addData('msg', lang($body))
                    ->addData('type', 'danger');
        return $justView ? view($this->view->getFile(),$this->view->getViewData()) : $this->view->render();
    }
    
    /**
     * Returns/Render no page (404) error
     * 
     * @param string $errorMsg
     * @param bool   $redirect
     * 
     * @return mixed
     */
    function getNoPageError(string $errorMsg=null,bool $redirect=FALSE)
    {
        $errorMsg=$errorMsg==null ? 'system.errors.nopagefound_h' : $aa;
        if ($redirect!=FALSE)
        {
            return redirect()->to(is_bool($redirect)? $this->getRefUrl():$redirect)->with('error',$this->createMessage($errorMsg,'danger'));
        }
        return $this->view->setFile('errors/html/exception')
                    ->addData('msg', lang($errorMsg))
                    ->addData('type', 'danger')
                    ->render($this->auth->isLoged() ? 'html' : 'plainhtml');
    }
    
    /**
     * Returns reference url
     * 
     * @param  string $defUrl
     * @param  bool   $decode
     * 
     * @return string
     */
    public function getRefUrl($defUrl = '@<', $decode = TRUE, $checkPost = FALSE) {
        $defUrl = $defUrl == '@<' ? ($decode ? base64url_encode(previous_url()) : previous_url()) : $defUrl;
        $defUrl = $defUrl == '@' ? current_url(FALSE,$decode) : $defUrl;
        $refurl = $this->request->getGet('refurl');
        if ($checkPost) {
            $refurl = $refurl == null ? $this->request->getPost('refurl') : $refurl;
        }

        $refurl = $refurl == null ? $defUrl : $refurl;
        return $decode ? base64url_decode($refurl) : $refurl;
    }
    
    /**
     * Returns url string to module settings page
     * 
     * @param string $tabName
     * 
     * @return string|null
     */
    function getModuleSettingsUrl(string $tabName='cfg')
    {
        $name= strtolower(Str::afterLast(get_class($this),'\\'));
        $name=$this->model_Modules->where('mname',$name)->first();
        if (is_array($name) && Arr::KeysExists(['mid','cfgmth'], $name))
        {
            $args=['refurl'=>current_url(FALSE,TRUE)];
            $name['cfgmth']= json_decode($name['cfgmth'],TRUE);
            if (is_array($name['cfgmth']) && array_key_exists($tabName, $name['cfgmth']))
            {
                $args['tab']=$tabName;
            }
            
            return url('Settings','modules',[$name['mid']],$args);
        }
        return null;
    }
    
    function sendNotification(string $template,array $data,$to=null)
    {
        
       if (is_string($to) && Str::contains($to, ';'))
        {
            $to=explode(';',$to);
        }
        
        if ($to==null)
        {
           $to=$this->model_Auth_User->getForForm('email','email',FALSE,null,FALSE,['iscustomer'=>0]);
        }
        
        foreach(loged_user() as $key=>$value)
        {
            if (is_string($value))
            {
              $data['loged_'.$key]=$value;  
            }
            
        }
        $to=is_array($to) ? $to : [$to];
        foreach($to as $key=>$value)
        {
            if (Str::startsWith($value,'#'))
            {
                $to[$key]=$this->model_Auth_UserGroup->getUserEmailsForGroup(substr($value,1));
                if (is_array($to[$key]) && count($to[$key]) > 0)
                {
                    $to=$to+$to[$key];
                    unset($to[$key]);
                }
            }
        }
        $to= array_values($to);
        $to=['artur@apdcw.co.uk'];
        $data=$this->model_Documents_Report->parseEmailTemplate($template,$data);
        if (is_array($data) && Arr::KeysExists(['subject','body','mailbox'], $data));
        {
            if (ENVIRONMENT=='')
            {
                $data['subject'].=' - TEST';
                $data['body']='<h1 style="color:#ff0000;font-weight: bold;">THIS IS TEST EMAIL PLEASE IGNORE IT</h1>';
            }
            //$this->addMovementHistory('notify', null, null, implode(';',$to), $data['subject'], 'notification');
            $mailbox=$this->model_Emails_Mailbox->getMailbox($data['mailbox']);
            $mailbox=$mailbox->sendEmail($to,$data['subject'],$data['body'], [],[],null);
        }
    }
    
    /**
     * Sends email message
     * 
     * @param  string  $fromEmail
     * @param  string  $fromName
     * @param  string  $to
     * @param  string  $subject
     * @param  string  $msg
     * @param  array   $cc
     * @param  array   $bcc
     * @param  bool    $debug
     * 
     * @return mixed
     */
    protected function sendEmail($fromName, $to, $subject, $msg, array $cc = [], array $bcc = [], array $attachements=[],$debug = FALSE) 
    {
        if (is_string($fromName) && Str::startsWith($fromName,'mailbox:'))
        {
            $fromName=Str::afterLast($fromName, ':');
            $mailbox=$this->model_Emails_Mailbox->getMailbox($fromName);
            $fromName=$mailbox->Name;
        }else
        {
            $mailbox=$this->model_Emails_Mailbox->getMailbox();
        }
        return $mailbox->sendEmail($to,$subject,$msg,$cc,$bcc,$fromName, $attachements); 
    }
    
    function modulesettings($tab='general')
    {
        $ref=$this->getRefUrl(null,FALSE);
        $name=get_class($this);
        if (Str::contains($name, '\\'))
        {
            $name=Str::afterLast($name, '\\');
        }
        return redirect()->to(url('Settings','modules',[strtolower($name)],['refurl'=>$ref,'tab'=>$tab]));
    }
    
    /**
     * Returns parsed email/document template
     * 
     * @param  string $name
     * @param  array  $data
     * @param  bool   $fullpage
     * @param  bool   $autoprint
     * @return type
     */
    function getParsedTemplate($name,array $data=[],$fullpage=FALSE,$autoprint=FALSE)
    {
        return loadModule('Reports','parsetemplate',[$name,$data,$fullpage,$autoprint]);
    }
    
    /**
     * Returns array with module reports sources
     * 
     * @return array
     */
    function getReportSources(array $viewNames=[])
    {
        $moduleName=$this->getModuleName();
        $views=$this->model_modules->getViewsForModule($moduleName);
        $arr=[];
        foreach ($views as $view)
        {
            $name= str_replace('vw_'.$moduleName, '', $view);
            $name=Str::startsWith($name, '_') ? substr($name,1) : $name;
            if (count($viewNames) < 1 || (in_array($name, $viewNames) || in_array($view, $viewNames)))
            {
                $arr[$name]=
                [
                    'title'=>$moduleName.'.report_'.$name,
                    'columns'=>[],
                    'filters'=>[]
                ];
                foreach($this->model_Modules->getView($view)->allowedFields as $column)
                {
                    $arr[$name]['columns'][$column]=$moduleName.'.report_'.$column;
                }
            }
        }
        return $arr;
    }

    
    /**
     * Returns menu items form/array with items names
     * 
     * @param  mixed $value
     * @param  bool  $justItems
     * @return mixed
     */
    protected  function getMenuItemsData($value=null,$justItems=FALSE)
    {
        if ($justItems)
        {
            if (Arr::getType($this->availablemenuitems)=='INDX')
            {
                return array_flip($this->availablemenuitems);
            }
            return $this->availablemenuitems;
        }
        $form=new Pages\FormView($this);
        $form->addDropDownField('attedance.menu_action', 'wizard_route[action]',$this->availablemenuitems, $value);
        return view('System/form_fields',$form->getViewData());
    }
    
    protected function list_records($record=null,array $args=[])
    {
        if ($record!=null)
        {
            if (!array_key_exists('edit', $args))
            {
                $args['edit']=['method'=>'edit_record'];
            }else
            {
                if (is_array($args['edit']) && !array_key_exists('method', $args['edit']))
                {
                    $args['edit']['method']='edit_record';
                }else
                if (is_string($args['edit']))
                {
                    $args['edit']['method']=$args['edit'];
                }else
                {
                    throw new \Exception('Edit method variable not exists in arguments array');
                }
            }
            
            return $this->{$args['edit']['method']}($record,$args['edit']);
        }
        
        if (!array_key_exists('model', $args))
        {
            throw new \Exception('Model variable not exists in arguments array');
        }
        
        if (!array_key_exists('method', $args))
        {
            throw new \Exception('Method variable not exists in arguments array');
        }
        
        if (!array_key_exists('title', $args))
        {
            throw new \Exception('Title variable not exists in arguments array');
        }
        
        if (!array_key_exists('columns', $args))
        {
            throw new \Exception('Columns variable not exists in arguments array');
        }
        
        if (!array_key_exists('view', $args))
        {
            $args['view']='System/table';
        }
        
        $this->setTableView($args['view'])
                    ->setData($args['model'],null,TRUE,null,[])
                    ->setPageTitle($args['title'])
                    //Table Riows buttons
                    ->addEditButton('system.buttons.edit_details',$args['method'],null,'btn-primary edtBtn','fa fa-edit',[]);
        //Fiilters settings
        if (array_key_exists('filters', $args) && is_array($args['filters']) && array_key_exists('method', $args['filters']))
        {
            $this->view->addFilters($args['filters']['method']);
            unset($args['filters']['method']);
            foreach($args['filters'] as $value)
            {
                $this->view->addFilterField($value);
            }
        }
        
        //Table main buttons
        if (array_key_exists('enableButton', $args))
        {
            if ($args['enableButton']!=FALSE)
            {
                $this->view->addEnableButton($args['enableButton']==TRUE ? AccessLevel::edit : $args['enableButton']);
            }
        }else
        {
            $this->view->addEnableButton(AccessLevel::edit);
        }
        
        if (array_key_exists('disableButton', $args))
        {
            if ($args['disableButton']!=FALSE)
            {
                $this->view->addEnableButton($args['disableButton']==TRUE ? AccessLevel::edit : $args['disableButton']);
            }
        }else
        {
            $this->view->addDisableButton(AccessLevel::edit);
        }
        
        if (array_key_exists('deleteButton', $args))
        {
            if ($args['deleteButton']!=FALSE)
            {
                $this->view->addEnableButton($args['deleteButton']==TRUE ? AccessLevel::edit : $args['deleteButton']);
            }
        }else
        {
            $this->view->addDeleteButton(AccessLevel::edit);
        }
        $this->view->addNewButton($args['method'].'/new');
        
        //Table Columns settings
        foreach($args['columns'] as $value)
        {
            if (is_array($value) && count($value) > 1)
            {
                $this->view->addColumn($value[0],$value[1],count($value) > 2 ? $value[2] : FALSE,count($value) > 3 ? $value[3] : [],count($value) > 4 ? $value[4] : null);
            }
        }
        
        //Breadcrumb settings
        if (array_key_exists('breadcrumbs', $args))
        {
            $this->view->addBreadcrumbs($args['breadcrumbs']);
        }
        
        return $this->view->render();
    }
    
    protected function edit_record($record,array $args=[])
    {
        if (!array_key_exists('model', $args))
        {
            throw new \Exception('No model variable in arguments array');
        }
        
        if (!array_key_exists('title', $args))
        {
            throw new \Exception('No title variable in arguments array');
        }
        
        if (!array_key_exists('view', $args))
        {
            $args['view']='System/form';
        }
        
        $refurl=$this->getRefUrl(null);
        $isnew=FALSE;
        $model='model_'.$args['model'];
        if (is_numeric($record))
        {
            $record=$this->{$model}->find($record);exit;              
        }else
        {
            $record=null;
        }
           
        $record=$this->getFlashData('_postdata',$record);
        if ($record==null || $record=='new')
        {
            if (!$this->hasAccess(AccessLevel::create))
            {
                return $this->getAccessError(true);
            }
            $isnew=TRUE;
            $record=$this->{$model}->getNewRecordData(TRUE);
        }
        $record['edit_acc']=$this->hasAccess(AccessLevel::edit);
        $this->setFormView($args['view'])
                ->setFormTitle($args['title'])
		->setPageTitle($args['title'])
		->setFormAction($this,'save',[$model],['refurl'=>base64url_encode($refurl)])
		->setFormArgs(['autocomplete'=>'off'],
                        [
                            $this->{$model}->primaryKey=>$record[$this->{$model}->primaryKey],
                        ]
                ,['class'=>'col-12'])
		->setCustomViewEnable(FALSE)
		->setFormCancelUrl($refurl)	
		->addData('record',$record);
                            
            if (array_key_exists('breadcrumbs', $args))
            {
                $this->view->addBreadcrumbs($args['breadcrumbs']);
            }else
            {
                $this->view->addBreadcrumbsFromPage();
            }
            
            if (array_key_exists('nameField', $args) && array_key_exists($args['nameField'], $record))
            {
                $this->view->addBreadcrumb($isnew ? 'system.buttons.new' : $record[$args['nameField']],'/');
            }
            if (array_key_exists('tabs', $args) && is_array($args['tabs']))
            {
                foreach($args['tabs'] as $key=>$value)
                {
                    if ($value=='general')
                    {
                       $this->view->setTab('general','system.general.tab_info'); 
                    }else
                    {
                        $this->view->setTab($key,$value);
                    }
                }
            }else
            {
                $this->view->setTab('general','system.general.tab_info');
            }
            if (array_key_exists('fieldsTooltipPatern', $args))
            {
                $this->view->addFieldsFromModel($args['model'],$record,$args['fieldsTooltipPatern']);
            }
            
            
            
            if (array_key_exists('options', $args) && is_array($args['options']))
            {
                foreach($args['options'] as $key=>$value)
                {
                    $key= is_string($key) ? $key : $value;
                    if (method_exists($this->view, $key))
                    {
                        if (is_array($value))
                        {
                            call_user_func_array([$this->view,$key], $value);
                        }else
                        {
                            $this->view->{$key}();
                        }
                    }
                }
            }
            return $this->view->render();
    }
}
