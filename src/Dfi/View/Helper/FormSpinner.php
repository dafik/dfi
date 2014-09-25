<?php

class Dfi_View_Helper_FormSpinner extends Zend_View_Helper_FormText
{

	 
	public function formSpinner( $name, $value = null, $attribs = null)
	{
		$options = $this->getOptions($attribs);

		$this->view->headScript()->appendFile(_JS.'lib/jquery/ui.spinner.js');
		$inlineScript = 'jQuery().ready(function($) {
            $(\'#'.$name.'\').spinner('.$this->parseOptions($options).');
          });';


		$xhtml = parent::formText($name, $value, $attribs);
		$res = $this->view->inlineScript()->setScript($inlineScript, $type = 'text/javascript', $attrs = array());
		return $xhtml.$res;


	}
	private function getOptions(&$attribs = null){
		$allowedOptions = array(
		'min',
		'max',
		'places',
		'step',
		'largeStep',
		'group',
		'point',
		'prefix',
		'suffix',
		'className',
		'showOn',
		'width',
		'increment',
		'mouseWheel',
		'allowNull',
		'format',
		'parse'
		);
		$options = array();
		foreach ($allowedOptions as $option) {
			if (isset($attribs[$option])) {
				$options[$option] = $attribs[$option];
				unset($attribs[$option]);
			}
		}
		return $options;
	}
	private function parseOptions($options=array()){
		if (count($options) > 0) {
			$opt ='{';
			foreach ($options as $option => $value) {
				$tmp[] = '\''.$option .'\' : \''.$value .'\''; 
			}
			$opt .= implode(",\n", $tmp);
			$opt .= '}';
			return $opt;
		}
        return $options;
	}
}



