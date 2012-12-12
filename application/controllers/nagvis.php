<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Nagvis controller
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Nagvis_Controller extends Authenticated_Controller {
	public function __call($name, $args)
	{
		$this->template->disable_refresh = true;
		$this->template->title = _('Monitoring') . ' » NagVis';
		$this->template->breadcrumb = _('Monitoring') . ' » '
			. '<a href="' . Kohana::config('config.site_domain') .
			'index.php/nagvis/index">NagVis</a> » ';
		$this->template->content = $this->add_view('nagvis/index');
		$this->xtra_js[] = $this->add_path('/js/iframe-adjust.js');

		$this->template->js_header = $this->add_view('js_header');
		$queryparams = http_build_query($_GET, '', '&amp;');
		switch($name) {
		 case 'index':
		 case 'configure':
			$this->template->content->params = $queryparams;
			break;
		 case 'view':
		 case 'edit':
			$this->template->content->params = 'mod=Map&amp;act=view&amp;show='.$args[0].'&amp;'.$queryparams;
			break;
		 case 'automap':
			$this->template->content->params = 'mod=Map&amp;act=view&amp;show=automap&amp;'.$queryparams;
			break;
		 case 'rotate':
			 $this->template->content->params = 'mod=Map&amp;act=view&amp;show='.$args[1].'&amp;rotation='.$args[0].'&amp;rotationStep=0&amp;'.$queryparams;
			break;
		}
	}
}

