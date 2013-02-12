<?php

namespace views;

use \models\Template as Template;
use \interfaces\Renderable as Renderable;

abstract class AbstractView implements Renderable {
	
	protected $_template;
	protected $_vars = array();
	protected $_children = array();
	
	public function __construct(Array $vars = array()){
		$this->_vars = $vars;
	}
	
	public function addChild(Renderable $child){
		$this->_children[] = $child;
	}
	
	public function render(){
		$html = '';
		foreach($this->_children as $child){
			$html .= $child->render() . "\n";
		}
		$template = new Template($this->_template, $this->_vars);
		return $template->render(array('content' => $html));
	}
	
	public function __set($property, $value){
		switch($property){
			case 'template':
				$this->_template = $value;
				break;
		}
	}
	
}