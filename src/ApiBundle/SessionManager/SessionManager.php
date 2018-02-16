<?php

namespace ApiBundle\SessionManager;

use ApiBundle\Utils\Curl;
use AppBundle\Entity\Poi;

class SessionManager
{
	public static function _getClient()
	{
		return \Elasticsearch\ClientBuilder::create()->setHosts(['ns2.enosisapp.fr:9200'])->build();
	}

	/**
	 * Starts a session for the user
	 * @return integer ID of the created session
	 */
	public static function StartSession($user, $poi)
	{
		$client = self::_getClient();
		
		$data = [
			'index' => 'app',
			'type' => 'session',
			'body' => [
					'user' => $user->getIdentifier(),
					'poi' => $poi->getId(),
					'location' => [
						'lat' => $poi->getLat(),
						'lon' => $poi->getLon()
					],
					// 'start' => time()
					'start' => date('Y-m-d H:i:s O'),
					'handicap' => $user->getHandicap(),
					'age' => $user->getAge(),
					'prefs' => $user->getInfos()
				]
		];

		$ret = $client->index($data);

		return $ret['_id'];
	}

	/**
	 * Stops a session by ID
	 */
	public static function StopSession($sessionId)
	{
		$client = self::_getClient();

		$data = [
			'index' => 'app',
			'type' => 'session',
			'id' => $sessionId,
			'body' => [
				'doc' => [
					'stop' => date('Y-m-d H:i:s O')
					// 'stop' => time()
				]
			]
		];

		$client->update($data);
	}

	/**
	 * Notifies a session it's still alive
	 * (I will survive, yeah yeah)
	 */
	public static function SendHeartBeatToSession($sessionId)
	{
		$client = self::_getClient();

		$data = [
			'index' => 'app',
			'type' => 'session',
			'id' => $sessionId,
			'body' => [
				'doc' => [
					'last_heartbeat' => date('Y-m-d H:i:s O')
					// 'last_heartbeat' => time()
				]
			]
 		];

		$client->update($data);
	}

	/**
	 * Returns a bucket aggregation of sessions count over days for 1 month
	 */
	public static function GetSessionsCountOverDays()
	{
		$client = self::_getClient();
    	$data = [
			'index' => 'app',
			'type' => 'session',
			'body' => [
				'size' => 500,
				'query' => [
					'bool' => [
						'must' => [
							[
								'range' => [
									'start' => [
										'from' => date("Y-m-d 00:00:00 O", strtotime("-14 days", time())),
										'to' => date('Y-m-d 23:59:00 +0100')
									]
								]
							]
						]
					]
				],
				'aggs' => [
					'sessions' => [
						'date_histogram' => [
							'field' => 'start',
							'interval' => '1d',
							'time_zone' => 'Europe/Paris',
							'min_doc_count' => 0,
							'extended_bounds' => [
								'min' => date("Y-m-d 00:00:00 O", strtotime("-14 days", time())),
								'max' => date('Y-m-d 00:00:00 +0100')
							]
						]
					]
				],
				'stored_fields' => [
					'*'
				]
			],
		];
		return $client->search($data);
	}

	/**
	 * Returns count of currently open sessions
	 */
	public static function GetOpenedSessions()
	{
		$client = self::_getClient();
    	$data = [
			'index' => 'app',
			'type' => 'session',
			'body' => [
				'query' => [
					'bool' => [
						'must_not' => [
							'exists' => [
								'field' => "stop"
							]
						]
					]
				]
			],
		];
		return $client->count($data);
	}

	public function GetInfosFromPoi($poi, $cats)
	{
		$client = self::_getClient();
    	$data = [
			'index' => 'app',
			'type' => 'session',
			'body' => [
				'query' => [
					'function_score' => [
						'query' => [
							'bool' => [
								'must' => [
									'term' => [
										'poi' => (int)$poi->getId()
									]
								]
							]
						],
						'functions' => [
							[
								"gauss" => [
									"start" => [
										"origin" => date('Y-m-d 00:00:00 O'),
										'scale' => '5d',
										'offset' => '2d',
										'decay' => 0.5
									]
								]
							]
						],
						'boost_mode' => 'replace'
					]
				],
				'aggs' => [
					'interests' => [
						'nested' => [
							'path' => 'prefs'
						]
					],
					'age' => [
						'terms' => [
							'field' => 'age'
						],
						'aggs' => [
							'score' => [
								'sum' => [
									'script' => [
										'inline' => '_score'
									]
								]
							]
						]
					]
				]
			]
		];

		foreach ($cats as $key) {
			$data['body']['aggs']['interests']['aggs'][$key] = [
								'terms' => [
									'field' => 'prefs.'.$key
								],
								'aggs' => [
									'score' => [
										'sum' => [
											'script' => [
												'inline' => '_score'
											]
										]
									]
								]
							];
		}
		return $client->search($data);
	}

	public static function updateBeaconsInElasticForPoi(Poi $poi) {
		$params = [
			'index' => 'app',
			'type' => 'poi',
			'id' => $poi->getId()
		];
		$beacons = [];
		foreach($poi->getBeacons() as $beacon)
			$beacons[] = $beacon->getBtName();  

		$params['body'] = [
			'doc' => [
				'beacons' => $beacons
			]
		];
        self::_getClient()->update($params);
	}
}
