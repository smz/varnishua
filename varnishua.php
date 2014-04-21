<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgSystemVarnishua extends JPlugin
{
	function onAfterInitialise()
	{
		$app = JFactory::getApplication();

		if ( $app->isAdmin() )
		{
			return;
		}

		if ($this->params->get('varnish_setup', 0) == "0")
		{ //"PHP "
			include_once(dirname(__FILE__).'/lib/Mobile_Detect.php');
			$detect = new Mobile_Detect();
			$layout = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'desktop');
			if (($detect->is('Bot')) || ($detect->is('MobileBot'))) $layout = 'bot';
				$app->setUserState('varnishua.device', $layout);
		}
		else 
		{ // "varnish"
			$badbrowser = $this->params->get('redirect_link');
			
			if (isset($_SERVER['HTTP_X_UA_DEVICE']))
			{
				$layout = $_SERVER['HTTP_X_UA_DEVICE'];
			} 
			else
			{ 
				$layout = 'pc';
			}

			if ($layout == 'pc')
			{ 
				$app->setUserState('varnishua.device', 'desktop');
			}
			if ($layout == 'bot')
			{
				$app->setUserState('varnishua.device', 'bot');
			}
			if (($layout == 'mobile-iphone') || ($layout == 'mobile-android') || ($layout == 'mobile-firefoxos') || ($layout == 'mobile-smartphone') || ($layout == 'mobile-generic'))
			{
				$app->setUserState('varnishua.device', 'mobile');
			}
			if (($layout == 'tablet-ipad') || ($layout == 'tablet-android'))
			{
				$app->setUserState('varnishua.device', 'tablet');
			}
			if ($layout == 'bad')
			{
				header('Location: '.$badbrowser);
				die();
			}
		}
	}
}
