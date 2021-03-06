<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CacheService;
use App\Service\ProductService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductController extends AbstractController
{
    /**
     * @Rest\Get("/products", name="product_list")
     * @Rest\QueryParam(
     *      name="keyword",
     *      requirements="[a-zA-Z0-9]+",
     *      nullable=true,
     *      description="The keyword search for."
     * )
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
     *      description="Max articles per page."
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
     *      description="Get the list of all products.",
     *      @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=App\Entity\Product::class, groups={"Default"}))
     *     )
     * )
     * @OA\Tag(name="Products")
     */
    public function productList(ProductService $productService, ParamFetcherInterface $paramFetch, CacheService $cache): Response
    {

        $products = $productService->getProductList($paramFetch);

        $this->denyAccessUnlessGranted('get', $products);

        return $cache->getResponse($products, ['Default']);
    }

    /**
     * @Rest\Get("/products/{id}", name="product_details")
     * 
     * @OA\Response(
     *      response=200,
     *      description="Get product details."
     * )
     * 
     * @OA\Response(
     *      response=404,
     *      description="Returned when product not exist."
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="The unique identifier of the product.",
     *     @OA\Schema(type="int")
     *  )
     * @OA\Tag(name="Products")
     */
    public function productDetails(Product $product, CacheService $cache): Response
    {
        $this->denyAccessUnlessGranted('get', $product);
        return $cache->getResponse($product, ['Details']);
    }

    /**
     * @Rest\Post("/products", name="product_post")
     * 
     * @OA\Response(
     *      response=201,
     *      description="Product added successfully."
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
     *                          property="name",
     *                          description="Product name.",
     *                          type="string",
     *                      ),
     *                      @OA\Property(
     *                          property="brand",
     *                          description="Product brand name.",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="details",
     *                          description="Product details.",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="releaseDate",
     *                          description="Product release date.",
     *                          type="datetime"
     *                      ) 
     *                  )
     *         )
     * )
     * 
     * @OA\Tag(name="Products")
     */
    public function productPost(ProductService $productService, Request $request, CacheService $cache): Response
    {

        $this->denyAccessUnlessGranted('post', new Product());

        $data = json_decode($request->getContent(), true);

        $product = $productService->addProduct($data);

        return $cache->getResponse(
            $product,
            ['Details'],
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl(
                    'product_details',
                    [
                        'id' => $product->getId(),
                        UrlGeneratorInterface::ABSOLUTE_PATH
                    ])
                    ]);
    }

    /**
     * @Rest\Patch("/products/{id}", name="product_patch")
     * 
     * @OA\Response(
     *      response=202,
     *      description="Product updated successfully."
     * )
     * 
     * @OA\Response(
     *      response=400,
     *      description="Required field not filled."
     * )
     * 
     * @OA\Response(
     *      response=404,
     *      description="Returned when article not exist."
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="The unique identifier of the article.",
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
     *                          property="name",
     *                          description="Updated product name.",
     *                          type="string",
     *                      ),
     *                      @OA\Property(
     *                          property="brand",
     *                          description="Updated product brand name.",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="details",
     *                          description="Updated product details.",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="releaseDate",
     *                          description="Updated product release date.",
     *                          type="datetime"
     *                      ) 
     *                  )
     *         )
     * )
     * @OA\Tag(name="Products")
     */
    public function productPatch(Product $product, Request $request, ProductService $productService, CacheService $cache): Response
    {

        $this->denyAccessUnlessGranted('patch', $product);

        $data = json_decode($request->getContent(), true);

        $product = $productService->editProduct($product, $data);

        return $cache->getResponse($product, ['Details']);
    }

    /**
     * @Rest\Delete("/products/{id}", name="product_delete")
     * 
     * @OA\Response(
     *      response=204,
     *      description="Product removed successfully."
     * )
     * 
     * @OA\Response(
     *      response=404,
     *      description="Returned when article not exist."
     * )
     * 
     * @OA\Parameter(
     *     name="id",
     *     in="query",
     *     description="The unique identifier of the article.",
     *     @OA\Schema(type="int")
     *  )
     * @OA\Tag(name="Products")
     */
    public function productDelete(Product $product, ProductService $productService, CacheService $cache): Response
    {

        $this->denyAccessUnlessGranted('delete', $product);

        $productService->deleteProduct($product);

        return $cache->getResponse('', ['Default'], Response::HTTP_NO_CONTENT);
    }
}
