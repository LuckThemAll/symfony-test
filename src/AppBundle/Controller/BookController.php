<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use AppBundle\Entity\Book;
use AppBundle\Service\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class BookController extends BaseController
{
    /**
     * @Route("/books/create/", name="book_create_action")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $book = new Book();
        $form = $this->createFormBuilder($book)
            ->add('name', TextType::class, ['label' => 'Title'])
            ->add('description', TextType::class, ['label' => 'Description'])
            ->add('publicationDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'input'  => 'string'
                ])
            ->add('authors', EntityType::class, [
                'class' => 'AppBundle:Author',
                'choice_label' => 'name',
                'multiple' => true
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'empty_data' => false,
                'required' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Create Book'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            $file = $form['image']->getData();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $upLoader = new FileUploader();
            $upLoader->upload($this->getParameter('image_directory'), $file, $fileName);
            $book->setImage($fileName);
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('books_index');
        }
        return $this->render('default/createBook.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/books/", name="books_index")
     * @param Request $request
     * @return Response
     */
    public function showAuthors(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Book::class);
        return $this->render('default/books.html.twig', [
            'books' => $repository->findAll(),
            'files_dir' => $this->getParameter('image_directory')
        ]);
    }

    /**
     * @Route("/books/update/{id}", name="books_update")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function updateBook(Request $request, $id)
    {
        $book_repository = $this->getDoctrine()->getRepository(Book::class);
        $book = $book_repository->find($id);
        $form = $this->createFormBuilder($book)
            ->add('name', TextType::class, ['label' => 'Title'])
            ->add('description', TextType::class, ['label' => 'Description'])
            ->add('publicationDate', DateType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'input'  => 'datetime'
            ])
            ->add('authors', EntityType::class, [
                'class' => 'AppBundle:Author',
                'choice_label' => 'name',
                'multiple' => true
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'empty_data' => false,
                'required' => false,
                'data_class' => null
            ])
            ->add('save', SubmitType::class, ['label' => 'Update Book'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            $file = $form['image']->getData();
            $fileName = md5(uniqid()).'.'.$file->guessExtension();
            $upLoader = new FileUploader();
            $upLoader->upload($this->getParameter('image_directory'), $file, $fileName);
            $book->setImage($fileName);
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('books_index');
        }
        return $this->render('default/updateBook.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/books/delete/{id}", name="book_delete", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function deleteAuthor($id)
    {
        $repository = $this->getDoctrine()->getRepository(Book::class);
        $book = $repository->find($id);
        if ($book){
            $this->getDoctrine()->getManager()->remove($book);
            $this->getDoctrine()->getManager()->flush();
        }
        return new Response("0", 200);
    }
}