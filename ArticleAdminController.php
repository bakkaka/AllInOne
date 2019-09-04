<?php

namespace App\Controller;
use App\Entity\Article;
use App\Entity\Author;
use App\Entity\Image;
use App\Entity\User;
use App\Form\ImageType;
use App\Form\UserType;
use App\Form\ArticleType;
use App\Form\AuthorType;
use App\Form\CommentType;
use App\Entity\Comment;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Annotations;

class ArticleAdminController extends AbstractController
{

    /**
     * @Route("/admin/article/list", name="admin_article_list")

     */
    public function list(ArticleRepository $articleRepo)
    {
        $articles = $articleRepo->findAll();
        return $this->render('article_admin/list.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/admin/article/new", name="admin_article_new")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(EntityManagerInterface $em, Request $request): Response
    {

        // $user = $this->getUser();
        $article = new Article();


        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setUser($user);
           
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash('success', 'Article Created! Knowledge is power!');


            return $this->redirectToRoute('admin_article_list');
        }

        return $this->render('article_admin/new.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/author/create", name="admin_author_create")
     *  @Security("is_granted('ROLE_USER')")
     */

    public function create(EntityManagerInterface $em, Request $request): Response
    {

        // $user = $this->getUser();
        $author = new Author();


        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $article->setUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($author);
            $em->flush();
            $this->addFlash(
                'notice', 'Congratulations!!, your an Author!'
            );

            return $this->redirectToRoute('blog');
        }

        return $this->render('article_admin/author.html.twig', [
            'author' => $author,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/article/{id}/edit", name="admin_article_edit", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER')")
     * @param Article $article
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Article $article, Request $request, EntityManagerInterface $em): Response
    {

        if ($article->getAuthor() != $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('No access!');
        }
        $this->denyAccessUnlessGranted('EDIT', $article);
        $form = $this->createForm(ArticleType::class, $article, [
            'include_published_at' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash(
                'notice', 'Congratulations!!, your resource has been modified!'
            );

            return $this->redirectToRoute('admin_article_list', [
                'id' => $article->getId(),
            ]);

        }


        return $this->render('article_admin/edit.html.twig.', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/article/{id}/delete", name="admin_article_delete")
     */

    public function delete(Article $article, Request $request): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $article);

        $entityManager = $this->getDoctrine()->getManager();
         $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('admin_article_list');
    }

    /**
     * @Route("/admin/article/location-select", name="admin_article_location_select")
     *  @IsGranted("ROLE_USER")
     */
    public function getSpecificLocationSelect(Request $request)
    {

        // a custom security check
        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser()->getArticles()->isEmpty()) {
            throw $this->createAccessDeniedException();
        }


        $article = new Article();
        $article->setLocation($request->query->get('location'));
        $form = $this->createForm(ArticleType::class, $article);
        // no field? Return an empty response
        if (!$form->has('specificLocationName')) {
            return new Response(null, 204);
        }
        return $this->render('article_admin/_specific_location_name.html.twig', [
            'articleForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/upload/test", name="upload_test")
     */
    public function temporaryUploadAction(Request $request)
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('image');
        $destination = $this->getParameter('kernel.project_dir').'/public/uploads/img';
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
        dd($uploadedFile->move(
            $destination,
            $newFilename
        ));
    }



}



