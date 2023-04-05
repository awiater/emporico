<?php

use EMPORIKO\Helpers\Strings as Str;
use EMPORIKO\Helpers\Arrays as Arr;

use Config\Services;
use CodeIgniter\I18n\Time;

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

function auth(string $provider = null)
{
    $providers=config('App')->authProviders;
    if (array_key_exists($provider, $providers))
    {
        return call_user_func($providers[$provider]);
    }else
    {
       return service('auth');
    }
}
    
function storage(string $engine=null)
{
    $engine= $engine==null ? config('APP')->storageEngine : $engine;
    if (strtolower($engine)=='onedrive')
    {
        $engine='\OneDrive\StorageEngine';
    }
    if (!class_exists($engine))
    {
        $engine=config('APP')->storageEngine ;
    }
    return new $engine();
}

/**
 * Map given dir and filter out files which not match filter
 * 
 * @param string $sourceDir
 * @param array $filters
 * 
 * @return array
 */
function mapDir(string $sourceDir,array $filters=[])
{
        $path=parsePath($sourceDir,TRUE);
        $fp = opendir($path);
        $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $path=rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $fileData  = [];
        $hidden=FALSE;
        while (false !== ($file = readdir($fp)))
        {
            if ($file === '.' || $file === '..' || ($hidden === false && $file[0] === '.'))
            {
                continue;
            }
            $file=['path'=>$sourceDir.$file,'type'=>'file','file'=>$file,'realpath'=>$path . $file,'url'=>parsePath($sourceDir.$file)];
            if (is_dir($file['realpath']))
            {
                $file['type']='dir';
                array_unshift($fileData,$file);
            } else 
            {
                $file['ext']=Str::afterLast($file['realpath'], '.');
                if (count($filters) < 1)
                {
                    $fileData[] = $file;
                }else
                if (Str::contains($file['realpath'], $filters))
                {
                    $fileData[] = $file;
                }
                
            }
        }
        array_sort_by_multiple_keys($fileData,['type'=>SORT_ASC,'file'=>SORT_ASC]);
        return $fileData;
 }

/**
 * Small button
 * 
 * @param string $id
 * @param string $color
 * @param string $icon
 * @param mixed  $tooltip
 * @param array  $args
 * 
 * @return type
 */
function html_button(string $id,string $color,$icon=null,$tooltip=null,array $args=[])
{
    if ($color=='new')
    {
        $color='dark';
        $icon=$icon==null ? 'fa fa-fa fa-plus':$icon;
        $tooltip=$tooltip==null ? 'system.buttons.new' : $tooltip;
    }
    
    if ($color=='edit')
    {
        $color='primary';
        $icon=$icon==null ? 'fa fa-edit':$icon;
        $tooltip=$tooltip==null ? 'system.buttons.edit_details' : $tooltip;
    }
    
    if ($color=='del' || $color=='removes')
    {
        $color='danger';
        $icon=$icon==null ? 'far fa-trash-alt':$icon;
        $tooltip=$tooltip==null ? 'system.buttons.remove' : $tooltip;
    }
    $color= str_replace(['new','edit','del'], ['dark','primary','danger'], $color);
    return \EMPORIKO\Controllers\Pages\HtmlItems\ToolbarButton::create($icon,$color,$tooltip,$id,null,$args)->render();
}

/**
 * Determines if current browser view port is mobile (open in mobile browser)
 * 
 * @return bool
 */
function html_isMobile()
{
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/**
 * Drop-down Menu
 * 
 * @param mixed $data
 * @param mixed $options
 * @param mixed $selected
 * @param mixed $extra
 * 
 * @return string
 */
function form_dropdown($data = '', $options = [], $selected = [], $extra = ''): string
{
		$defaults = [];
		if (is_array($data))
		{
			if (isset($data['selected']))
			{
				$selected = $data['selected'];
				unset($data['selected']); // select tags don't have a selected attribute
			}
			if (isset($data['options']))
			{
				$options = $data['options'];
				unset($data['options']); // select tags don't use an options attribute
			}
		}
		else
		{
			$defaults = ['name' => $data];
		}

		is_array($selected) || $selected = [$selected]; // @phpstan-ignore-line

		is_array($options) || $options = [$options]; // @phpstan-ignore-line

		// If no selected state was submitted we will attempt to set it automatically
		if (empty($selected))
		{
			if (is_array($data))
			{
				if (isset($data['name'], $_POST[$data['name']]))
				{
					$selected = [$_POST[$data['name']]];
				}
			}
			elseif (isset($_POST[$data]))
			{
				$selected = [$_POST[$data]];
			}
		}

		$extra    = stringify_attributes($extra);
		$multiple = (count($selected) > 1 && stripos($extra, 'multiple') === false) ? ' multiple="multiple"' : '';
		$form     = '<select ' . rtrim(parse_form_attributes($data, $defaults)) . $extra . $multiple . ">\n";
		foreach ($options as $key => $val)
		{
			$key = (string) $key;
			if (is_array($val))
			{
				if (empty($val))
				{
					continue;
				}
				$form .= '<optgroup label="' . $key . "\">\n";
				foreach ($val as $optgroupKey => $optgroupVal)
				{
					$sel   = in_array($optgroupKey, $selected, true) ? ' selected="selected"' : '';
					$form .= '<option value="' . htmlspecialchars($optgroupKey) . '"' . $sel . '>'
							. lang($optgroupVal)  . "</option>\n";
				}
				$form .= "</optgroup>\n";
			}
			else
			{
				$form .= '<option value="' . htmlspecialchars($key) . '"'
						. (in_array($key, $selected, true) ? ' selected="selected"' : '') . '>'
						. lang($val) . "</option>\n";
			}
		}

		return $form . "</select>\n";
}

/**
 * Return data upload form
 * 
 * @param string $driver
 * @param string $title
 * @param array  $args
 * 
 * @return string
 */
function form_dataupload($driver,$title,array $args=[])
{
    $data=[];
    $url=url('Settings','uploaddata',[$driver],['refurl'=> current_url(FALSE,TRUE)]);
    $data['upload_url']=$url;
    $data['form_id']='id_data_upload_form';
    $data['modal_id']='id_data_upload_modal';
    $data['use_modal']=TRUE;
    $data['title']=$title==null ? 'system.settings.upload_modal_title' : $title;
    $data['label']=lang('system.settings.upload_data_label',[url_tag($url, '<i class="fas fa-file-csv"></i>',['class'=>'p-0'])]);
    $data['input_name']='data_upload_file';
    $data['input_id']='data_upload_file';
    $data['input_format']='.csv';
    $data['input_field_id']='data_upload_text';
    $data['button_id']=null;
    
    foreach($args as $key=>$arg)
    {
        if (array_key_exists($key, $data))
        {
            $data[$key]=$arg;
        }
    }
    return view('System/data_upload',$data);
}

/**
 * Delete dir with all it content
 * 
 * @param string $dirPath
 * 
 * @throws InvalidArgumentException
 */
function deleteDir($dirPath) 
{
    if (! is_dir($dirPath)) {
        throw new InvalidArgumentException("$dirPath must be a directory");
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
        $dirPath .= '/';
    }
    $files = glob($dirPath . '*', GLOB_MARK);
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDir($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}

  
/**
 * A convenience method to translate a string or array of them and format
 * the result with the intl extension's MessageFormatter.
 *
 * @param string      $line
 * @param array       $args
 * @param string|null $locale
 *
 * @return string
 */
function lang($line, array $args = [], string $locale = null)
{
    if ($line==null)
    {
        return $line;
    }
    if (preg_match('#\{\{(.*?)\}\}#', $line, $match) && count($match) > 1)
    {
        $match[1]=explode(':',$match[1]);
        $line= str_replace($match[0], call_user_func_array($match[1][0], explode(',',$match[1][1])), $line);
    }
    
    return config('APP')->parseLngVars ? Services::language($locale)->getLine($line, $args) : $line;
}

/**
	 * Grabs the current RendererInterface-compatible class
	 * and tells it to render the specified view. Simply provides
	 * a convenience method that can be used in Controllers,
	 * libraries, and routed closures.
	 *
	 * NOTE: Does not provide any escaping of the data, so that must
	 * all be handled manually by the developer.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param array  $options Unused - reserved for third-party extensions.
	 *
	 * @return string
	 */
	function view(string $name, array $data = [], array $options = [])
	{
		/**
		 * @var CodeIgniter\View\View $renderer
		 */
		$renderer = Services::renderer();
                  
		$saveData = config(View::class)->saveData;

		if (array_key_exists('saveData', $options))
		{
			$saveData = (bool) $options['saveData'];
			unset($options['saveData']);
		}
		
		if (Str::startsWith($name,'@'))
		{
			$name=parsePath($name);
		}
		
		if (Str::startsWith($name,'#'))
		{
			$name=substr($name, 1);
			return $renderer->setData($data, 'raw')
						->renderString($name, $options, $saveData);
		}
		
		return $renderer->setData($data, 'raw')
						->render($name, $options, $saveData);
	}
    
function url_from_route(string $route,array $replace=[],array $args=[])
{
    $route= str_replace(Arr::ParsePatern(array_keys($replace), '-value-'), array_values($replace), $route);
    return url($route,null,[],$args);
}        
/**
 * Create url from array
 * 
 * @param  array $arr
 * @return string
 */ 
function url_from_array(array $arr)
{
	if (!array_key_exists('controller', $arr))
	{
		return null;
	}
	$controller=$arr['controller'];
	$action=array_key_exists('action', $arr)?$arr['action']:null;
	$params=array_key_exists('args', $arr)?$arr['args']:[];
	return url($controller,$action,$params);
}

/**
 * Create ticket support link
 * 
 * @param string $taskID
 * @param string $text
 * @param string $mode
 * 
 * @return string
 */
function support_link($taskID,$text=null,string $mode='emailform')
{
    $text=$text==null ? lang('system.buttons.contact_us') : $text;
    if ($mode=='ticket')
    {
        $taskID=url('Tickets','cases',['new'],['tpl'=>$taskID]);
    } else 
    {
        $taskID=url('Emails','compose',['systemsupport'],['error'=> base64url_encode($taskID)]);
    }
    return url_tag($taskID,$text,['class'=>'p-0']);
}

/**
 * Create url from string
 * 
 * @param string $url
 * 
 * @return string|null
 */
function url_from_string(string $url)
{
    if (!Str::contains($url, '::'))
    {
        return null;
    }
    $args=[];
    if (Str::contains($url,'@'))
    {
        $url=explode('@',$url);
        $args=explode(',',$url[1]);
        $url=$url[0];
    }
    $url=explode('::',$url);
    return url($url[0], $url[1], $args);
}

/**
 * Generate link to customer portal page
 * 
 * @param string $name
 * @param array $get
 * 
 * @return string
 */
function page(string $name,array $get=[])
{
    $name= strtolower($name);
    if (!Str::endsWith($name, '.html'))
    {
        $name.='.html';
    }
    return url('portal',$name,[],$get);
}

/**
 * Creates url to call internal api
 * 
 * @param type $controller
 * @param string $method
 * 
 * @return string
 */
function api_url(string $controller,string $method)
{
    return url('Api',$controller,[$method]);
}

/**
 * Create url
 * 
 * @param  string $controller
 * @param  string $action
 * @param  array  $params
 * @param  array  $get
 * @param  bool   $encode
 * @return string
 */
function url($controller,$action=null,array $params=[],array $get=[],$encode=FALSE)
{
		
		if (!is_string($controller) && is_object($controller))
		{
			$controller=get_class($controller);
		}else
		if ($controller==null)
		{
			return null;
		}else
		if ($controller=='$')
		{
			$controller=current_url();
			goto generate_params;
		}else
		if ($controller=='<')
		{
			$controller=previous_url();
			goto generate_params;
		}else
		if ($controller=='@')
		{
			$controller=site_url();
			goto generate_params;
		}else
		if (Str::startsWith(strtolower($controller),'http'))
		{
			goto generate_params;
		}else
		if (Str::startsWith($controller,'/'))
		{
			$controller=site_url($controller);
			goto generate_params;
		}
		
		$controller=str_replace('\\', chr(47), $controller);
		
		if (Str::contains($controller,chr(47)))
		{
			$controller=strtolower(substr(strrchr($controller, chr(47)),1));
		}
		
		if ($action!=null)
		{
			array_unshift($params,$action);
		}
		array_unshift($params,$controller);
		
		$controller=strtolower(site_url($params));
		
		generate_params:
		if ($get!=null && is_array($get) && count($get)>0)
		{
			array_walk($get,function(&$value,$key){$value=$key.'='.$value;});
			$controller.=(Str::contains($controller,'?') ? '&' : '?').implode('&',$get);
		}
		
		return $encode ? base64url_encode($controller) : $controller;
}

function loged_user($field=null)
{
  $auth=auth();
  $user=$auth->user();
  if ($field=='avatar')
  {
      return $user->getAvatar();
  }
  if ($field=='auth')
  {
      return $auth;
  }else
  if ($field=='object')
  {
      return $auth->user();
  }else
  if ($user!=null && method_exists($user, $field))
  {
      return $auth->user()->{$field}();
  }
  if ($user!=null)
  {
      $arr=$user->toPublicFieldsArray();
  } else 
  {
    $arr=null;
  }
  $arr= is_array($arr) ? $arr : null;
  return is_array($arr) && array_key_exists($field, $arr) ? $arr[$field] : $arr;
}


function current_url(bool $returnObject = FALSE,$hashed=FALSE)
{
	$uri=$_SERVER['REQUEST_URI'];
	$baseURL=config('App')->baseURL;
        helper('text');
	if (strpos($uri, $baseURL)===FALSE)
	{
		if ($uri=='/')
		{
			$uri=$baseURL;
		}else
		{
			$uri=$baseURL.$uri;
		}
		
	}
	$uri=reduce_double_slashes($uri);
	if ($returnObject)
	{
		$uri = clone Services::request()->uri;
		$uri= $returnObject ? $uri : (string) $uri->setQuery('');
	}
	return $hashed&&!$returnObject?base64url_encode($uri):$uri;
}


/**
 * Returns a href (url) html tag
 * 
 * @param  String $href  Url path (href)
 * @param  String $text  Url display text
 * @param  String $args  Custom url arguments
 * @return String A href tag (url)
 */
function url_tag($href,$text,array $args=[])
{
	
	$properties=['target','id','class','aria-haspopup','style','title'];
	if (!array_key_exists('class', $args))
	{
		$args['class']='btn btn-link p-0';
	}
	$str='<a';
	if ($href!=null && strlen($href)>0)
	{
		$str.=' href="'.$href.' "';
	}

	foreach ($args as $key => $value) 
	{
		if (in_array($key, $properties) || Str::startsWith($key,'data-'))
		{
			$str.=' '.$key.'="'.$value.'"';
		}
		
	}
	return $str.'>'.lang($text).'</a>';
}

/**
 * Returns FontAwesome tag
 * 
 * @param string $iconName
 * @param array  $args
 * 
 * @return string
 */
function html_fontawesome($iconName,array $args=[])
{
    if (array_key_exists('htmlTagBody', $args))
    {
        $htmlTagBody=$args['htmlTagBody'];
        unset($args['htmlTagBody']);
    }else
    {
        $htmlTagBody=null;
    }
    
    if (array_key_exists('htmlTag', $args))
    {
        $htmlTag=$args['htmlTag'];
        unset($args['htmlTag']);
    }else
    {
        $htmlTag='i';
    }
    
    if (array_key_exists('FaTag', $args))
    {
        $FaTag=$args['FaTag'];
        unset($args['FaTag']);
    }else
    {
        $FaTag='fa fa-';
    }
    
    $iconName=$FaTag.$iconName;
    if (array_key_exists('class', $args))
    {
       $args['class']=$iconName.' '.$args['class']; 
    } else 
    {
        $args['class']=$iconName;
    }
    
    foreach($args as $key=>$value)
    {
        
    }
    $iconName='<'.$htmlTag;
    foreach($args as $key=>$value)
    {
        if (is_string($value))
        {
           $iconName.=' '.$key.'="'.$value.'"'; 
        }
        
    }
    
    return $iconName.'>'.$htmlTagBody.'</'.$htmlTag.'>';
}

/**
 * Url safe base64 string encoding
 * 
 * @param  String $data String to be encoded
 * @return String
 */
function base64url_encode($data)
{
	return Str::base64url_encode($data);
}

/**
 * Url safe base64 string decoding
 * 
 * @param  String $data String to be decoded
 * @return String
 */
function base64url_decode($data)
{
	return Str::base64url_decode($data);
}

/**
 * Returns array with months names
 * 
 * @param  string $start
 * @param  string $valueFormat
 * @return type
 */
function getMonthsList($start='now',$valueFormat=null)
{
    $arr=[];
    $start= formatDate($start,'by');
    for($i=0;$i<12;$i++)
    {
        $date= formatDate($start,'+ '.$i.' month');
        $arr[$valueFormat==null ? $date : convertDate($date, 'DB', $valueFormat)]= convertDate($date, 'DB', 'F');
    }
    return $arr;
}

/**
 * Return formated date string
 * 
 * @param  mixed  $date     Date integer or word now for now time.
 * @param  bool   $targetDB Determine if date will be saved in db
 * @param  string $format   Format which will be used to format date (if not saved to db)
 * 
 */
function formatDate($date='now',$targetDB=TRUE,$format='YmdHi')
{
	if ($date=='now')
	{
		$date=Time::now();
	}
	$format=$format==null ? 'YmdHi' : $format;
	if ((is_array($date) && count($date)==2) && ($targetDB=='diff' || $targetDB=='between' || $targetDB=='<>' || $targetDB=='bet'))
	{
		$date[0]= is_string($date[0]) ? Time::createFromFormat($format,$date[0]) : Time::now();
                $date[1]= is_string($date[1]) ? Time::createFromFormat($format,$date[1]) : Time::now();
		return $date[1]->diff($date[0]);
	}else
	if (is_string($targetDB))
	{
            if (is_array($date))
            {
                $date2=$date[1];
                $date=$date[0]; 
            }    
            if (is_string($date))
            {
                $date=Time::createFromFormat($format,$date);
            }else
            {
                $date=Time::now();
            }
		
		$int=(int)filter_var($targetDB,FILTER_SANITIZE_NUMBER_INT);
		$funct='add';
		$aa=FALSE;
		if (Str::startsWith($targetDB,'-'))
		{
			$int=$int*(-1);
			$aa=TRUE;
			$funct='sub';
		}
                if (strtolower($targetDB)=='endmonth'|| $targetDB=='>' || strtolower($targetDB)=='em')
                {
                    $month=$date->month<10 ? '0'.$date->month:$date->month;
                    $day=\cal_days_in_month(CAL_GREGORIAN, $date->getMonth(), $date->getYear());
                    $day=$day<10?'0'.$day:$day;
                    $date=Time::createFromFormat('YmdHi',$date->year.$month.$day.'2359');
                    return $date->toDateTime()->format($format);
                }else
                if (strtolower($targetDB)=='beginmonth'|| $targetDB=='<' || strtolower($targetDB)=='bm')
                {
                    $month=$date->month<10 ? '0'.$date->month:$date->month;
                    $date=Time::createFromFormat('YmdHi',$date->year.$month.'010000');
                    return $date->toDateTime()->format($format);
                }else
                if (strtolower($targetDB)=='endyear'|| $targetDB=='>>' || strtolower($targetDB)=='ey')
                {
                    $date=Time::createFromFormat('YmdHi',$date->year.'12312359');
                    return $date->toDateTime()->format($format);
                }else
                if (strtolower($targetDB)=='beginyear' || $targetDB=='<<'|| strtolower($targetDB)=='by')
                {
                    $date=Time::createFromFormat('YmdHi',$date->year.'01010000');
                    return $date->toDateTime()->format($format);
                }else    
		if ($targetDB=='dayofWeek')
		{
			return $date->getDayOfWeek();
		}else
		if ($targetDB=='daysInMonth')
		{
			return \cal_days_in_month(CAL_GREGORIAN, $date->getMonth(), $date->getYear());
		}else
                if ($targetDB=='weeksInMonth')
                {
                    $month=$date->month<10 ? '0'.$date->month:$date->month;
                    $start=Time::createFromFormat('YmdHi',$date->year.$month.'010000');
                    $last=\cal_days_in_month(CAL_GREGORIAN, $date->getMonth(), $date->getYear());
                    $last=Time::createFromFormat('YmdHi',$date->year.$month.$last.'0000');
                    $start=$start->toDateTime()->format('W');
                    $last=$last->toDateTime()->format('W');
                    if ($last<$start)
                    {
                        $start=date("W", strtotime("-1 week",$last))+1;
                    }
                    return $last-$start+1;
                }else
		if ($targetDB=='startOfWeek' || $targetDB=='sw' || $targetDB=='bw')
		{
			$funct='subDays';
			$int=$date->getDayOfWeek();
			$int=$int-1;
		}else
		if (Str::contains(strtolower($targetDB),'week'))
		{
			$int=$int*7;
			$funct.='Days';
		}else
		if (Str::contains(strtolower($targetDB),'day'))
		{
			$funct.='Days';
		}else
		if (Str::contains(strtolower($targetDB),'second'))
		{
			$funct.='Seconds';
		}else
		if (Str::contains(strtolower($targetDB),'minute'))
		{
			$funct.='Minutes';
		}else
		if (Str::contains(strtolower($targetDB),'hour'))
		{
			$funct.='Hours';
		}else
		if (Str::contains(strtolower($targetDB),'month'))
		{
                        $cdate=$date;
                        for($i=0;$i<$int;$i++)
                        {
                            $days=\cal_days_in_month(CAL_GREGORIAN, $cdate->getMonth(), $cdate->getYear());
                            $cdate->modify(($funct=='sub' ? '-':'').$days.' days');
                        }
                       return $cdate->format($format);
		}else
		if (Str::contains(strtolower($targetDB),'year'))
		{
			$funct.='Years';
		} else 
                {
                    return $date->format($targetDB);
                }
		$date=$date->{$funct}($int);
		func_end:
		return $date->toDateTime()->format($format);//date($format,$date);
	}else
	if ($targetDB)
	{
		if (is_int($date))
		{
                    return date($format,$date);
		}else
		if (is_a($date, 'DateTime'))
		{
			return $date->format($format);
		}else
		{
			return null;
		}
		
	}else
	{
            if (is_a($date,'\CodeIgniter\I18n\Time'))
            {
                return $date->toDateTime()->format($format);
            }
            return date($format,$date);
	}
}

/**
 * Check if given date is valid using given date format
 * 
 * @param string $date
 * @param mixed  $formatIn
 * 
 * @return bool
 */
function validateDate(string $date,$formatIn=null)
{
    $formatIn=strtoupper($formatIn)=='DB' || $formatIn==null ? 'YmdHi' : $formatIn;
    $date=\DateTime::createFromFormat($formatIn,$date);
    return is_bool($date) ? FALSE : TRUE;
}
        
/**
 * Convert given string to formated date string
 * 
 * @param  mixed  $date      Date string
 * @param  string $formatIn  Format of given date string
 * @param  string $formatOut Format which will be used to format date (for db format use DB or null)
 * 
 */
function convertDate($date,$formatIn,$formatOut)
{
	$formatOut=strtoupper($formatOut)=='DB'||$formatOut==null?'YmdHi':$formatOut;
	$formatOut=$formatOut=='ISO8601' ? \DateTime::ATOM : $formatOut;
        $formatOut=$formatOut=='date' ? config('APP')->dateFormat: $formatOut;
	$formatIn=strtoupper($formatIn)=='DB' || $formatIn==null ? 'YmdHi' : $formatIn;
	if ($formatIn=='ISO8601')
	{
		$date=new \DateTime($date);
	}else
	{
            $date=\DateTime::createFromFormat($formatIn,$date);
	}
	
	if ($date!=FALSE)
	{
		return $date->format($formatOut);
	}else
	{
		return null;
	}
}

/**
 * Returns array with week days names
 * 
 * @param  $locale String representation of locale (ie en)
 * 
 * @return array
 */
function getWeekDaysNames($locale=null)
{
	if ($locale!=null)
	{
		setlocale(LC_TIME, $locale);
	}
	$days=[];
	$today = ( 86400 * (date("N")) );
	for( $i = 0; $i < 7; $i++ ) 
	{
    	$days[] = strftime('%A', time() - $today + ($i*86400));
	}
	return $days;
}

/**
 * Protect resource file by system
 * 
 * @param  string $url      Absolute or Not path to file
 * @param  bool   $base64   Determine if file link will be protected (FALSE) or base64 URI created (TRUE, only for images/videos)
 * @param  bool   $fullLink Determine if full link or just id will be returned
 * 
 * @return string
 */
function protected_link($url,$base64=FALSE,$fullLink=TRUE)
{
  $url=parsePath($url);
	if ($base64)
	{
		return Str::resourceToBase64($url);
	}
	$url=str_replace(config('App')->baseURL, FCPATH, $url);
	$url=base64url_encode(\Config\Services::encrypter()->encrypt($url));
	return $fullLink ? url('Media/MediaController','getfile',['id'=>$url]):$url;
}


function loadModuleFromString($string,array $extraArgs=[])
{
    if (!Str::contains($string,'::'))
    {
        if (Str::isJson($string))
        {
            $string= json_decode($string,TRUE);
            if (is_array($string) && Arr::KeysExists(['controller','action'], $string))
            {
                $string= array_values($string);
                if (count($string)<3)
                {
                    $string[2]=[];
                }
                goto merge_args;
            }
        }
        return null;
    }
	$string=explode('::', $string);
        
	if (Str::contains($string[1],'@'))
	{
		$string[1]=explode('@', $string[1]);
		$string[2]=explode(',',$string[1][1]);
		$string[1]=$string[1][0];
	}else
	{
		$string[2]=[];
	}
        merge_args:
        if (count($extraArgs)>0)
        {
           $string[2]= array_merge($string[2],$extraArgs);
        }
	return loadModule($string[0],$string[1],$string[2]);
}

function loadModuleFromArray(array $arr)
{
	if (!Arr::keysExists(['controller','action'],$arr))
	{
		return null;
	}
        if (array_key_exists('params', $arr))
        {
            $arr['args']=$arr['params'];
        }
	return loadModule($arr['controller'],$arr['action'],array_key_exists('args', $arr) ? $arr['args']:[]);
}

/**
 * Load Controller (and call method with params)
 * 
 * @params  string $controller Controller name or full class name
 * @params  string $method     Controller method name
 * @params  array  $params	   Params which will be passed to controller method
 * 
 * @return mixed
 */
function loadModule($controller,$method=null,array $params = [])
{
        if (Str::isJson($controller))
        {
            return loadModuleFromArray(json_decode($controller,TRUE));
        }
	if (Str::contains($controller,'::'))
	{
		$controller=explode('::', $controller);
		$action=$controller[1];
		$controller=$controller[0];
		if (Str::contains($action,'/'))
		{
			$action=explode('/', $action);
			$method=$action[0];
			unset($action[0]);
			$params=array_merge($params,$action);
		}else
		{
			$method=$action;
		}
	}	
	
	if (Str::endsWith($controller,'Model'))
	{
		$controller=model($controller);
		goto method_set;
	}
		
	if (!Str::startsWith($controller,'\EMPORIKO\Controllers'))
	{
		$controller='\\EMPORIKO\\Controllers\\'.$controller;
	}
        
	$controller= str_replace('\EMPORIKO\Controllers\Orders', '\EMPORIKO\Controllers\Sales', $controller);
	
        if (!class_exists($controller))
	{
		throw new Exception(lang('system.errors.load_module_no_class').' '.$controller, 1);		
	}
	$controller=new $controller();
	
	if (method_exists($controller, 'initController'))
	{
		$controller->initController(Services::request(), Services::response(), Services::logger());
	}
	
	method_set:
	$output=$controller;
	if ($method!=null)
	{
            
		if (! method_exists($controller,$method))
		{
			throw new Exception(lang('system.errors.load_module_no_method'), 1);
		}
		
		$refMethod  = new ReflectionMethod($controller, $method);
		$paramCount = $refMethod->getNumberOfParameters();
		$refParams  = $refMethod->getParameters();
		$output=null;
		if ($paramCount === 0)
		{
			if (count($params)>0)
			{
				throw new Exception(lang('system.errors.load_module_no_params'), 1);
			}
			$output = $controller->{$method}();
		}else
		if ($paramCount<count($params))
		{
			//throw new Exception(lang('system.errors.load_module_no_params'), 1);
		}else
		{
			
		}$output = call_user_func_array([$controller,$method], $params);
	}
	
	return $output;
}
	/**
	 * Parse given path to full website or server path
	 * 
	 * @param  bool $direct If true server path will be used instead of website url
	 * 
	 * @return string 
	 */
	function parsePath($path,$direct=FALSE)
	{
		$baseURL= is_bool($direct) && $direct ? FCPATH : config('App')->baseURL;
		$repl=
		[
			'@vendor'=>'@assets/vendor/',
			'@template'=>'@assets/template/',
			'@storage'=>'@writable/',
			'@views'=>'@app/Views/',
			'@temp'=>FCPATH . 'writable/temp/',
                        '@upload'=>FCPATH . 'writable/uploads/',
                        '@cache'=>FCPATH . 'writable/cache/',
			'@app'=>realpath(config('Paths')->appDirectory),
                        '@curr_url_ref'=> current_url(FALSE,TRUE),
			'@'=>$baseURL,
			'://'=>':#',
			'//'=>'/',
			':#'=>'://',
                        '\/'=>'/',
		];
                $path=str_replace(array_keys($repl),array_values($repl), $path);
                if (is_bool($direct) && $direct)
                {
                    $path=str_replace(' ','_', $path);
                }
		return $direct=='rel' || $direct=='relative' ? str_replace(config('App')->baseURL, '/', $path) : $path ;
	}
	
	 /**
	 * Create html message container
	 * 
	 * @param  String $message Message text (if prefix with @ it will be used as language tag name)
	 * @param  String $type    Type of message (danger,info,success)
	 * @param  mixed  $encode  Determine if html code is base64 (or base64url) encoded
	 * @return String
	 */
	 function createErrorMessage($message,$type='info',$encode=FALSE)
	 {
	 	$message=is_array($message)?$message:[$message];
		$result='';
		$View=service('viewRenderer',FALSE);
		foreach ($message as $value) 
		{
			$result.=$View->setData(['msg'=>lang($value),'type'=>$type])->render(parsePath('@views/errors/html/exception',TRUE));
		}
		if ($encode)
		{
			$result=base64_encode($result);
		}else
		if ($encode=='url')
		{
			$result=base64url_encode($result);
		}
	 	return $result;
	 }
	 
	 function getData($object,$arg=null)
	 {
	 	if (is_array($object))
		{
			if (array_key_exists($arg, $object))
			{
				return $object[$arg];
			}else
			if(is_string($arg) && Str::contains($arg,'.'))
			{
				return dot_array_search($arg,$object);	
			}else
			{
				return null;
			}
			
		}else
		if (is_object($object) && property_exists($object, $arg))
		{
			return $object->{$arg};
		}else
		if (is_string($object) && $arg==null)
		{
			if (!empty($$object))
			{
				return $$object;
			}else
			{
				return null;
			}
		}
		
	 }
	 
	 function createDefaultAvatar(string $text = 'DEV',array $bgColor = [255, 255, 255],array $textColor = [0, 0, 0],int $fontSize = 340,int $width = 600,int $height = 600,string $font = '@vendor/fonts/myfont.ttf') 
	 {
    	$font=parsePath($font,TRUE);
        $image = @imagecreate($width, $height)
            or die("Cannot Initialize new GD image stream");
		
		if (Str::contains($text,' '))
		{
			$stext='';
			foreach (explode(' ', $text) as  $value) 
			{
				$stext.=substr($value,0,1);
			}
			$text=$stext;
		}else
		{
			$text=substr($text, 0,1);
		}
		
		if (strlen($text)>3)
		{
			$text=substr($text, 0,3);
			$fontSize=240;
		}else
		if (strlen($text)>2)
		{
			$fontSize=240;
		}
		
        imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);

        $fontColor = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);

        $textBoundingBox = imagettfbbox($fontSize, 0, $font, $text);

        $y = abs(ceil(($height - $textBoundingBox[5]) / 2));
        $x = abs(ceil(($width - $textBoundingBox[2]) / 2));

        imagettftext($image, $fontSize, 0, $x, $y, $fontColor, $font, $text);
		
		ob_start(); // Let's start output buffering.
    	imagejpeg($image); //This will normally output the image, but because of ob_start(), it won't.
    	$contents = ob_get_contents(); //Instead, output above is saved to $contents
		ob_end_clean();
		imagedestroy($image);
        return base64_encode($contents);
    }
    
    /**
     * Converts bytes to KB and MB
     * 
     * @param mixed  $size
     * @param string $sizeMode
     * 
     * @return mixed
     */
    function convertBytesSize($size,string $sizeMode='KB')
    {
        $sizeMode= strtolower($sizeMode);
        $size=floatval($size)/1024;
        if ($sizeMode=='mb')
        {
            $size=$size/1024;
        }else
        if ($sizeMode=='text')
        {
            if ($size > 1024)
            {
                $size=$size/1024;
                $size=round($size,2).' MB';
            } else 
            {
                $size=round($size,2).' KB';
            }
        }
        if (is_numeric($size))
        {
            $size=round($size,2);
        }
        return $size;
        
    }
	/**
	 * Generates barcode from data
	 * 
	 * @param  string $data
	 * @param  array  $options
	 * @param  bool   $imgTag
	 * @return string
	 */
	function GenerateBarcode($data,array $options=[],$imgTag=TRUE,array $imgTagArgs=[])
	{
		$options['st']=FALSE;
		$data=service('BarcodeGenerator')->generateBase64($data,$options);
		return $imgTag ? img($data,FALSE,$imgTagArgs) : $data;
	}
	 

function dump($data,$echo=TRUE)
{
	$arr=debug_backtrace();
	$str='';
	if (!defined(ENVIRONMENT)||(defined(ENVIRONMENT) && ENVIRONMENT=='production'))
	{
		//throw new Exception("Try to dump data in production ".$arr[0]['file'].' at '.$arr[0]['line'], 1);		
	}
	
	if (is_array($arr)&&count($arr)>0)
	{
		$str= '<p>'.$arr[0]['file'].' at '.$arr[0]['line'].'</p><br>';
	}
	
	if (is_array($data)||is_object($data))
	{
		if ($echo)
		{
			echo $str.'<pre>';print_r($data); '</pre>';
		}else
		{
			return $str.'<pre>'.print_r($data,TRUE).'</pre>';
		}
		
	}else
	{
		var_dump($data);
	}
	
	
}



