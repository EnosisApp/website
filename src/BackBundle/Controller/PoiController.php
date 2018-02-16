<?php

namespace BackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ApiBundle\SessionManager\SessionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use AppBundle\Entity\Poi;
use AppBundle\Entity\Beacon;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}

class PoiController extends Controller
{
	// List of possible PoI categories
	protected $choices = [
		'Machine à café' => 'cofee',
		'Pôle santé' => 'health',
		'Distributeur de billets' => 'dab',
		'Laverie' => 'laverie',
		'Services d\'impression' => 'printing',
		'Bar' => 'bar',
		'Restaurant' => 'restaurant',
		'Resto U' => 'restaurant-u',
		'Association' => 'asso',
		'Bibliothèque' => 'library',
		'Lieu culturel' => 'cultural-place',
		'Lieu de détente' => 'relaxation-place',
		'Défibrilateur' => 'defibrillator',
		'Autre' => 'default'
	];

	/**
	 * @Route("/pois", name="back_pois")
	 * @Template
	 * Lists of all PoIs
	 */
	public function listAction()
	{
		return ['pois' => $this->getDoctrine()->getManager()->getRepository('AppBundle:Poi')->findAll()];
	}

	/**
	 * @Route("/pois/add", name="back_pois_add")
	 * @Template
	 * Add a PoI
	 */
	public function addAction(Request $r)
	{
		$poi = new Poi();


		$form = $this->createFormBuilder($poi)
			->add('caption', TextType::class, ['label' => 'Nom du lieu', 'attr' => ['class' => 'form-control']])
			->add('address', TextType::class, ['label' => 'Adresse', 'attr' => ['class' => 'form-control', 'required' => true]])
			->add('lat', TextType::class, ['attr' => ['class' => 'form-control']])
			->add('lon', TextType::class, ['attr' => ['class' => 'form-control']])
			->add('type', ChoiceType::class, ['label' => 'Type d\'endroit', 'choices' => $this->choices, 'attr' => ['class' => 'form-control']])
			->add('city', TextType::class, ['label' => 'Ville', 'attr' => ['class' => 'form-control']])
			->add('pushNotification', TextType::class, ['label' => 'Notification push (laisser vide pour ne pas envoyer de push):', 'attr' => ['class' => 'form-control'], 'required' => false])
			->getForm();

		$form->handleRequest($r);
		if($form->isSubmitted() && $form->isValid()) {
			$ret = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyCM8kPEg_3rSvQvZDrr5-rcuzterWeVSP4&address=".$poi->getCity());
	        $ret = json_decode($ret);

	        if("OK" == $ret->status) {
	            $poi->setCityLat($ret->results['0']->geometry->location->lat);
	            $poi->setCityLon($ret->results['0']->geometry->location->lng);
	        }

			$em = $this->getDoctrine()->getManager();
			$this->getUser()->addPoi($poi);
			$em->persist($poi);
			$em->flush();

			return $this->redirect($this->generateUrl('back_pois_edit', array('id' => $poi->getId())));
		}

		return ['form' => $form->createView()];
	}

	/**
 	 * @Route("/pois/edit/{id}", name="back_pois_edit")
 	 * @ParamConverter("poi", class="AppBundle:Poi")
 	 * @Template
 	 * Edit a PoI
 	 */
	public function editAction(Request $r, Poi $poi)
	{
		$form = $this->createFormBuilder($poi)
			->add('caption', TextType::class, ['label' => 'Nom du lieu', 'attr' => ['class' => 'form-control']])
			->add('address', TextType::class, ['label' => 'Adresse', 'attr' => ['class' => 'form-control', 'required' => true]])
			->add('lat', TextType::class, ['attr' => ['class' => 'form-control']])
			->add('lon', TextType::class, ['attr' => ['class' => 'form-control']])
			->add('type', ChoiceType::class, ['label' => 'Type d\'endroit', 'choices' => $this->choices, 'attr' => ['class' => 'form-control']])
			->add('city', TextType::class, ['label' => 'Ville', 'attr' => ['class' => 'form-control']])
			->add('pushNotification', TextType::class, ['label' => 'Notification push (laisser vide pour ne pas envoyer de push):', 'attr' => ['class' => 'form-control'], 'required' => false])
			->getForm();

		$form->handleRequest($r);
		if($form->isSubmitted() && $form->isValid()) {
			$ret = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyCM8kPEg_3rSvQvZDrr5-rcuzterWeVSP4&address=".$poi->getCity());
	        $ret = json_decode($ret);

	        if("OK" == $ret->status) {
	            $poi->setCityLat($ret->results['0']->geometry->location->lat);
	            $poi->setCityLon($ret->results['0']->geometry->location->lng);
	        }
	        
			$em = $this->getDoctrine()->getManager();
			$em->flush();
		}

		$json = json_decode(file_get_contents('http://api.enosisapp.fr/api/v2/getPoi/' . $poi->getId()));
		$infos = objectToArray($json->infos);
		$interests = objectToArray(json_decode(file_get_contents(__DIR__ . '/interests.json')));

		return ['form' => $form->createView(), 'poi' => $poi, 'infos' => $infos, 'interests' => $interests];
	}

	/**
 	 * @Route("/pois/remove/{id}", name="back_pois_remove")
 	 * @ParamConverter("poi", class="AppBundle:Poi")
 	 * @Template
 	 * Remove a PoI
 	 */
	public function removeAction(Request $r, Poi $poi)
	{
		if($r->get('confirm')) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($poi);
			$em->flush();
			return $this->redirect($this->generateUrl('back_pois'));
		}
		return ['poi' => $poi];
	}

	/**
 	 * @Route("/pois/{poiid}/beacons/add", name="back_pois_beacons_add")
 	 * @ParamConverter("poi", class="AppBundle:Poi", options={"id" = "poiid"})
 	 * Add a beacon to a PoI
 	 */
	public function addBeaconAction(Request $r, Poi $poi)
	{
		// if($poi->getOwner() != $this->getUser())
			// return $this->redirect($this->generateUrl('back_pois'));

		$b = new Beacon();
		$b->setCaption($r->get('caption'));
		$b->setBtName($r->get('btName'));
		$poi->addBeacon($b);

		$em = $this->getDoctrine()->getManager();
		$em->persist($b);
		$em->flush();

		SessionManager::updateBeaconsInElasticForPoi($poi);

		return $this->redirect($this->generateUrl('back_pois_edit', array('id' => $poi->getId())));
	}

	/**
 	 * @Route("/pois/{poiid}/beacons/remove/{id}", name="back_pois_beacons_remove")
 	 * @ParamConverter("b", class="AppBundle:Beacon")
 	 * Remove a beacon from a PoI
 	 */
	public function removeBeaconAction(Beacon $b)
	{
		// if($b->getPoi()->getOwner() != $this->getUser())
			// return $this->redirect($this->generateUrl('back_pois'));

		$em = $this->getDoctrine()->getManager();
		$em->remove($b);
		$em->flush();
		
		SessionManager::updateBeaconsInElasticForPoi($b->getPoi());

		return $this->redirect($this->generateUrl('back_pois_edit', array('id' => $b->getPoi()->getId())));
	}
}
