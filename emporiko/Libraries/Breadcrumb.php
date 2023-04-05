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

namespace EMPORIKO\Libraries;
  
class Breadcrumb
{

    private $breadcrumbs = array();
    private $tags;

    public function __construct()
    {
        $this->URI = service('uri');

        // SHOULD THE LAST BREADCRUMB BE A CLICKABLE LINK? iF SO SET TO TRUE
        $this->clickable = true;

        // create our bootstrap html elements
        $this->tags['navopen']  = "<nav aria-label=\"breadcrumb\">";
        $this->tags['navclose'] = "</nav>";
        $this->tags['olopen']   = "<ol class=\"breadcrumb%class%\">";
        $this->tags['olclose']  = "</ol>";
        $this->tags['liopen']   = "<li class=\"breadcrumb-item\">";
        $this->tags['liclose']  = "</li>";
		
	$this->add(lang('system.general.home_breadcrumb'), site_url());
    }

    public function add($crumb, $href)
    {
		if (!$crumb or !$href) return; // if the title or Href not set return 

        	$this->breadcrumbs[] = 
        	[
            	'crumb' => $crumb,
            'href' => $href,
        	];
    }
	
	 public function addMany(array $crumbs)
	 {
	 	foreach ($crumbs as $key => $value) 
	 	{
			 $this->add($key, $value);
		}
	 }
	
    public function render($class=null)
    {
	$class=$class==null ? '' : ' '.$class;
        $output  = $this->tags['navopen'];
        $output .= str_replace('%class%', $class, $this->tags['olopen']);
        
        $count = count($this->breadcrumbs) - 1;

        foreach ($this->breadcrumbs as $index => $breadcrumb) 
        {

            if ($index == $count) {
                $output .= $this->tags['liopen'];
                $output .= $breadcrumb['crumb'];
                $output .= $this->tags['liclose'];
            } else {
                $output .= $this->tags['liopen'];
                $output .= '<a href="' .  $breadcrumb['href'] . '" data-loader="true">';
                $output .= $breadcrumb['crumb'];
                $output .= '</a>';
                $output .= $this->tags['liclose'];
            }
        }

        $output .= $this->tags['olclose'];
        $output .= $this->tags['navclose'];

        return $output;
    }

    public function buildAuto($class=null)
    {
    	$current_seg=[];	
        foreach ($this->URI->getSegments() as $value) 
        {
            $current_seg[]=$value;
            $this->add($this->sanitizeName($value), site_url($current_seg));
        }

        return $this->render($class);
    }

	private function sanitizeName($name)
	{
		$name = ucwords(str_replace(array(".php", "_"), array("", " "), $name));
                $name = ucwords(str_replace('-', ' ', $name));
		$name=str_replace('+', '-', $name);
		return $name;
	}
}