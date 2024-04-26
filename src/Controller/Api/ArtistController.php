<?php

namespace App\Controller\Api;

use App\Entity\Artist;
use App\Repository\ArtistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[OA\Tag(name:"Artist")]
class ArtistController extends AbstractController
{
    public function __construct(
        private ArtistRepository $artistRepository,
        private EntityManagerInterface $emi,
        private SerializerInterface $serializerInterface
    ) {
    }

    #[Route('/api/artists', name: 'app_api_artist', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Artist::class, groups: ['read']))
        )
    )]
    public function index(PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $artists = $this->artistRepository->getAllArtistsQuery();
        $data = $paginator->paginate(
            $artists,
            $request->query->get('page', 1),
            5
        );


        return $this->json([
            'artists' => $data,
            'currentPageNumber' => $data->getCurrentPageNumber()
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/artist/show/{id}', name: 'app_api_artist_get')]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Artist::class, groups: ['read'])
    )]
    public function get(?Artist $artist = null): JsonResponse
    {
        if (!$artist) {
            return $this->json(['error' => "Ressource does not exist"], 404);
        }

        return $this->json([
            'artist' => $artist,
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/artist', name: 'app_api_artist_post', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Artist::class, groups: ['read'])
    )]
    public function add(#[MapRequestPayload('json', ['groups' => ['create']])] Artist $artist): JsonResponse
    {
        $this->emi->persist($artist);
        $this->emi->flush();

        return $this->json($artist, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/artist/{id}', name: 'app_api_artist_update', methods: ['PUT'])]
    #[OA\Put(
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(
                    type: Artist::class,
                    groups: ['update']
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Artist::class, groups: ['read'])
    )]
    public function update(?Artist $artist, Request $request): JsonResponse
    {

        if (!$artist) {
            return $this->json(['error' => "Ressource does not exist"], 404);
        }

        $data = $request->getContent();
        $this->serializerInterface->deserialize(
            $data,
            Artist::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $artist
            ]
        );

        $this->emi->flush();

        return $this->json($artist, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/artist/{id}', name: 'app_api_artist_delete', methods: ['DELETE'])]
    public function delete(?Artist $artist): JsonResponse
    {

        if (!$artist) {
            return $this->json(['error' => "Ressource does not exist"], 404);
        }

        $this->emi->remove($artist); 
        $this->emi->flush();

        return $this->json([
            'message' => 'Movie deleted successfully'
        ], 200);
    }
}
