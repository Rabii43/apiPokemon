<?php

namespace App\Controller;

use App\Entity\Pokemon;
use App\Form\PokemonType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use http\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class PokemonController
 *
 * @package App\Controller
 * @Route("/api", name="api_Pokemon_")
 */
class PokemonController extends AbstractController
{

    /**
     * @Route("/pokemons", name="app_pokemon", methods={"GET"})
     * @return Response
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $pokemons = $doctrine->getManager()->getRepository(Pokemon::class)->findAll();
        return $this->json($pokemons);
    }

    /**
     * @Route("/pokemons/add", name="app_pokemons", methods={"POST"})
     * @return Response
     */
    public function addPokemon(Request $request, EntityManagerInterface $manager): Response
    {
        $parameters = json_decode($request->getContent(), true);
        $pokemon = new Pokemon();
        $pokemon->setName($parameters['name']);
        $pokemon->setCp($parameters['cp']);
        $pokemon->setHp($parameters['hp']);
        $pokemon->setPicture($parameters['picture']);
        $pokemon->setTypes($parameters['types']);
        $pokemon->setCreated(new \DateTime());
        $manager->persist($pokemon);
        $manager->flush();
        return $this->json([$pokemon,"message" => "pokemon created", "code" => 201]);

    }
    /**
     * @Route("/pokemon/{id}", name="app_pokemon-show", methods={"GET"})
     * @return Response
     */
    public function showPokemon(Request $request, EntityManagerInterface $manager, $id): Response
    {
        $pokemon = $manager->getRepository(Pokemon::class)->find(['id' => $id]);
        if (!$pokemon) {
            return $this->json(["message" => "No pokemon", "code" => 404]);
        }
        return $this->json($pokemon);
    }



    /**
     * @Route("/pokemon/{id}", name="app_pokemon-update", methods={"PUT"})
     * @return Response
     */
    public function updatePokemon(Request $request, EntityManagerInterface $manager, $id): Response
    {
        $parameters = json_decode($request->getContent(), true);
        $pokemon = $manager->getRepository(Pokemon::class)->find(['id' => $id]);
        if (!$pokemon) {
            return $this->json(["message" => "No pokemon", "code" => 404]);
        }
        $pokemon->setName($parameters['name']);
        $pokemon->setCp($parameters['cp']);
        $pokemon->setHp($parameters['hp']);
        $pokemon->setPicture($parameters['picture']);
        $pokemon->setTypes($parameters['types']);
        $pokemon->setCreated($pokemon->getCreated());
        $manager->persist($pokemon);
        $manager->flush();
    return $this->json($pokemon);
    }

    /**
     * @Route("/pokemon/{id}", name="app_pokemon-delete", methods={"DELETE"})
     * @return Response
     */
    public function deletePokemon(EntityManagerInterface $manager, $id): Response
    {
        $pokemon = $manager->getRepository(Pokemon::class)->find(['id' => $id]);
        if (!$pokemon) {
            return $this->json(["message" => "No pokemon", "code" => 404]);
        }
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($pokemon);
        $manager->flush();
        return $this->json(["message" => "pokemon delete", "code" => 200]);
    }
}

//$pokemons = new Pokemon();
//
//$form = $this->createForm(PokemonType::class,$pokemons);
//
//$form->handleRequest($request);
//
//if ($form->isSubmitted() && $form->isValid()) {
//    $pokemons = $form->getData();
//    $manager->persist($pokemons);
//    $manager->flush();
//
//    return $this->json(200, $pokemons);
//}
//
//return $this->json(400);
