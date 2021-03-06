<?php
/**
 * Part of og project. 
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Opengraph\Model;

use Facebook\FacebookAuthorizationException;
use Facebook\FacebookSDKException;
use Joomla\Http\HttpFactory;
use Opengraph\Analysis\FacebookAnalysis;
use Opengraph\Helper\DateHelper;
use Opengraph\Table\Table;
use PHPHtmlParser\Dom;
use Windwalker\Core\Model\DatabaseModel;
use Windwalker\Data\Data;
use Windwalker\DataMapper\DataMapper;
use Windwalker\Ioc;

/**
 * The DebugModel class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class DebugModel extends DatabaseModel
{
	/**
	 * save
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public function save($url)
	{
		$data = $this->get($url);

		if ($data->url && $data->graph_id && !$this['fb.refresh'])
		{
			return $data;
		}

		if (!$data->url || !$data->graph_id || $this['fb.refresh'])
		{
			$fb = Ioc::getFBAnalysis();

			$data->error_msg = null;

			try
			{
				// Use post to refresh data
				$fb->get($url, $fb::POST);
			}
			catch (FacebookAuthorizationException $e)
			{
				$response = $e->getResponse();

				$data->error_msg = json_encode($response);

				// Fallback to get old data.
				$fb->get($url, $fb::GET);
			}

			$object = $fb->getGraphObject();

			$data->graph_id = $object->getProperty('id');
			$data->graph_object = json_encode($object->asArray());

			$http = HttpFactory::getHttp();
			$response = $http->get($url);

			if ($response->code != 200)
			{
				throw new \RuntimeException('網址無法存取');
			}

			$data->url = $url;
			$data->html = $response->body;
		}

		$data->last_search = DateHelper::format('now');
		$data->searches += 1;

		// Create
		if (!$data->id)
		{
			$data->created = DateHelper::format('now');
		}
		// Update
		else
		{

		}

		$this->getDataMapper()->saveOne($data, 'id', DataMapper::UPDATE_NULLS);

		return true;
	}

	/**
	 * get
	 *
	 * @param string $url
	 *
	 * @return  Data
	 */
	public function get($url)
	{
		$mapper = $this->getDataMapper();

		if (!$url)
		{
			return new Data;
		}

		return $mapper->findOne(['url' => $url]);
	}

	/**
	 * getDataMapper
	 *
	 * @return  DataMapper
	 */
	protected function getDataMapper()
	{
		return new DataMapper(Table::RESULTS);
	}
}
