<?php

namespace App\Controller;

use App\Entity\Item;
use App\Entity\TodoList;
use App\Form\ItemType;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/user{user_id}/item")
 */
class ItemController extends AbstractController
{
    /**
     * @Route("/", name="item_index", methods={"GET"})
     * @param ItemRepository $itemRepository
     * @param UserRepository $userRepository
     * @param string $user_id
     * @return Response
     */
    public function index(ItemRepository $itemRepository, UserRepository $userRepository, string $user_id): Response
    {
        $user = $userRepository->find($user_id);
        return $this->render('User_connect/item/index.html.twig', [
            'user' => $user,
            'todoList' => $itemRepository->findBy(array('todoList' => $user->getTodoList()))
        ]);
    }

    /**
     * @Route("/API_get_all_items", name="API_get_all_items")
     */
    public function API_get_all_items(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $items = $em->getRepository(Item::class)->findAll();
        return new JsonResponse($this->serializeToJson($items), Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/API_get_item/{id}", name="API_get_item")
     */
    public function API_get_item(Item $item): Response
    {
        return new JsonResponse($this->serializeToJson($item), Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/API_add_item", methods={"POST"})
     */
    public function API_add_item(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $item = new Item();
        $item->setDateAtCreated(Carbon::now());
        $item->setTodoList($user->getTodoList());
        $item->setName($request->get("name"));
        $item->setContent($request->get("content"));
        $em->persist($item);
        $em->flush();
        $result = array("result"=> "Item successfully added!");
        return new JsonResponse(json_encode($result), Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/new", name="item_new", methods={"GET","POST"})
     * @param Request $request
     * @param string $user_id
     * @param UserRepository $userRepository
     * @return Response
     */
    public function new(Request $request, string $user_id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($user_id);
        $item = new Item();
        $item->setDateAtCreated(Carbon::now());
        $item->setTodoList($user->getTodoList());
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $item->isValid() && $user->getTodoList()->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $user->getTodoList()->addItem($item);
            $entityManager->persist($item);
            $entityManager->flush();

            return $this->redirectToRoute('item_index', [
                'user_id' => $user->getId()
            ]);
        }
        $this->addFlash('red', 'Attendre 30 minutes aprés la dernière création');

        return $this->render('User_connect/item/new.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/item-{id}", name="item_show", methods={"GET"})
     * @param Item $item
     * @param UserRepository $userRepository
     * @param string $user_id
     * @return Response
     */
    public function show(Item $item, UserRepository $userRepository, string $user_id): Response
    {
        $user = $userRepository->find($user_id);
        return $this->render('User_connect/item/show.html.twig', [
            'user' => $user,
            'item' => $item,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="item_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Item $item
     * @param UserRepository $userRepository
     * @param string $user_id
     * @return Response
     */
    public function edit(Request $request, Item $item, UserRepository $userRepository, string $user_id): Response
    {
        $form = $this->createForm(ItemType::class, $item);
        $user = $userRepository->find($user_id);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('item_index', [
                'user_id' => $user->getId()
            ]);
        }

        return $this->render('User_connect/item/edit.html.twig', [
            'user' => $user,
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete{id}", name="item_delete", methods={"GET","POST"})
     * @param Request $request
     * @param Item $item
     * @param UserRepository $userRepository
     * @param string $user_id
     * @return Response
     */
    public function delete(Request $request, Item $item, UserRepository $userRepository, string $user_id): Response
    {
        $user = $userRepository->find($user_id);
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('item_index', [
            'user_id' => $user->getId()
        ]);
    }

    private function serializeToJson($object){
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize($object, 'json');
        return $jsonContent;
    }
}
