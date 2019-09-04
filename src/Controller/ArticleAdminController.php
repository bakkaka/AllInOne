<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ArticleRepository;
use App\Service\UploaderHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleAdminController extends BaseController
{
    /**
     * @Route("/admin/article/new", name="admin_article_new")
     * @IsGranted("ROLE_USER")
     */
    public function new(EntityManagerInterface $em, Request $request, UploaderHelper $uploaderHelper)
    {
		 $user = $this->getUser();
		 $author = $user->getAuthor();
	     //$user = $this->user->getAuthor();
		if(!$user->getAuthor()){
		  $this->addFlash('error', 'Unable to find author!');
        return $this->redirectToRoute('admin_author_new');
		 $this->addFlash('error', 'Inscriver-vous pour devenir auteur!');
		}
		
		$article = new Article();
        $form = $this->createForm(ArticleType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
             /** @var Article $article */
            $article = $form->getData();
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadArticleImage($uploadedFile, $article->getImageFilename());
                $article->setImageFilename($newFilename);
            }


            $em->persist($article);
            $em->flush();

            $this->addFlash('success', 'Article Created! Knowledge is power!');

            return $this->redirectToRoute('admin_article_list');
        }

        return $this->render('article_admin/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/article/{id}/edit", name="admin_article_edit")
     * @IsGranted("MANAGE", subject="article")
     */
    
	public function edit(Article $article, Request $request, EntityManagerInterface $em, UploaderHelper $uploaderHelper)
    {
        $form = $this->createForm(ArticleType::class, $article, [
            'include_published_at' => true
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
		      /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadArticleImage($uploadedFile, $article->getImageFilename());
                $article->setImageFilename($newFilename);
            }
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'Article Updated! Inaccuracies squashed!');

            return $this->redirectToRoute('admin_article_edit', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render('article_admin/edit.html.twig', [
            'form' => $form->createView(),
			'article' => $article,
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
		return $image();
    }

    /**
     * @Route("/admin/article/location-select", name="admin_article_location_select")
     * @IsGranted("ROLE_USER")
     */
    public function getSpecificLocationSelect(Request $request)
    {
        // a custom security check
        if (!$this->isGranted('ROLE_ADMIN_ARTICLE') && $this->getUser()->getArticles()->isEmpty()) {
            throw $this->createAccessDeniedException();
        }

        $article = new Article();
        $article->setLocation($request->query->get('location'));
        $form = $this->createForm(ArticleFormType::class, $article);

        // no field? Return an empty response
        if (!$form->has('specificLocationName')) {
            return new Response(null, 204);
        }

        return $this->render('article_admin/_specific_location_name.html.twig', [
            'form' => $form->createView(),
        ]);
    }

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
     * @Route("/admin/article/{id}/delete", name="admin_article_delete")
	  * @IsGranted("MANAGE", subject="article")
     */

    public function delete(Article $article, Request $request): Response
    {
       // $this->denyAccessUnlessGranted('DELETE', $article);

        $entityManager = $this->getDoctrine()->getManager();
         $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('admin_article_list');
    }
	
	
}
