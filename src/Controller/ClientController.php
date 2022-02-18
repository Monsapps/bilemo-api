<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ClientController extends AbstractController
{
    /**
     * @Rest\Get("/clients", name="client_list")
     * 
     * @Rest\QueryParam(
     *      name="keyword",
     *      requirements="[a-zA-Z0-9]+",
     *      nullable=true,
     *      description="The keyword search for."
     * )
     * 
     * @Rest\QueryParam(
     *      name="order",
     *      requirements="(asc|desc)",
     *      default="asc",
     *      description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      default="15",
     *      description="Max users per page."
     * )
     * @Rest\QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      default="1",
     *      description="The page number."
     * )
     * 
     * @OA\Response(
     *      response=200,
     *      description="Get the list of all clients.",
     *      @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=App\Entity\User::class))
     *     )
     * )
     * @OA\Tag(name="Clients")
     */
    public function clientList(ParamFetcherInterface $paramFetch, UserService $userService): View
    {
        //TODO add client

        $users = $userService->getUserList($paramFetch, $this->getUser());

        $this->denyAccessUnlessGranted('get_client', $users);

        return new View($users);
    }

    /**
     * @Rest\Get("/clients/{id}", name="client_details")
     * @Rest\View(serializerGroups={"ClientView"})
     * 
     * @OA\Response(
     *      response=200,
     *      description="Get client details."
     * )
     * 
     * @OA\Response(
     *      response=404,
     *      description="Returned when client not exist."
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="The unique identifier of the client.",
     *     @OA\Schema(type="int")
     *  )
     * @OA\Tag(name="Clients")
     */
    public function clientDetails(User $user): View
    {
        $this->denyAccessUnlessGranted('get_client_details', $user);
        return new View($user);
    }

    /**
     * @Rest\Post("/clients", name="client_post")
     * @Rest\View(serializerGroups={"ClientView"})
     * 
     * @OA\Response(
     *      response=201,
     *      description="Client added successfully."
     * )
     * 
     * @OA\Response(
     *      response=400,
     *      description="Required field not filled."
     * )
     * 
     * @OA\RequestBody(
     *       description="Input data format",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *                  @OA\Schema(
     *                      type="object",
     *                      @OA\Property(
     *                          property="username",
     *                          description="Username.",
     *                          type="string",
     *                      ),
     *                      @OA\Property(
     *                          property="email",
     *                          description="Client email.",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="password",
     *                          description="Client password.",
     *                          type="string"
     *                      )
     *                  )
     *         )
     * )
     * 
     * @OA\Tag(name="Clients")
     */
    public function clientPost(UserService $userService, Request $request): View
    {
        $this->denyAccessUnlessGranted('post_client', new User());

        $data = json_decode($request->getContent(), true);

        $user = $userService->addUser($data, $this->getUser());

        return new View(
            $user,
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl(
                    'client_details',
                    [
                        'id' => $user->getId(),
                        UrlGeneratorInterface::ABSOLUTE_PATH
                    ])
            ]);
    }

    /**
     * @Rest\Patch("/clients/{id}", name="client_patch")
     * @Rest\View(serializerGroups={"Details"})
     * 
     * @OA\Response(
     *      response=202,
     *      description="Client updated successfully."
     * )
     * 
     * @OA\Response(
     *      response=400,
     *      description="Required field not filled."
     * )
     * 
     * @OA\Response(
     *      response=404,
     *      description="Returned when client not exist."
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="The unique identifier of the client.",
     *     @OA\Schema(type="int")
     *  )
     * 
     * @OA\RequestBody(
     *       description="Input data format",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *                  @OA\Schema(
     *                      type="object",
     *                      @OA\Property(
     *                          property="username",
     *                          description="Updated username.",
     *                          type="string",
     *                      ),
     *                      @OA\Property(
     *                          property="email",
     *                          description="Updated client email.",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="password",
     *                          description="Updated client password.",
     *                          type="string"
     *                      )
     *                  )
     *         )
     * )
     * @OA\Tag(name="Clients")
     */
    public function clientPatch(User $user, Request $request, UserService $userService): View
    {
        $this->denyAccessUnlessGranted('patch_client', $user);

        $data = json_decode($request->getContent(), true);

        $user = $userService->editUser($user, $data);

        return new View(
            $user,
            Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/clients/{id}", name="client_delete")
     * 
     * @OA\Response(
     *      response=204,
     *      description="Client removed successfully."
     * )
     * 
     * @OA\Response(
     *      response=404,
     *      description="Returned when client not exist."
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="The unique identifier of the client.",
     *     @OA\Schema(type="int")
     *  )
     * @OA\Tag(name="Clients")
     */
    public function clientDelete(User $user, UserService $userService)
    {
        $this->denyAccessUnlessGranted('delete_client', $user);

        $userService->deleteUser($user);

        return new View('', Response::HTTP_NO_CONTENT);
    }
}