<?php

/**
 * Content - Remote
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version    $Id $
 */

defined('_JEXEC') or die;

class plgContentRemote extends JPlugin {
	
	public function onContentPrepare($context, &$row, &$params, $page = 0) {
		
		if (JString::strpos($row->text, '{remote') === false){
			return true;
		}
		
		preg_match_all('#{remote\s+(.*?)}#i', $row->text, $matches, PREG_SET_ORDER);
			
		if (!empty($matches)){
			foreach ($matches as $match) {
				$row->text = JString::str_ireplace($match[0], $this->remote($match[1]), $row->text);
			}
		}
	}
	
	private function remote($url){
		if(!filter_var($url, FILTER_VALIDATE_URL)) {
			return 'not a valid url: ' . $url;	
		}
		
		$cache = JFactory::getCache('remote', 'output');
		$cache->setCaching(1);
		$cache->setLifeTime($this->params->get('cache_time', 60));
		 
		$key = md5($url);
		 
		if(!$result = $cache->get($key)) {
			try {
				$http = new JHttp(new JRegistry, new JHttpTransportCurl(new JRegistry));
		
				$result = $http->get($url, null, $this->params->get('timeout', 10));
			}catch(Exception $e) {
				return $e->getMessage();
			}
		
			$cache->store($result, $key);
		}
		 
		if($result->code != 200) {
			return __CLASS__ . ' HTTP-Status ' . JHtml::_('link', 'http://wikipedia.org/wiki/List_of_HTTP_status_codes#'.$result->code, $result->code, array('target' => '_blank'));
		}
		
		return $result->body;
	}
}