<?php

namespace AppBundle\Controller;

use AppBundle\Form\PostType;
use AppBundle\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/posts")
 */
class PostController extends Controller
{
    /**
     * @Route("/", name="posts")
     */
    public function postIndexAction(Request $request)
    {
        $repo = $this->get('doctrine')
                     ->getManager()
                     ->getRepository('AppBundle\Entity\Post');
        
        
        $qb = $repo->createQueryBuilder('p');
        $posts = $qb->getQuery()->getResult();
        
        return $this->render('posts/index.html.twig', [
            'posts' => $posts,
        ]);
    }
    
    /**
     * @Route("/{post_id}", name="post_view", requirements={"post_id": "\d+"})
     */
    public function postViewAction(Request $request, $post_id)
    {
        $em = $this->get('doctrine')->getManager();
        $post = $em->getRepository('AppBundle\Entity\Post')
                   ->findOneBy(array('id' => $post_id));
        
        return $this->render('posts/index.html.twig', [
            'posts' => array($post),
        ]);
    }
    
    /**
     * @Route("/new", name="new_post")
     */
    public function newPostAction(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $post->setAuthor($this->getUser());
            $em->persist($post);
            $em->flush();
            
            $post_id = $post->getId();
            
            return $this->redirectToRoute('post_view', [
                'post_id' => $post_id,
            ]);
        }
        
        return $this->render('posts/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/authors", name="authors_view")
     */
    public function authorsViewAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $authors = $em->getRepository('AppBundle\Entity\User')
                      ->getAllAuthors();
        return $this->render('posts/authors.html.twig', [
            'authors' => $authors,
        ]);
    }
    
    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{post_id}/edit", name="post_edit")
     * @Method({"GET", "POST"})
     */
    public function postEditAction(Request $request, $post_id)
    {
        $em = $this->get('doctrine')->getManager();
        $post = $em->getRepository('AppBundle\Entity\Post')
                   ->find($post_id);
        
        $deleteForm = $this->createDeleteForm($post);
        $editForm = $this->createForm('AppBundle\Form\PostType', $post);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('post_view', [
                'post_id' => $post->getId(),
            ]);
        }

        return $this->render('posts/edit.html.twig', array(
            'post' => $post,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    /**
     * Deletes a Post entity.
     *
     * @Route("/{post_id}", name="post_delete", requirements={"post_id": "\d+"})
     * @Method("DELETE")
     */
    public function postDeleteAction(Request $request, $post_id)
    {
        $em = $this->get('doctrine')->getManager();
        $post = $em->getRepository('AppBundle\Entity\Post')
                   ->findOneBy(array('id' => $post_id));
        
        $form = $this->createDeleteForm($event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('posts');
    }
    
    /**
     * Creates a form to delete a Post entity.
     *
     * @param Post $post The Post entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Post $post)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', [
               'post_id' => $post->getId(), 
            ]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
