<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* Document class
*/
class Document {
	private $title;
	private $description;
	private $keywords;

  // OCFilter start
  private $noindex = false;
  // OCFilter end
      
	private $links = array();
	private $styles = array();
	private $scripts = array();

			private $robots;
			
		public function setRobots($value) {
			$this->robots = $value;
		}
	
		public function getRobots() {
			return $this->robots;
		}
	
			

				private $ogmetas = array();
                

			private $octStyles = [];
			private $octScripts = [];
			
			public function addOctStyle($href, $rel = 'stylesheet', $media = 'screen') {
				$href = $this->removeVersion($href);
				
				$this->octStyles[$href] = [
					'href'  => $href,
					'rel'   => $rel,
					'media' => $media
				];
			}
		
			public function getOctStyles() {
				$styles = $this->styles;
				$this->styles = [];
				
				foreach ($styles as $style) {
					$href = $this->removeVersion($style['href']);
					
					$this->styles[$href] = [
						'href'  => $href,
						'rel'   => $style['rel'],
						'media' => $style['media']
					];
				}
				
				return array_merge($this->octStyles, $this->styles);
			}
		
			public function addOctScript($href, $postion = 'header') {
				$href = $this->removeVersion($href);
				
				$this->octScripts[$postion][$href] = $href;
			}
		
			public function getOctScripts($postion = 'header') {
				if (isset($this->octScripts[$postion])) {
					$scripts = isset($this->scripts[$postion]) ? $this->scripts[$postion] : [];
					$this->scripts[$postion] = [];
					
					foreach ($scripts as $script) {
						$href = $this->removeVersion($script);
						
						$this->scripts[$postion][$href] = $href;
					}
					
					return array_merge($this->octScripts[$postion], $this->scripts[$postion]);
				} else {
					return array();
				}
			}
			
			private function removeVersion($link) {
				$href = explode('?', $link);
				
				if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($href[0]))) {
			       $link = $href[0];
		        }
		        
		        return $link;
		    }
			

	/**
     * 
     *
     * @param	string	$title
     */

  // OCFilter start
  public function setNoindex($state = false) {
  	$this->noindex = $state;
  }

	public function isNoindex() {
		return $this->noindex;
	}
  // OCFilter end
      
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
     * 
	 * 
	 * @return	string
     */
	public function getTitle() {
		return $this->title;
	}

	/**
     * 
     *
     * @param	string	$description
     */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
     * 
     *
     * @param	string	$description
	 * 
	 * @return	string
     */
	public function getDescription() {
		return $this->description;
	}

	/**
     * 
     *
     * @param	string	$keywords
     */

				public function addOGMeta($meta_name, $content) {
		            $this->ogmetas[] = array(
		        	'meta_name'  => $meta_name,
	        		'content'   => $content
	            	);
            	}
				public function getOGMeta() {
            		return $this->ogmetas;
            	}
                
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}

	/**
     *
	 * 
	 * @return	string
     */
	public function getKeywords() {
		return $this->keywords;
	}
	
	/**
     * 
     *
     * @param	string	$href
	 * @param	string	$rel
     */

// Full IndeX →
			public function delLinks($rel) {
				foreach( $this->links as $key=>$value){
					if (in_array($value['rel'], $rel)) unset($this->links[$key]);
				}
			}
// ← Full IndeX
			
	public function addLink($href, $rel) {
		$this->links[$href] = array(
			'href' => $href,
			'rel'  => $rel
		);
	}

	/**
     * 
	 * 
	 * @return	array
     */

  // OCFilter canonical fix start
	public function deleteLink($rel) {
    foreach ($this->links as $href => $link) {
      if ($link['rel'] == $rel) {
      	unset($this->links[$href]);
      }
    }
	}
  // OCFilter canonical fix end
      
	public function getLinks() {
		return $this->links;
	}

	/**
     * 
     *
     * @param	string	$href
	 * @param	string	$rel
	 * @param	string	$media
     */
	public function addStyle($href, $rel = 'stylesheet', $media = 'screen') {
		$this->styles[$href] = array(
			'href'  => $href,
			'rel'   => $rel,
			'media' => $media
		);
	}

	/**
     * 
	 * 
	 * @return	array
     */
	public function getStyles() {
		return $this->styles;
	}

	/**
     * 
     *
     * @param	string	$href
	 * @param	string	$postion
     */
	public function addScript($href, $postion = 'header') {
		$this->scripts[$postion][$href] = $href;
	}

	/**
     * 
     *
     * @param	string	$postion
	 * 
	 * @return	array
     */
	public function getScripts($postion = 'header') {
		if (isset($this->scripts[$postion])) {
			return $this->scripts[$postion];
		} else {
			return array();
		}
	}
}