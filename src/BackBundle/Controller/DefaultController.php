<?php

namespace BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ApiBundle\SessionManager\SessionManager;

class DefaultController extends Controller
{
	/**
	 * @Route("/", name="back_dashboard")
	 * @Template
     * Dashboard
	 */
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getManager();

    	return [
    		'poiCount' => $em->getRepository('AppBundle:Poi')->findCount(),
    		'appUserCount' => $em->getRepository('ApiBundle:AppUser')->findCount(),
    		'currentSessionsCount' => SessionManager::GetOpenedSessions()['count'],
    		'sessionsOverDays' => SessionManager::GetSessionsCountOverDays()['aggregations']['sessions']['buckets']
    	];
    }

	/**
	 * @Route("/heatmap", name="back_heatmap")
	 * @Template
     * Heatmap
	 */
	public function heatmapAction()
	{
		$prefs = json_decode(file_get_contents(__DIR__ . '/interests.json'), true);
		return ['prefs' => $prefs];
	}

	/**
	 * @Route("/heatmap/api", name="back_heatmap_api")
     * Heatmap
	 */
	public function heatmapApiAction()
	{
		$prefs = [];
		foreach($_POST as $cat => $entries) {
			$prefs['prefs.' . $cat] = [];
			foreach($entries as $interest => $on)
				array_push($prefs['prefs.' . $cat],  $interest);
		}

		$should = [];
		foreach($prefs as $cat => $pref)  {
			$should[] = [
				'terms' => [
					$cat => $pref
				]
			];
		}


		$client =  SessionManager::_getClient();

		$aggs = $client->search([
			'index' => 'app',
			'type' => 'session',
			'body' => [
				'size' => 0,
				'query' => [
					'nested' => [
						'path' => 'prefs',
						'query' => [
							'bool' => [
								'must' => $should
							]
						]
					]
				],
				'aggs' => [
					'poi' => [
						'terms' => [
							'script' => 'doc.location',
							'size' => 1000000
						]
					]
				]
			]
		]);

		return new JsonResponse($aggs['aggregations']['poi']['buckets']);
	}
}
