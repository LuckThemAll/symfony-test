<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\Book;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AuthorController extends BaseController
{
    /**
     * @Route("/authors/create/", name="create_form", methods={"GET", "POST"})
     */
    public function createAction(Request $request)
    {
        $author = new Author();
        $form = $this->createFormBuilder($author)
            ->add('name', TextType::class, ['label' => 'Author name'])
            ->add('save', SubmitType::class, ['label' => 'Create Author'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $author = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($author);
            $em->flush();

            return $this->redirectToRoute('authors_index');
        }
        return $this->render('default/createAuthor.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/authors/", name="authors_index")
     */
    public function showAuthors(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Author::class);
        return $this->render('default/authors.html.twig', [
            'authors' => $repository->findAll(),
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/authors/delete/{id}", name="authors_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function deleteAuthor(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Author::class);
        $author = $repository->find($id);
        $author->delete($this->getDoctrine()->getManager());
        return new Response("200");
    }

    /**
     * @Route("/authors/update/{id}", name="authors_update", requirements={"id"="\d+"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function updateAuthor(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Author::class);
        $author = $repository->find($id);
        if ($request->getMethod() == "GET"){
            return $this->render('default/updateAuthor.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                'author_name' => strval($author->getName()),
            ]);
        }
        if ($request->getMethod() == "POST"){
            $new_name = $request->request->get('name');
            if ($new_name != ''){
                $author->setName($new_name);
                $author->save($this->getDoctrine()->getManager());
                return $this->render('default/updateAuthor.html.twig', [
                    'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                    'author_name' => strval($author->getName()),
                ]);
            }
            else return new Response("Name cant be empty");
        }
        return new Response("", 200);
    }

    /**
     * @Route("/authors/get/all", name="get_authors")
     * @param Request $request
     * @return Response
     */
    public function getAuthors(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Author::class);
        $authors = $repository->findAll();
        $data = array();
        foreach ($authors as $author) {
            $data[$author->getId()] = $author->getName();
        }
        echo json_encode($data);
        return new Response("", 200);
    }
}