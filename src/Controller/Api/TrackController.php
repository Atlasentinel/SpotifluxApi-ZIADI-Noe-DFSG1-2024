<?php

namespace App\Controller\Api;

use App\Entity\Track;
use App\Repository\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[OA\Tag(name:"Track")]
class TrackController extends AbstractController
{
    public function __construct(
        private TrackRepository $trackRepository,
        private EntityManagerInterface $emi,
        private SerializerInterface $serializerInterface
    ) {
    }

    #[Route('/api/tracks', name: 'app_api_track', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Track::class, groups: ['read']))
        )
    )]
    public function index(PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $tracks = $this->trackRepository->getAllTrackQuery();
        $data = $paginator->paginate(
            $tracks,
            $request->query->get('page', 1),
            5
        );


        return $this->json([
            'tracks' => $data,
            'currentPageNumber' => $data->getCurrentPageNumber()
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/track/show/{id}', name: 'app_api_track_get')]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Track::class, groups: ['read'])
    )]
    public function get(?Track $track = null): JsonResponse
    {
        if (!$track) {
            return $this->json(['error' => "Ressource does not exist"], 404);
        }

        return $this->json([
            'track' => $track,
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/track', name: 'app_api_track_post', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Track::class, groups: ['read'])
    )]
    public function add(#[MapRequestPayload('json', ['groups' => ['create']])] Track $track): JsonResponse
    {
        $this->emi->persist($track);
        $this->emi->flush();

        return $this->json($track, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/track/{id}', name: 'app_api_track_update', methods: ['PUT'])]
    #[OA\Put(
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(
                    type: Track::class,
                    groups: ['update']
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Track::class, groups: ['read'])
    )]
    public function update(?Track $track, Request $request): JsonResponse
    {

        if (!$track) {
            return $this->json(['error' => "Ressource does not exist"], 404);
        }

        $data = $request->getContent();
        $this->serializerInterface->deserialize(
            $data,
            Track::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $track
            ]
        );

        $this->emi->flush();

        return $this->json($track, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/track/{id}', name: 'app_api_track_delete', methods: ['DELETE'])]
    public function delete(?Track $track): JsonResponse
    {

        if (!$track) {
            return $this->json(['error' => "Ressource does not exist"], 404);
        }

        $this->emi->remove($track); 
        $this->emi->flush();

        return $this->json([
            'message' => 'Movie deleted successfully'
        ], 200);
    }
}
