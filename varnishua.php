<?php
/**
 * ---------------------------------------------------------------------------------------------------------
 * 	Varnish User Agent plugin
 *
 * Copyright (C) 2013 Dimitris Grammatikogiannis. All rights reserved.
 *
 * Varnish User Agent is free software and is distributed under the GNU General Public License,
 * and as distributed it may include or be derivative of works licensed under the GNU
 * General Public License or other free or open source software licenses.
 *	pc
 *	bot
 *	tablet-ipad
 *	mobile-iphone
 *	mobile-android
 *	tablet-android
 *	mobile-firefoxos
 *	mobile-smartphone
 *	mobile-generic
 * --------------------------------------------------------------------------------------------------------- 
 **/
// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * VARNISH, PHP UA Plugin
 */
class plgSystemVarnishua extends JPlugin
{
			
	public function onAfterInitialise()
	{
		if (!$this->params->get('varnish_setup')) {
//		echo "php staff ok";			
		include_once(dirname(__FILE__).'/lib/Mobile_Detect.php');
		$detect = new Mobile_Detect();
		$layout = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'desktop');
		if (($detect->is('Bot')) || ($detect->is('MobileBot'))) $layout = 'bot';
		$session = JFactory::getSession();
		$session->set('ualayout', $layout);
		}
		
		else {
//		echo "varnish staff ok";				
		$user = JFactory::getUser();
		if (!$user->guest) {
			JResponse::setHeader('X-Logged-In', 'True', true);
			} else {
			JResponse::setHeader('X-Logged-In', 'False', true);	
		}			
		$layout = $_SERVER['HTTP_X_UA_DEVICE'];
		$session = JFactory::getSession();
	
		if ($layout == 'pc') { $session->set('ualayout', 'desktop'); }
		if ($layout == 'bot') { $session->set('ualayout', 'bot'); }
		if (($layout == 'mobile-iphone') || ($layout == 'mobile-android') || ($layout == 'mobile-firefoxos') || ($layout == 'mobile-smartphone') || ($layout == 'mobile-generic')) { $session->set('ualayout', 'mobile'); }
		if (($layout == 'tablet-ipad') || ($layout == 'tablet-android')) { $session->set('ualayout', 'tablet'); }
		if ($layout == 'bad') { 
			header("Location: http://browsehappy.com");
					die();
				}			
		}
	}
}