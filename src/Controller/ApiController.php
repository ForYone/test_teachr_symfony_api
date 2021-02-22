<?php

namespace App\Controller;

use App\Entity\Statistics;
use App\Entity\Teachr;
use App\Repository\TeachrRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api", name="api",methods={"GET"})
     */
    public function index(TeachrRepository $teachrRepository, SerializerInterface $serializer): Response
    {
        return $this->json($teachrRepository->findAll(), 200, []);

    }

    /**
     * @Route("/api",name="api_new_teachr",methods={"PUT"})
     */
    public function newTeacher(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,ValidatorInterface $validator,TeachrRepository $teachrRepository)
    {
        $jsonRecu = $request->getContent();

        try {

            $post = $serializer->deserialize($jsonRecu, Teachr::class, 'json');
            $post->setDate(new \DateTime());

            $errors = $validator->validate($post);

            if(count($errors)>0){
                return $this->json($errors,400);
            }

            $em->persist($post);
            $em->flush();

            /** Compteur */
            $cpt = new Statistics();
            $cpt->setCompteur(count($teachrRepository->findAll()));

            $em->persist($cpt);
            $em->flush();
            /************* */

            return $this->json($post, 201, []);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status'=> 400,
                'message'=> $e->getMessage()
            ],400);
        }

    }
}
