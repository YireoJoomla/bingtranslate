<?php
/*
 * Joomla! System Plugin - Bing Translate
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * Bing Translate System Plugin
 */
class plgSystemBingTranslate extends JPlugin
{
	/**
	 * Event onAfterRender
	 *
	 * @param null
	 * @return null
	 */
	public function onBeforeRender()
	{
		if($this->allow() == false)
		{
			return false;
		}

		// Add the proper JavaScript to this document
		JHtml::_('jquery.framework');
		$document = JFactory::getDocument();
		$document->addScript(JURI::base().'../media/com_bingtranslate/js/system.js');
		//$document->addStyleSheet(JURI::base().'../media/com_bingtranslate/css/system.css');
	}

    /**
     * Event onAfterRender
     *
     * @param null
     * @return null
     */
    public function onAfterRender()
    {
        if($this->allow() == false)
		{
			return false;
		}

        // Get the body and fetch a list of files
        $body = JResponse::getBody();

        if(preg_match_all('/\<input([^\>]+)\>/', $body, $matches)) {
            foreach($matches[0] as $matchIndex => $inputTag) {
                if(preg_match('/type=\"([^\"]+)/', $inputTag, $matchType) == false) {
                    continue;
                }

                $type = $matchType[1];
                if(in_array($type, $this->getAllowedTypes()) == false) {
                    continue;
                }

                $inputId = null;

                if(preg_match('/id=\"([^\"]+)/', $inputTag, $matchType)) {
                    $inputId = 'input#'.$matchType[1];
                } elseif(preg_match('/name=\"([^\"]+)/', $inputTag, $matchType)) {
                    $inputId = 'input[name=\''.$matchType[1].'\']';
                }
                
                if(empty($inputId)) {
                    return null;
                }

                $inputScript = 'javascript:doBingTranslate(\''.$inputId.'\', \'\', null);';
                $inputScript .= 'return false;';
                $inputHtml = array();
                $inputHtml[] = '<div class="input-append">';
                $inputHtml[] = $inputTag;
                $inputHtml[] = '<span class="add-on">';
                $inputHtml[] = '<a href="#" onclick="'.$inputScript.'"><i class="icon-copy"></i></a>';
                $inputHtml[] = '</span>';
                $inputHtml[] = '</div>';
                
                //$body = str_replace($inputTag, implode('', $inputHtml), $body);
            }
        }

        JResponse::setBody($body);
    }

	protected function allow()
	{
		// Fetch variables
		$app = JFactory::getApplication();
		$jinput = $app->input;
		$view = $jinput->getCmd('view');
		$layout = $jinput->getCmd('layout');

		// Check for the current view
		if(in_array($view, $this->getAllowedViews()) == false) {
			return false;
		}

		// Check for the current layout
		if(in_array($layout, $this->getAllowedLayouts()) == false) {
			return false;
		}

		return true;
	}

    protected function getAllowedTypes()
    {
        return array(
            'text',
        );
    }

    protected function getAllowedViews()
    {
        return array(
            'module',
            'category',
            'article',
            'item',
        );
    }

    protected function getAllowedLayouts()
    {
        return array(
            'edit',
            'form',
        );
    }
}
