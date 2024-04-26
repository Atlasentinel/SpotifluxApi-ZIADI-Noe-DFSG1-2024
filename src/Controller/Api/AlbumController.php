<?php

namespace App\Controller\Api;

use App\Entity\Album;
use App\Repository\AlbumRepository;
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

#[OA\Tag(name:"Album")]
class AlbumController extends AbstractController
{
    public function __construct(
        private AlbumRepository $albumRepository,
        private EntityManagerInterface $emi,
        private SerializerInterface $serializerInterface
    ) {
    }

    #[Route('/api/albums', name: 'app_api_album', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Album::class, groups: ['read']))
        )
    )]
    public function index(PaginatorInterface $paginator, Request $request): JsonResponse
    {
        $albums = $this->albumRepository->getAllAlbumsQuery ();
        $data = $paginator->paginate(
        $albums,
        $request->query->get('page',1),
        5
        );


        return $this->json([
            'albums' => $data,
            'currentPageNumber' => $data->getCurrentPageNumber()
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/album/show/{id}', name: 'app_api_album_get')]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Album::class, groups: ['read'])
    )]
    public function get(?Album $album = null): JsonResponse
    {
        if (!$album) {
            return $this->json(['error' => "Ressource does not exist"], 404);
        }

        return $this->json([
            'album' => $album,
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/album', name: 'app_api_album_post', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Album::class, groups: ['read'])
    )]
    public function add(#[MapRequestPayload('json', ['groups' => ['create']])] Album $album): JsonResponse
    {
        $this->emi->persist($album);
        $this->emi->flush();

        return $this->json($album, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/album/{id}', name: 'app_api_album_update', methods: ['PUT'])]
    #[OA\Put(
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(
                    type: Album::class, 
                    groups: ['update']
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Album::class, groups: ['read'])
    )]
    public function update(?Album $album, Request $request): JsonResponse
    {

        if (!$album) {
            return $this->json(['error' => "Ressource does not exist"], 404);
        }

        $data = $request->getContent();
        $this->serializerInterface->deserialize(
            $data,
            Album::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $album
            ]
        );

        $this->emi->flush();

        return $this->json($album, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/album/{id}', name: 'app_api_album_delete', methods: ['DELETE'])]
    public function delete(?Album $album): JsonResponse
    {

        if (!$album) {
            return $this->json(['error' => "Ressource does not exist"], 404);
        }

        $this->emi->remove($album);
        $this->emi->flush();

        return $this->json([
            'message' => 'Movie deleted successfully'
        ], 200);
    }
}
