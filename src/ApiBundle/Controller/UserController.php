<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use ApiBundle\Entity\AppUser;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class UserController extends Controller
{
    protected $cats = ['langue', 'sport', 'lifestyle', 'musique', 'creativite', 'culture', 'sorties'];

    /**
     * @Route("/newUser")
     * @Method("GET")
     * @ApiDoc(
     *   description="Creates a new User in the database and returns its unique id",
     *   statusCodes={
     *     200="User created"
     *  }
     * )
     */
    public function newUserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $appUser = new AppUser();
        $appUser->setIdentifier(hash('sha256', time().date('Y-m-d:H:i').microtime()));

        $em->persist($appUser);
        $em->flush();

        return new JsonResponse(['code' => 200, 'user' => $appUser->getIdentifier()]);
    }

    /**
     * @Route("/updateUser/{identifier}")
     * @Method("POST")
     * @ApiDoc(
     *   description="Updates a user hobbies in the database",
     *   parameters={
     *     {"name"="infos", "dataType"="array", "required"=true, "description"="Contains all the user hobbies array"},
     *     {"name"="infos[age]", "dataType"="string", "required"=true, "description"="Age range of the user"},
     *     {"name"="infos[handicap]", "dataType"="boolean", "required"=true, "description"="Is the user disabled"}
     *   },
     *   statusCodes={
     *     200="Successfully updated the user infos",
     *     404="User not found"
     *   }
     * )
     */
    public function updateUserAction($identifier)
    {
        if(!isset($_POST['infos']) || empty($_POST['infos']))
            return new JsonResponse(['code' => 418, 'message' => 'I\'m a teapot']);

        $infos = $_POST['infos'];

        $age = $infos['age'];
        unset($infos['age']);
        $handicap = $infos['handicap'];
        unset($infos['handicap']);

        $valid = true;
        foreach ($infos as $key => $value)
            if(!in_array($key, $this->cats))
                $valid = false;


        if(!$valid)
            return new JsonResponse(['code' => 418, 'message' => 'I\'m a teapot']);

        $em = $this->getDoctrine()->getManager();
        $u = $em->getRepository('ApiBundle:AppUser')->findOneByIdentifier($identifier);

        if(!$u)
            return new JsonResponse(['code' => 404, 'message' => 'User not found']);

        $u->setInfos($infos);
        $u->setHandicap(boolval($handicap));
        $u->setAge($age);
        $em->flush();

        return new JsonResponse(['code' => 200]);
    }
}
