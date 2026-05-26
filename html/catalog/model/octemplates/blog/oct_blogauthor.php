<?php
/**********************************************************/
/*	@copyright	OCTemplates 2015-2019.					  */
/*	@support	https://octemplates.net/					  */
/*	@license	LICENSE.txt									  */
/**********************************************************/

class ModelOCTemplatesBlogOCTBlogAuthor extends Model {
	public function updateViewed($blogauthor_id) {
		$this->db->query("
			UPDATE
				" . DB_PREFIX . "oct_blogauthor
			SET
				viewed =
				(
					viewed + 1
				)
			WHERE
				blogauthor_id = '" . (int)$blogauthor_id . "'
		");
	}

	public function getAuthor($blogauthor_id) {
		$query = $this->db->query("
			SELECT DISTINCT
				*,
				ad.name AS name,
				a.image,
				a.sort_order
			FROM
				" . DB_PREFIX . "oct_blogauthor a
				LEFT JOIN
					" . DB_PREFIX . "oct_blogauthor_description ad
					ON (a.blogauthor_id = ad.blogauthor_id)
				LEFT JOIN
					" . DB_PREFIX . "oct_blogauthor_to_store a2s
					ON (a.blogauthor_id = a2s.blogauthor_id)
			WHERE
				a.blogauthor_id = '" . (int)$blogauthor_id . "'
				AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'
				AND a.status = '1'
				AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
		");

		if ($query->num_rows) {
			$authors = [
				'blogauthor_id'   => $query->row['blogauthor_id'],
				'name'             => $query->row['name'],
				'shot_description' => $query->row['shot_description'],
				'description'      => $query->row['description'],
				'meta_title'       => $query->row['meta_title'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'image'            => $query->row['image'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed'],
				'youtube'           => $query->row['youtube'],
				'instagram'           => $query->row['instagram'],
				'facebook'           => $query->row['facebook'],
				'linkedin'           => $query->row['linkedin']
			];

			return $authors;
		} else {
			return false;
		}
	}
	
	public function getArticle($blogarticle_id) {
		$query = $this->db->query("
			SELECT DISTINCT
				*,
				ad.name AS name,
				a.image,
				(
					SELECT
						COUNT(*) AS total
					FROM
						" . DB_PREFIX . "oct_blogcomments r2
					WHERE
						r2.blogarticle_id = a.blogarticle_id
						AND r2.status = '1'
					GROUP BY
						r2.blogarticle_id
				)
				AS comments_total,
				a.sort_order
			FROM
				" . DB_PREFIX . "oct_blogarticle a
				LEFT JOIN
					" . DB_PREFIX . "oct_blogarticle_description ad
					ON (a.blogarticle_id = ad.blogarticle_id)
				LEFT JOIN
					" . DB_PREFIX . "oct_blogarticle_to_store a2s
					ON (a.blogarticle_id = a2s.blogarticle_id)
			WHERE
				a.blogarticle_id = '" . (int)$blogarticle_id . "'
				AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'
				AND a.status = '1'
				AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
		");

		if ($query->num_rows) {
			$articles = [
				'blogarticle_id'   => $query->row['blogarticle_id'],
				'name'             => $query->row['name'],
				'shot_description' => $query->row['shot_description'],
				'description'      => $query->row['description'],
				'meta_title'       => $query->row['meta_title'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'image'            => $query->row['image'],
				'comments_total'   => $query->row['comments_total'] ? $query->row['comments_total'] : 0,
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			];

			return $articles;
		} else {
			return false;
		}
	}

	public function getAuthors($data = []) {
		$sql = "
			SELECT
				a.blogauthor_id
		";

		if (isset($data['filter_blogcategory_id']) && !empty($data['filter_blogcategory_id'])) {
			if (isset($data['filter_sub_blogcategory']) && !empty($data['filter_sub_blogcategory'])) {
				$sql .= " FROM " . DB_PREFIX . "oct_blogcategory_path bcp LEFT JOIN " . DB_PREFIX . "oct_blogauthor_to_category a2c ON (bcp.blogcategory_id = a2c.blogcategory_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "oct_blogauthor_to_category a2c";
			}

			$sql .= " LEFT JOIN " . DB_PREFIX . "oct_blogauthor a ON (a2c.blogauthor_id = a.blogauthor_id)";
		} else {
			$sql .= " FROM " . DB_PREFIX . "oct_blogauthor a";
		}

		$sql .= "
			LEFT JOIN
				" . DB_PREFIX . "oct_blogauthor_description ad
				ON (a.blogauthor_id = ad.blogauthor_id)
			LEFT JOIN
				" . DB_PREFIX . "oct_blogauthor_to_store a2s
				ON (a.blogauthor_id = a2s.blogauthor_id)
			WHERE
				ad.language_id = '" . (int)$this->config->get('config_language_id') . "'
				AND a.status = '1'
				AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
		";

		if (isset($data['filter_blogcategory_id']) && !empty($data['filter_blogcategory_id'])) {
			if (is_array($data['filter_blogcategory_id'])) {
				$implode = [];

		        foreach ($data['filter_blogcategory_id'] as $blogcategory_id) {
					$implode[] = (int)$blogcategory_id;
				}

				$sql .= " AND a2c.blogcategory_id IN (" . implode(',', $implode) . ")";
			} else {
				if (isset($data['filter_sub_blogcategory']) && !empty($data['filter_sub_blogcategory'])) {
					$sql .= " AND bcp.blog_path_id = '" . (int)$data['filter_blogcategory_id'] . "'";
				} else {
					$sql .= " AND a2c.blogcategory_id = '" . (int)$data['filter_blogcategory_id'] . "'";
				}
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = [];

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "ad.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR ad.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = [];

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "ad.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			$sql .= ")";
		}

		$sql .= " GROUP BY a.blogauthor_id";

		$sort_data = [
			'ad.name',
			'a.sort_order',
			'a.date_added'
		];

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'ad.name') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY a.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(ad.name) DESC";
		} else {
			$sql .= " ASC, LCASE(ad.name) ASC";
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

		$author_data = [];

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$author_data[$result['blogauthor_id']] = $this->getAuthor($result['blogauthor_id']);
		}

		return $author_data;
	}

	public function getAuthorImages($blogauthor_id) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				" . DB_PREFIX . "oct_blogauthor_image
			WHERE
				blogauthor_id = '" . (int)$blogauthor_id . "'
			ORDER BY
				sort_order ASC
		");

		return $query->rows;
	}

	public function getAuthorRelated($blogauthor_id) {
		$article_data = [];

		$query = $this->db->query("
			SELECT
				*
			FROM
				" . DB_PREFIX . "oct_blogarticle_to_author a2a
				LEFT JOIN
					" . DB_PREFIX . "oct_blogauthor a
					ON (a2a.blogarticle_id = a.blogauthor_id)
				LEFT JOIN
					" . DB_PREFIX . "oct_blogauthor_to_store a2s
					ON (a.blogauthor_id = a2s.blogauthor_id)
			WHERE
				a2a.blogauthor_id = '" . (int)$blogauthor_id . "'
				
		");

		foreach ($query->rows as $result) {
			$article_data[$result['blogarticle_id']] = $this->getArticle($result['blogarticle_id']);
		}

		return $article_data;
	}

	public function getAuthorRelatedProduct($blogauthor_id) {
		$this->load->model('catalog/product');

		$product_data = [];

		$query = $this->db->query("
			SELECT
				*
			FROM
				" . DB_PREFIX . "oct_blogauthor_related_product arp
			WHERE
				arp.blogauthor_id = '" . (int)$blogauthor_id . "'
		");

		foreach ($query->rows as $result) {
			$product_info = $this->model_catalog_product->getProduct($result['product_id']);

			if ($product_info) {
				$product_data[$result['product_id']] = $product_info;
			}
		}

		return $product_data;
	}

	public function getAuthorLayoutId($blogauthor_id) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				" . DB_PREFIX . "oct_blogauthor_to_layout
			WHERE
				blogauthor_id = '" . (int)$blogauthor_id . "'
				AND store_id = '" . (int)$this->config->get('config_store_id') . "'
		");

		if ($query->num_rows) {
			return (int)$query->row['layout_id'];
		} else {
			return 0;
		}
	}

	public function getAuthorBlogCategories($blogauthor_id) {
		$query = $this->db->query("
			SELECT
				*
			FROM
				" . DB_PREFIX . "oct_blogauthor_to_category
			WHERE
				blogauthor_id = '" . (int)$blogauthor_id . "'
		");

		return $query->rows;
	}

	public function getTotalAuthors($data = []) {
		$sql = "SELECT COUNT(DISTINCT a.blogauthor_id) AS total";

		if (!empty($data['filter_blogcategory_id'])) {
			if (!empty($data['filter_sub_blogcategory'])) {
				$sql .= " FROM " . DB_PREFIX . "oct_blogcategory_path bcp LEFT JOIN " . DB_PREFIX . "oct_blogauthor_to_category a2c ON (bcp.blogcategory_id = a2c.blogcategory_id)";
			} else {
				$sql .= " FROM " . DB_PREFIX . "oct_blogauthor_to_category a2c";
			}

			$sql .= " LEFT JOIN " . DB_PREFIX . "oct_blogauthor a ON (a2c.blogauthor_id = a.blogauthor_id)";
		} else {
			$sql .= " FROM " . DB_PREFIX . "oct_blogauthor a";
		}

		$sql .= "
			LEFT JOIN
				" . DB_PREFIX . "oct_blogauthor_description ad
				ON (a.blogauthor_id = ad.blogauthor_id)
			LEFT JOIN
				" . DB_PREFIX . "oct_blogauthor_to_store a2s
				ON (a.blogauthor_id = a2s.blogauthor_id)
			WHERE
				ad.language_id = '" . (int)$this->config->get('config_language_id') . "'
				AND a.status = '1'
				AND a2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
		";

		if (!empty($data['filter_blogcategory_id'])) {
			if (!empty($data['filter_sub_blogcategory'])) {
				$sql .= " AND bcp.blog_path_id = '" . (int)$data['filter_blogcategory_id'] . "'";
			} else {
				$sql .= " AND a2c.blogcategory_id = '" . (int)$data['filter_blogcategory_id'] . "'";
			}
		}

		if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
			$sql .= " AND (";

			if (!empty($data['filter_name'])) {
				$implode = [];

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

				foreach ($words as $word) {
					$implode[] = "ad.name LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}

				if (!empty($data['filter_description'])) {
					$sql .= " OR ad.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
				}
			}

			if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
				$sql .= " OR ";
			}

			if (!empty($data['filter_tag'])) {
				$implode = [];

				$words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

				foreach ($words as $word) {
					$implode[] = "ad.tag LIKE '%" . $this->db->escape($word) . "%'";
				}

				if ($implode) {
					$sql .= " " . implode(" AND ", $implode) . "";
				}
			}

			$sql .= ")";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}
}