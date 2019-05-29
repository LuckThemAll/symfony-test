<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use AppBundle\Entity\Book;
use AppBundle\Service\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class BookController extends BaseController
{
    /**
     * @Route("/books/create/", name="book_create_action")
     */
    public function createAction(Request $request)
    {
        $authors = $this->getDoctrine()->getRepository(Author::class)->findAll();
        if ($request->getMethod() == "GET") {
            return $this->render('default/createBook.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
                'authors' => $authors,
            ]);
        }
        if ($request->getMethod() == "POST") {
            $name = $request->request->get('name');
            $description = $request->request->get('description');
            $publication_date = $request->request->get('publication_date');
            $file = $request->files->get("image");
            var_dump($_FILES);
            $file_name = $file->getClientOriginalName();
            $up_loader = new FileUploader();
            $up_loader->upload($this->getParameter('image_directory'), $file, $file_name);
            $book = New Book();
            $book->setName($name);
            $book->setPublicationDate($publication_date);
            $book->setDescription($description);
            $book->setImage($file_name);
            foreach ($request->request->all()['authors'] as $author_id) {
                $author = $this->getDoctrine()->getRepository(Author::class)->find($author_id);
                $book->getAuthors()->add($author);
            }
            $this->getDoctrine()->getManager()->persist($book);
            $this->getDoctrine()->getManager()->flush();
            return $this->render('default/createBook.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
                'authors' => $authors,
            ]);
        }
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/books/", name="books_index")
     */
    public function showAuthors(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Book::class);
        return $this->render('default/books.html.twig', [
            'books' => $repository->findAll(),
            'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
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
        $author_repository = $this->getDoctrine()->getRepository(Author::class);
        /** @var Book $book */
        $book = $book_repository->find($id);
        $authors = $author_repository->findAll();
        $current_authors_names = new ArrayCollection();
        foreach ($book->getAuthors() as $author) {
            $current_authors_names->add($author->getName());
        }
        if ($request->getMethod() == "GET") {
            return $this->render('default/updateBook.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
                'book' => $book,
                'authors' => $authors,
                'current_authors_names' => $current_authors_names,
            ]);
        }
        if ($request->getMethod() == "POST") {
            $new_name = $request->request->get('name');
            $new_description = $request->request->get('description');
            $new_publication_date = $request->request->get('publication_date');
            $book->setName($new_name);
            $book->setDescription($new_description);
            $book->setPublicationDate($new_publication_date);
            $book->getAuthors()->clear();
            $current_authors_names->clear();
            foreach ($request->request->all()['authors'] as $author_id) {
                $author = $this->getDoctrine()->getRepository(Author::class)->find($author_id);
                $current_authors_names->add($author->getName());
                $book->getAuthors()->add($author);
            }
            $this->getDoctrine()->getManager()->persist($book);
            $this->getDoctrine()->getManager()->flush();
            return $this->render('default/updateBook.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
                'author_name' => strval($author->getName()),
                'current_authors_names' => $current_authors_names,
                'authors' => $authors,
            ]);
        }
        return new Response("", 200);
    }

    /**
     * @Route("/books/delete/{id}", name="book_delete", requirements={"id"="\d+"})
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function deleteAuthor(Request $request, $id)
    {
        $repository = $this->getDoctrine()->getRepository(Book::class);
        $book = $repository->find($id);
        $this->getDoctrine()->getManager()->remove($book);
        $this->getDoctrine()->getManager()->flush();
        return new Response("0", 200);
    }

}