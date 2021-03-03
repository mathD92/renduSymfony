<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Token;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{
    /**
     * @Route("/home", name="homeC")
     */
    public function index(): Response
    {
        return $this->render('media/index.html.twig', [
            'controller_name' => 'MediaController',
        ]);
    }
    /**
     * @Route("/media", name="allMedia")
     */
    public function getAllMedia(): Response
    {
        $media = $this->getDoctrine()
            ->getRepository(Media::class)
            ->findAll();

        $status = Response::HTTP_OK;

        if (empty($media)) {
            $media = ['error' => 'sorry, no media found'];
            $status = Response::HTTP_NOT_FOUND;
        }

        $media = $this->get('serializer')->serialize($media, 'json');

        $response = new Response($media);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($status);
        return $response;
    }


    /**
     * @Route("/media/create", name="addMedia", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function addMedia(Request $request): Response
    {
        $datas = json_decode($request->getContent(),true);
        if (!empty($datas["token"])) {

            $token = $this->getDoctrine()
                ->getRepository(Token::class)
                ->findOneBy(['token' => $datas["token"]]);
            $status = Response::HTTP_CREATED;
            $content = $token;

            if ($token) {
                if (!empty($datas['nom']) && !empty($datas['synopsis']) && !empty($datas['type'])) {
                    if ($datas['type'] === 'film' || $datas['type'] === 'serie') {
                        $entityManager = $this->getDoctrine()->getManager();
                        $media = new Media();
                        $media->setNom($datas['nom'])
                            ->setSynopsis($datas['synopsis'])
                            ->setType($datas['type'])
                            ->setCreatedAt(new \DateTime('now'));
                        $entityManager->persist($media);
                        $entityManager->flush();
                        $status = Response::HTTP_CREATED;
                        $content = "Media created  at id : " . $media->getId();
                    } else {
                        $status = Response::HTTP_BAD_REQUEST;
                        $content = " need to be  'film' or 'serie'";
                    }
                } else {
                    $status = Response::HTTP_BAD_REQUEST;
                    $content = "Need Field 'nom', 'synopsis' or 'type' ";
                }
            } else {
                $status = Response::HTTP_BAD_REQUEST;
                $content = "Field 'token' is not valid";
            }
        } else {
            $status = Response::HTTP_BAD_REQUEST;
            $content = "Field 'token' is needed, please go to /token to get one";
        }

        $response = new Response();
        $response->setStatusCode($status);
        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/media/{id}", name="MediaById")
     * @param Request $request
     * @return Response
     */
    public function getOneMedia(Request $request): Response
    {
        $id = $request->attributes->get('id');
        if (!empty($id) && is_numeric($id)) {
            $media = $this->getDoctrine()
                ->getRepository(Media::class)
                ->findOneBy(['id' => $id,]);
            $status = Response::HTTP_ACCEPTED;
            if ($media === null || empty($media)) {
                $media = ['error' => 'sorry, no media found with this id'];
                $status = Response::HTTP_NOT_FOUND;
            }
        } else {
            $media = ['error' => 'sorry, the argument you pass is not valid, please pass a valid media id'];
            $status = Response::HTTP_BAD_REQUEST;
        }
        $media = $this->get('serializer')->serialize($media, 'json');
        $response = new Response($media);
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($status);
        return $response;
    }

}
