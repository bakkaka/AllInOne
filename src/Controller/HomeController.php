<?php

namespace App\Controller;
use App\Form\ArticleType;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\UploaderHelper;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Sluggable\Util\Urlizer;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Psr\Log\LoggerInterface;
use App\Entity\Comment;
use App\Form\CommentType;

use App\Entity\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(ArticleRepository $articleRepository): Response
    {
        $user = $this->getUser();

        return $this->render('home/index.html.twig', [
            'articles' => $articleRepository->findAll(),
            //'allarticles' => $articleRepository->getArticleWithUser($user),

        ]);
    }
	
	/**
     * @Route("show/article{id}", name="article_show", methods={"GET"})

     */
    public function show( ArticleRepository $articleRepository,CommentRepository $commentRepository, Request $request, $id,EntityManagerInterface $em): Response
    {
        $article = $articleRepository->find($id);
		 //$article->incrementVisitCount();
        //$em->flush();
        //return new JsonResponse(['visits' => $article->getVisitCount()]);
        
        $comments = $commentRepository
            ->getCommentWithArticle($article);
        if (!$article) {
            throw $this->createNotFoundException(sprintf('No article for slug "%s"', $id));
        }
		
        
        return $this->render('home/show.html.twig', [
            'article' => $article,
           'comments' => $comments

        ]);
    }
	
	/**
     * @Route("/home/upload/test", name="home_upload_test")
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
    
	   return $this->render('home/image.html.twig', [
            'image' => $image,
        ]);
	}
	
	 /**
     * @Route("/news/{id}/heart", name="article_toggle_heart", methods={"POST"})
     */
    public function toggleArticleHeart(Article $article, LoggerInterface $logger,EntityManagerInterface $em)
    {
        // TODO - actually heart/unheart the article!
        $logger->info('Article is being hearted!');
        $article->incrementHeartCount();
        $em->flush();
        return new JsonResponse(['hearts' => $article->getHeartCount()]);
    }
	
	/**
     * @Route("/addComment/article/{id}", name="home_addComment", methods={"GET","POST"})
      *@Security("is_granted('ROLE_USER')")
     */

    public function addComment(Article $article, Request $request, CommentRepository $commentRepository, ArticleRepository $articleRepository, $id): Response
    {
        $comment = new Comment();
        $user = $this->getUser();
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository('App:Article')->findOneById($id);


        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $entityManager->getRepository('App:Article')->findOneById($id);
            $comment->setUser($user);
            $article->setAuthor($user);
            $comment->setArticle($article);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash(
                'notice', 'Congratulations!!, your comment has been added!'
            );

            return $this->redirectToRoute('article_show', array('id' => $article->getId()));
            //return $this->redirectToRoute($this->generateUrl('home_show', array('id' => $article->getId())));
        }

        return $this->render('home/addComment.html.twig', [
            $comments = $commentRepository
                ->getCommentWithArticle($article),
            'article' => $articleRepository->findOneById($id),
            'form' => $form->createView(),
        ]);
    }

}
