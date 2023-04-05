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

  
namespace EMPORIKO\Controllers;

use \EMPORIKO\Helpers\AccessLevel;
use \EMPORIKO\Helpers\Arrays as Arr;
use \EMPORIKO\Helpers\Strings as Str;

class Api extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
            'post'=>             AccessLevel::view,
	];
        
        protected $no_access=['api','index','post','get'];
        
        /**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
	];
	
        /**
	 * Array with controller method remaps ($key is fake function name and $value is actual function name)
	 */
	public $remaps=
        [
            'put'=>'post',
        ];
        
        /**
	 * Array with function names which are excluded from routes actions
	 * @var Array
	 */
	protected $routerexlude=[''];
        
        private $_apiauth=FALSE;
        
        
        public function getConfig($name='default')
        {
            $baseConfig=config('VRM');
            $name=$name=='default' ? $baseConfig->defaultGroup : $name;
            $baseConfig=$baseConfig->{$name};
            if (is_array($baseConfig))
            {
                $baseConfig=Arr::toObject($baseConfig);
                $baseConfig->fields=json_decode(json_encode($baseConfig->fields),TRUE);
                $baseConfig->service_headers=json_decode(json_encode($baseConfig->service_headers),TRUE);
            }
            return $baseConfig;
        }
        
        /**
        * Send request to service
        * 
        * @param  array $data
        * @return array
        */
        public function callService($data=[],array $fields=[],$jsonEncode=TRUE)
        {
            if (is_array($data) && count($data) < 1)
            {
                error:
                return json_encode(['error'=>'invalid input data']);
            }
            
            if (is_array($data) && array_key_exists('cfg', $data))
            {
                $data=$this->getConfig($data['cfg']);
            }
            
            if (is_array($data))
            {
                $data=Arr::toObject($data);
            }
            
            if (gettype($data)!='object')
            {
                goto error;
            }
            
            if (!Arr::KeysExists(['service_response','service_callmethod','service_response','service_fetchmethod','url','post'], json_decode(json_encode($data),TRUE)))
            {
                return json_encode(['error'=>'invalid input data structure']);
            }
            
            if (gettype($data)=='object' && !is_array($data->post) && count($data->post) < 0)
            {
                goto error;
            }
            
            if (property_exists($data, 'username_fieldname')  && $data->username_fieldname!=null && strlen($data->username_fieldname) >0)
            {
                if (property_exists($data, 'header_credentials') && $data->header_credentials)
               {
                   $data->service_headers[$data->username_fieldname]=$data->service_username;
               }else
               {
                    $data->post[$data->username_fieldname]=$data->service_username;
               }
            }
            if (property_exists($data, 'pass_fieldname') && $data->pass_fieldname!=null && strlen($data->pass_fieldname) >0)
            {
               if (property_exists($data, 'header_credentials') && $data->header_credentials)
               {
                   $data->service_headers[$data->pass_fieldname]=$data->service_pass;
               }else
               {
                   $data->post[$data->pass_fieldname]=$data->service_pass;
               }
                
            }
            
            $options = 
            [
                'http' =>
                [
                    'header'  => $data->service_response=='xml' ? "Content-type:application/x-www-form-urlencoded\r\n": "Content-type:application/json",//,
                    'method'  => $data->service_callmethod,
                    'content' => $data->service_response=='xml' ? http_build_query($data->post) : json_encode($data->post) //
                ]
            ];
            if ($data->service_fetchmethod=='curl')
            {
                $options['http']['header']= is_array($options['http']['header']) ? $options['http']['header'] : [$options['http']['header']];   
            }
            
            foreach ($data->service_headers as $key=>$value)
            {
                if ($data->service_fetchmethod=='curl')
                {
                    $options['http']['header'][]=$key.':'.$value;
                }else
                {
                  $options['http']['header'].=$key.':'.$value;  
                }
                 
            }
            if ($data->service_fetchmethod=='curl')
            {
                $curl = curl_init();
                
                curl_setopt_array($curl,
                [
                    CURLOPT_URL => $data->url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => $data->service_callmethod,
                    CURLOPT_POSTFIELDS => $options['http']['content'],
                    CURLOPT_HTTPHEADER => $options['http']['header']
                ]);
                $result = curl_exec($curl);
                curl_close($curl);
            } else 
            {
                $context  = stream_context_create($options);
                $result = file_get_contents($data->url, false, $context);
            }
            
            if ($data->service_response=='xml')
            {
                if (!Str::contains($result, 'xmlns="http'))
                {
                    $result= str_replace('xmlns="', 'xmlns="http://', $result);
                }
                
                $xml = simplexml_load_string($result);
                $result = json_encode($xml);
            }
            
            if (!Str::isJson($result))
            {
                return json_encode(['error'=>'Invalid response']);
            }
            
            if (count($fields) > 1)
            {
                $result= json_decode($result,TRUE);
                foreach($fields as $key=>$value)
                {
                    if (is_array($result) && array_key_exists($value, $result))
                    {
                       $result[$key]=$result[$value];
                       unset($result[$value]);
                    }
                }
            }else
            {
                $result= json_decode($result,TRUE);
            }
            
            return $jsonEncode ? json_encode($result) : $result;
    }
    
    
    function _remap($method, ...$params) 
    {
        if (!$this->auth->isLoged())
        {
            $auth=loged_user('auth')->setAuthenticator('token')->trylogin(['key'=>'@']);
            if (!$auth->isOK())
            {
                return $this->compile(['error'=>$auth->reason()]);
            }
        }
        
        if (in_array($method, $this->no_access))
        {
            if (count($params) < 2)
            {
                return $this->error_invalid_command();
            }
            $controller=$params[0];
            $action=$params[1];
        }else
        {
            if (count($params) < 1)
            {
                return $this->error_invalid_command();
            }
            $controller=ucwords($method);
            $action=$params[0];
        }
        $content=$this->getInput();
        $content=['controller'=> ucwords($method),'action'=>'api','args'=>[$action,$content]];
        $return= loadModuleFromArray($content);
        return $this->compile($return);
    }
    
    private function error_invalid_command()
    {
        return $this->compile(['error'=>lang('system.api.error_api_no_comm')]);
    }
    
    private function compile($data,bool $echo=FALSE)
    {
        if ($data==null)
        {
            return $this->error_invalid_command();
        }
        if ($this->_apiauth)
        {
           $this->auth->logout(); 
        }
        if ($echo)
        {
            echo json_encode($data);
        }
        return $this->response->setJSON($data);
    }
    
    private function getInput()
    {
        $content= file_get_contents('php://input');
        $content= is_array($content) ? $content : (Str::isJson($content) ? json_decode($content,TRUE) : []);
        $content= is_array($content) ? $content : [];
        $content=$content+$this->request->getPost()+$this->request->getGet();
        return $content;
    }
    
    
    
    function _remap1($method, ...$params) 
    {
        $http_method=$this->request->getMethod();
        if ($method=='index')
        {
            $method=$http_method;
        }
        
        $content= file_get_contents('php://input');
        $content= is_array($content) ? $content : (Str::isJson($content) ? json_decode($content,TRUE) : []);
        $content= is_array($content) ? $content : [];
        $content=$content+$this->request->getPost()+$this->request->getGet();
        
        if (count($params) < 1)
        {
            return $this->error_invalid_command();
        }
        
        if ($this->auth->isLoged() == FALSE)
        {
            if (!array_key_exists('auth', $content))
            {
                error_auth:
                return $this->compile(['error'=>lang('system.api.error_api_auth')]);
            }
            
            $content['auth']=$this->model_Auth_User->filtered(['apitoken'=>$content['auth']])->first();
            If (!is_array($content['auth']) || (is_array($content['auth']) && !array_key_exists('username', $content['auth'])))
            {
                goto error_auth;
            }
            $content['auth']=$this->model_Auth_User->getUserData($content['auth']['username'],null,$content['auth']['username']);
            $this->_apiauth=TRUE;
            $this->auth->createSessionToken($content['auth']);
        }
        unset($content['auth']);
        $action=$params[0];
        unset($params[0]);
        $content=$params+$content;
        $content=[strtolower($action),$content];
        $content=['controller'=> ucwords($method),'action'=>'api','args'=>$content];
        $return= loadModuleFromArray($content);
        
        return $this->compile($return);
    }
}