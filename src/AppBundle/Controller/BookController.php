<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use AppBundle\Entity\Book;
use AppBundle\Repositories\BookRepository;
use AppBundle\Service\FileUploader;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
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
    public function showBooks(Request $request)
    {
        $books_repository = $this->getDoctrine()->getRepository(Book::class);
        $authors_repository = $this->getDoctrine()->getRepository(Author::class);
        $conds = [];
        $conds['filter_name'] = $request->query->get('filter_name');
        $conds['filter_description'] = $request->query->get('filter_description');
        $conds['filter_date_from'] = $request->query->get('filter_date_from');
        $conds['filter_date_to'] = $request->query->get('filter_date_to');
        $conds['filter_authors'] = [];
        if ($request->query->get('filter_authors')){
            foreach ($request->query->get('filter_authors') as $author_id) {
                array_push($conds['filter_authors'], $authors_repository->find($author_id));
            }
        }

        $conds['filter_image'] = $request->query->get('filter_image');
//        var_dump($conds);
        $custum_repos = new BookRepository($this->getDoctrine()->getManager(), new ClassMetadata(Book::class));
        $custum_repos->parse_conditions($conds);
//        var_dump($custum_repos->getQuery());
        return $this->render('default/book.html.twig', [
            'books' => $custum_repos->getQuery()->getResult(),
            'authors' => $authors_repository->findAll(),
            'files_dir' => $this->getParameter('image_directory'),
            'conds' => $conds
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
        $fileName = $book->getImage();
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
                'required' => false,
                'data_class' => null
            ])
            ->add('save', SubmitType::class, ['label' => 'Update Book'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            $file = $form['image']->getData();
            if (!is_null($file)){
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $upLoader = new FileUploader();
                $upLoader->upload($this->getParameter('image_directory'), $file, $fileName);
                $book->setImage($fileName);
            } else
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
     * @Route("/books/update/inline/{id}", name="books_update_inline")
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function inlineUpdateBook(Request $request, $id)
    {
        $book_repository = $this->getDoctrine()->getRepository(Book::class);
        if ($book_repository->find($id)){
            /** @var Book $book */
            $book = $book_repository->find($id);

            $new_name = $request->request->get('name');
            $new_description = $request->request->get('description');
            $new_publication_date = $request->request->get('publicationDate');

            $book->setName($new_name);
            $book->setDescription($new_description);
            $book->setPublicationDate(DateTime::createFromFormat('Y-m-d', $new_publication_date));

            if($new_authors = $request->request->all()['authors']){
                $book->getAuthors()->clear();
                foreach ($request->request->all()['authors'] as $author_id) {
                    $author = $this->getDoctrine()->getRepository(Author::class)->find($author_id);
                    $book->getAuthors()->add($author);
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            $authors = [];
            /** @var Author $author */
            foreach ($book->getAuthors() as $author){
                $authors[$author->getId()] = $author->getName();
            }

            $data = array(
                'name' => $book->getName(),
                'description' => $book->getDescription(),
                'publicationDate' => $new_publication_date,
                'authors' => $authors
            );
            echo json_encode($data);
            return new Response('', 200);

        }
//        $file = $form['image']->getData();
//        $fileName = md5(uniqid()).'.'.$file->guessExtension();
//        $upLoader = new FileUploader();
//        $upLoader->upload($this->getParameter('image_directory'), $file, $fileName);
//        $book->setImage($fileName);
        return new Response('', 500);
    }


    /**
     * @Route("/books/delete/{id}", name="book_delete", requirements={"id"="\d+"})
     * @param $id
     * @return Response
     */
    public function deleteBook($id)
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