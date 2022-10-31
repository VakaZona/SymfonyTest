<?php

namespace App\Controller\Api;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/movies')]
class MoviesApiController extends AbstractController
{
    private $em;
    private $movieRepository;

    public function __construct(MovieRepository $movieRepository, EntityManagerInterface $em)
    {
        $this->movieRepository = $movieRepository;
        $this->em = $em;
    }

    #[Route('/add', name: 'app_movies_add')]
    public function index(Request $request)
    {

        $this->json(['status' => 'success', $request->getContent()]);

        $imagePath = $request->files->get('image_path');
        $title = $request->request->get('title');
        $release_year = $request->request->get('release_year');
        $description = $request->request->get('description');

        $movie = new Movie();
        $movie->setTitle($title);
        $movie->setReleaseYear($release_year);
        $movie->setDescription($description);

        if ($imagePath) {
            $newFileName = uniqid() . '.' . $imagePath->guessExtension();
            try {
                $imagePath->move(
                    $this->getParameter('kernel.project_dir') . '/public/uploads',
                    $newFileName
                );
            } catch (FileException $e) {
                return new Response($e->getMessage());
            }
            $movie->setImagePath('/uploads/' . $newFileName);
        }

        $this->em->persist($movie);
        $this->em->flush();

            return $this->json([
                'movie' => $movie,
                'html' => $this->render('movies/movies.twig', ['movie' => $movie])
                ]);
        }
}
