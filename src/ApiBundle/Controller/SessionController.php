<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use ApiBundle\SessionManager\SessionManager;

use ApiBundle\Entity\AppUser;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

function shuffle_assoc(&$array) {
        $keys = array_keys($array);

        shuffle($keys);

        foreach($keys as $key) {
            $new[$key] = $array[$key];
        }

        $array = $new;

        return true;
    }

class SessionController extends Controller
{
    /**
     * @Route("/sessions/start")
     * @Method("POST")
     * @ApiDoc(
     *   description="Starts a session for a given user and poi",
     *   parameters={
     *     {"name"="user", "dataType"="string", "required"=true, "description"="Unique identifier of the user"},
     *     {"name"="poi", "dataType"="integer", "required"=true, "description"="ID of the PoI the user is on"}
     *   },
     *   statusCodes={
     *     200="Session created",
     *     404={
     *       "User not found",
     *       "PoI not found"
     *     }
     *   }
     * )
     */
    public function startAction(Request $r)
    {
        if(!$r->get('user') || !$r->get('poi'))
            return new JsonResponse(['code' => 418, 'message' => 'I\'m a teapot']);

        $em = $this->getDoctrine()->getManager();

        $aurep = $em->getRepository('ApiBundle:AppUser');
        if(!$user = $aurep->findOneByIdentifier($r->get('user')))
            return new JsonResponse(['code' => 404, 'message' => 'User not found, unauthorized']);

        $prep = $em->getRepository('AppBundle:Poi');
        if(!$poi = $prep->find($r->get('poi')))
            return new JsonResponse(['code' => 404, 'message' => 'Poi not found, unauthorized']);

        $sid = SessionManager::StartSession($user, $poi);

        return new JsonResponse(['code' => 200, 'sid' => $sid]);
    }

    /**
     * @Route("/sessions/stop")
     * @Method("POST")
     * @ApiDoc(
     *   description="Stops a given session",
     *   parameters={
     *     {"name"="sid", "dataType"="integer", "required"=true, "description"="ID of the session"}
     *   },
     *   statusCodes={
     *     200="Session stopped",
     *   }
     * )
     */
    public function stopAction(Request $r)
    {
        if(!$r->get('sid'))
            return new JsonResponse(['code' => 418, 'message' => 'I\'m a teapot']);

        SessionManager::StopSession($r->get('sid'));

        return new JsonResponse(['code' => 200]);
    }

    /**
     * @Route("/sessions/heartbeat")
     * @Method("POST")
     * @ApiDoc(
     *   description="Sends a heartbeat to a given session to notify it still is alive (user is still on the PoI)",
     *   parameters={
     *     {"name"="sid", "dataType"="integer", "required"=true, "description"="ID of the session"}
     *   },
     *   statusCodes={
     *     200="Heartbeat sent",
     *   }
     * )
     */
    public function heartbeatAction(Request $r)
    {
        if(!$r->get('sid'))
            return new JsonResponse(['code' => 418, 'message' => 'I\'m a teapot']);

        SessionManager::SendHeartBeatToSession($r->get('sid'));

        return new JsonResponse(['code' => 200]);
    }

    /**
     * @Route("/genSessions")
     * @Method("GET")
     * @ApiDoc(
     *   description="Generates sessions (SHOULD NOT BE USED IN A PRODUCTION ENVIRONMENT)",
     *   statusCodes={
     *     200="Sessions created successfully",
     *   }
     * )
     */
    public function genSessionsAction()
    {
        if(!in_array($_SERVER['REMOTE_ADDR'], ["147.210.245.180", "46.193.16.47", "82.127.192.191"]))
            return new JsonResponse(['code' => 418, 'message' => 'I\'m a teapot']);

        set_time_limit(0);

        $em = $this->getDoctrine()->getManager();
        $prep = $em->getRepository('AppBundle:Poi');
        $allpois = $prep->findAll();

        // $exclude = [45,46,47,48,49];
	$include_only = [29, 10, 7, 9, 8, 5, 6, 4, 3, 36, 2, 1, 40, 30, 31, 32];

        $pois = [];
        //foreach ($allpois as $poi)
            //if(!in_array($poi->getId(), $exclude))
        foreach ($allpois as $poi)
            if(in_array($poi->getId(), $include_only))
                $pois[] = $poi;

        if(!count($pois))
		return new JsonResponse(['code' => 404, 'message' => 'No poi found']);

        $possibleprefs = json_decode('{
    "langue":{
          "fr":{
              "text": "Français"
          },
          "en":{
              "text": "Anglais"
          },
          "esp":{
              "text": "Espagnol"
          },
          "de":{
              "text": "Allemand"
          },
          "it":{
              "text": "Italien"
          },
          "chi":{
              "text": "Chinois"
          },
          "jap":{
              "text": "Japonais"
          }
    },
      "sport": {
            "foot": {
                "text": "Foot"
            },
            "rugby": {
                "text": "Rugby"
            },
          "hand":{
              "text": "Handball"
          },
          "basket":{
              "text": "Basket"
          },
          "bad":{
              "text": "Badminton"
          },
          "boxe":{
              "text": "Boxe"
          },
          "escalade":{
              "text": "Escalade"
          },
          "muscu":{
              "text": "Musculation"
          },
          "natation":{
              "text": "Natation"
          },
          "escrime":{
              "text": "Escrime"
          },
          "equitation":{
              "text": "Equitation"
          },
          "kayak":{
              "text": "Kayak"
          },
          "ping":{
              "text": "Ping-pong"
          },
          "fitness":{
              "text": "Fitness"
          }
    },
      "lifestyle": {
          "ecolo": {
                "text": "Ecologie"
            },
          "human": {
                "text": "Humanitaire"
            },
          "vegeta": {
                "text": "Vegetarien"
            },
          "vegan": {
                "text": "Vegan"
            },
          "collec": {
                "text": "Collectionneur"
            },
          "hipster": {
                "text": "Hipster"
            }
    },
      "musique": {
          "pop": {
                     "text": "Pop"
              },
          "rock": {
                     "text": "Rock"
              },
          "rap": {
                     "text": "Rap"
              },
          "jazz": {
                     "text": "Jazz"
              },
          "variete": {
                     "text": "Variété"
              },
          "electro": {
                     "text": "Électro"
              },
          "edm": {
                     "text": "EDM"
              },
          "raggea": {
                     "text": "Raggea"
              },
          "folk": {
                     "text": "Folk"
              },
          "country": {
                     "text": "Country"
              },
          "metal": {
                     "text": "Metal"
              },
          "afro": {
                     "text": "Afro-Beat"
              }
    },
      "creativite": {
            "brico":{
              "text": "Bricolage"
          },
          "photogra":{
              "text": "Photographie"
          },
          "video":{
              "text": "Vidéo"
          },
          "peinture":{
              "text": "Peinture"
          },
          "dessin":{
              "text": "Dessin"
          },
          "music":{
              "text": "Musique"
          },
          "sculpture":{
              "text": "Sculpture"
          },
          "anim":{
              "text": "Animation 2D/3D"
          },
          "jardin":{
              "text": "Jardinage"
          },
          "cook":{
              "text": "Cuisine"
          },
          "ecrire":{
              "text": "Ecriture"
          }
    },
      "culture":{
          "cinema":{
              "text": "Cinéma"
          },
          "musee":{
              "text": "Musée"
          },
          "tv":{
              "text": "Télévision"
          },
          "mode":{
              "text": "Mode"
          },
          "techno":{
              "text": "Nouvelle Technologie"
          },
          "games":{
              "text": "Jeu Vidéos"
          },
          "art":{
              "text": "Art"
          },
          "lecture":{
              "text": "Lecture"
          }
      },
      "sorties":{
          "voyage":{
              "text": "Voyage"
          },
          "randonnee":{
              "text": "Randonnée"
          },
          "festival":{
              "text": "Festival"
          },
          "concert":{
              "text": "Concert"
          },
          "bar":{
              "text": "Bar"
          },
          "theatre":{
              "text": "Théâtre"
          },
          "opera":{
              "text": "Opéra"
          }
      }
}', true);

        foreach (range(0,7) as $i) {
            // Create a new user
            $u = new AppUser();

            // Set it's unique identifier
            $u->setIdentifier("fake-".hash('sha256', time().date('Y-m-d:H:i').microtime()));

            // Set user preferences (random)
            $prefs = [];
            foreach ($possibleprefs as $cat => $pref) {
                $tmp = $pref;
                shuffle_assoc($tmp);
                $tmp2 = [];
                foreach ($tmp as $key => $value) {
                    $tmp2[] = $key;
                }
                $o = 0;
                $prefs[$cat] = array_slice($tmp2, 0, 7);
            }
            $u->setInfos($prefs);
            // echo "<pre>";
            // var_dump($u->getInfos());
            // exit();

            // Add a certain amount of sessions for random pois for this user
            foreach (range(0,5) as $o) {
                $poi = $pois[array_rand($pois)];
                $sid = SessionManager::StartSession($u, $poi);
                SessionManager::StopSession($sid);
            }
        }
        return new JsonResponse(['code' => 200]);
    }

    /**
     * @Route("/closeExpiredSessions")
     * @Method("GET")
     * @ApiDoc(
     *   description="Closes expired sessions from elasticsearch Database",
     *   statusCodes={
     *     200="Success"
     *   }
     * )
     */
    public function closeExpiredSessionsAction()
    {
        $client = SessionManager::_getClient();
        $query = json_decode('{
  "query": {
    "bool": {
      "must_not": {
        "exists": {
          "field": "stop"
        }
      },
      "must": [
        {
          "range": {
            "last_heartbeat": {
              "lte": "now-20m",
              "time_zone": "+01:00"
            }
          }
        }
      ]
    }
  }
}');
        $query = [
            'index' => 'app',
            'type' => 'session',
            'body' => $query
        ];
        $ret = $client->search($query);

        if(0 !== $ret['hits']['total']) {
            $query = ['body' => []];
            foreach ($ret['hits']['hits'] as $hit) {
                $query['body'][] = ['index' => ['_index' => 'app', '_type' => 'session', '_id' => $hit['_id']]];
                $query['body'][] = ['stop' => date('Y-m-d H:i:s O')];
            }
            $client->bulk($query);
        }

        return new JsonResponse(['code' => 200]);
    }
}
