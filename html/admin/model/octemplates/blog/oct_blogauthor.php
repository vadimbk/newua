<?php
/**********************************************************/
/*	@copyright	OCTemplates 2015-2019.					  */
/*	@support	https://octemplates.net/					  */
/*	@license	LICENSE.txt									  */
/**********************************************************/

class ModelOCTemplatesBlogOCTBlogAuthor extends Model {
	public function addAuthor($data) {
		if ($data['date_added'] == '0000-00-00') {
			$data['date_added'] = "NOW()";
		}
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor SET date_available = '" . $this->db->escape($data['date_available']) . "', facebook = '" . $this->db->escape($value['facebook']) . "', youtube = '" . $this->db->escape($value['youtube']) . "', instagram = '" . $this->db->escape($value['instagram']) . "', linkedin= '" . $this->db->escape($value['linkedin']) . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = '" . $this->db->escape($data['date_added']) . "', date_modified = NOW()");

		$blogauthor_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "oct_blogauthor SET image = '" . $this->db->escape($data['image']) . "' WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		}
		
		foreach ($data['author_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_description SET blogauthor_id = '" . (int)$blogauthor_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', shot_description = '" . $this->db->escape($value['shot_description']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['author_store'])) {
			foreach ($data['author_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_to_store SET blogauthor_id = '" . (int)$blogauthor_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['author_image'])) {
			foreach ($data['author_image'] as $author_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_image SET blogauthor_id = '" . (int)$blogauthor_id . "', image = '" . $this->db->escape($author_image['image']) . "', sort_order = '" . (int)$author_image['sort_order'] . "'");
			}
		}

		if (isset($data['author_category'])) {
			foreach ($data['author_category'] as $blogcategory_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_to_category SET blogauthor_id = '" . (int)$blogauthor_id . "', blogcategory_id = '" . (int)$blogcategory_id . "'");
			}
		}

		if (isset($data['author_related'])) {
			foreach ($data['author_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related WHERE blogauthor_id = '" . (int)$blogauthor_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_related SET blogauthor_id = '" . (int)$blogauthor_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related WHERE blogauthor_id = '" . (int)$related_id . "' AND related_id = '" . (int)$blogauthor_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_related SET blogauthor_id = '" . (int)$related_id . "', related_id = '" . (int)$blogauthor_id . "'");
			}
		}

		if (isset($data['author_related_product'])) {
			foreach ($data['author_related_product'] as $product_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related_product WHERE blogauthor_id = '" . (int)$blogauthor_id . "' AND product_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_related_product SET blogauthor_id = '" . (int)$blogauthor_id . "', product_id = '" . (int)$product_id . "'");
			}
		}

		// SEO URL
		if (isset($data['author_seo_url'])) {
			foreach ($data['author_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'blogauthor_id=" . (int)$blogauthor_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		if (isset($data['author_layout'])) {
			foreach ($data['author_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_to_layout SET blogauthor_id = '" . (int)$blogauthor_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}


		$this->cache->delete('oct_blogauthor');

		return $blogauthor_id;
	}

	public function editAuthor($blogauthor_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "oct_blogauthor SET date_available = '" . $this->db->escape($data['date_available']) . "', date_added = '" . $this->db->escape($data['date_added']) . "', status = '" . (int)$data['status'] . "', facebook = '" . $this->db->escape($data['facebook']) . "', youtube = '" . $this->db->escape($data['youtube']) . "', instagram = '" . $this->db->escape($data['instagram']) . "', linkedin= '" . $this->db->escape($data['linkedin']) . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "oct_blogauthor SET image = '" . $this->db->escape($data['image']) . "' WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		}

		
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_description WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		foreach ($data['author_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_description SET blogauthor_id = '" . (int)$blogauthor_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', shot_description = '" . $this->db->escape($value['shot_description']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_to_store WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		if (isset($data['author_store'])) {
			foreach ($data['author_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_to_store SET blogauthor_id = '" . (int)$blogauthor_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_image WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		if (isset($data['author_image'])) {
			foreach ($data['author_image'] as $author_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_image SET blogauthor_id = '" . (int)$blogauthor_id . "', image = '" . $this->db->escape($author_image['image']) . "', sort_order = '" . (int)$author_image['sort_order'] . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_to_category WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		if (isset($data['author_category'])) {
			foreach ($data['author_category'] as $blogcategory_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_to_category SET blogauthor_id = '" . (int)$blogauthor_id . "', blogcategory_id = '" . (int)$blogcategory_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related WHERE related_id = '" . (int)$blogauthor_id . "'");

		if (isset($data['author_related'])) {
			foreach ($data['author_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related WHERE blogauthor_id = '" . (int)$blogauthor_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_related SET blogauthor_id = '" . (int)$blogauthor_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related WHERE blogauthor_id = '" . (int)$related_id . "' AND related_id = '" . (int)$blogauthor_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_related SET blogauthor_id = '" . (int)$related_id . "', related_id = '" . (int)$blogauthor_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related_product WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		if (isset($data['author_related_product'])) {
			foreach ($data['author_related_product'] as $product_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related_product WHERE blogauthor_id = '" . (int)$blogauthor_id . "' AND product_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_related_product SET blogauthor_id = '" . (int)$blogauthor_id . "', product_id = '" . (int)$product_id . "'");
			}
		}

		// SEO URL
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'blogauthor_id=" . (int)$blogauthor_id . "'");

		if (isset($data['author_seo_url'])) {
			foreach ($data['author_seo_url']as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'blogauthor_id=" . (int)$blogauthor_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_to_layout WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		if (isset($data['author_layout'])) {
			foreach ($data['author_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "oct_blogauthor_to_layout SET blogauthor_id = '" . (int)$blogauthor_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}

		$this->cache->delete('oct_blogauthor');
	}

	public function copyAuthor($blogauthor_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "oct_blogauthor p WHERE p.blogauthor_id = '" . (int)$blogauthor_id . "'");

		if ($query->num_rows) {
			$data = $query->row;

			$data['viewed'] = '0';
			$data['keyword'] = '';
			$data['status'] = '0';

			$data['author_description'] = $this->getAuthorDescriptions($blogauthor_id);
			$data['author_image'] = $this->getAuthorImages($blogauthor_id);
			$data['author_related'] = $this->getAuthorRelated($blogauthor_id);
			$data['author_category'] = $this->getAuthorCategories($blogauthor_id);
			$data['author_layout'] = $this->getAuthorLayouts($blogauthor_id);
			$data['author_store'] = $this->getAuthorStores($blogauthor_id);

			$this->addAuthor($data);
		}
	}

	public function deleteAuthor($blogauthor_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_description WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_image WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_related WHERE related_id = '" . (int)$blogauthor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_to_category WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_to_layout WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogauthor_to_store WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "oct_blogcomments WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'blogauthor_id=" . (int)$blogauthor_id . "'");

		$this->cache->delete('oct_blogauthor');
	}

	public function getAuthor($blogauthor_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "oct_blogauthor p LEFT JOIN " . DB_PREFIX . "oct_blogauthor_description pd ON (p.blogauthor_id = pd.blogauthor_id) WHERE p.blogauthor_id = '" . (int)$blogauthor_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getAuthors($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "oct_blogauthor a LEFT JOIN " . DB_PREFIX . "oct_blogauthor_description ad ON (a.blogauthor_id = ad.blogauthor_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND a.status = '" . (int)$data['filter_status'] . "'";
		}

		$sql .= " GROUP BY a.blogauthor_id";

		$sort_data = array(
			'ad.name',
			'a.status',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY a.date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getAuthorsByCategoryId($blogcategory_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "oct_blogauthor p LEFT JOIN " . DB_PREFIX . "oct_blogauthor_description pd ON (p.blogauthor_id = pd.blogauthor_id) LEFT JOIN " . DB_PREFIX . "oct_blogauthor_to_category p2c ON (p.blogauthor_id = p2c.blogauthor_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.blogcategory_id = '" . (int)$blogcategory_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getAuthorDescriptions($blogauthor_id) {
		$author_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "oct_blogauthor_description WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		foreach ($query->rows as $result) {
			$author_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'shot_description' => $result['shot_description'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'tag'              => $result['tag']
			);
		}

		return $author_description_data;
	}

	public function getAuthorCategories($blogauthor_id) {
		$author_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "oct_blogauthor_to_category WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		foreach ($query->rows as $result) {
			$author_category_data[] = $result['blogcategory_id'];
		}

		return $author_category_data;
	}

	public function getAuthorImages($blogauthor_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "oct_blogauthor_image WHERE blogauthor_id = '" . (int)$blogauthor_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getAuthorStores($blogauthor_id) {
		$author_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "oct_blogauthor_to_store WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		foreach ($query->rows as $result) {
			$author_store_data[] = $result['store_id'];
		}

		return $author_store_data;
	}

	public function getAuthorSeoUrls($blogauthor_id) {
		$author_seo_url_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'blogauthor_id=" . (int)$blogauthor_id . "'");

		foreach ($query->rows as $result) {
			$author_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $author_seo_url_data;
	}

	public function getAuthorLayouts($blogauthor_id) {
		$author_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "oct_blogauthor_to_layout WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		foreach ($query->rows as $result) {
			$author_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $author_layout_data;
	}

	public function getAuthorRelated($blogauthor_id) {
		$author_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "oct_blogauthor_related WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		foreach ($query->rows as $result) {
			$author_related_data[] = $result['related_id'];
		}

		return $author_related_data;
	}

	public function getAuthorRelatedProducts($blogauthor_id) {
		$author_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "oct_blogauthor_related_product WHERE blogauthor_id = '" . (int)$blogauthor_id . "'");

		foreach ($query->rows as $result) {
			$author_related_data[] = $result['product_id'];
		}

		return $author_related_data;
	}

	public function getTotalAuthors($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.blogauthor_id) AS total FROM " . DB_PREFIX . "oct_blogauthor p LEFT JOIN " . DB_PREFIX . "oct_blogauthor_description pd ON (p.blogauthor_id = pd.blogauthor_id)";

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalAuthorsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "oct_blogauthor_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
}
