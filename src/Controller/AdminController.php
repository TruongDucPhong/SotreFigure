<?php

namespace App\Controller;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\SanPham;
use App\Entity\Contact;
use App\Service\FileUploader;
use App\Form\SanPhamType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Knp\Component\Pager\PaginatorInterface;



class AdminController extends AbstractController
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }
    #[Route('/', name: 'admin')]
    public function adminRequirement()
    {
        return $this->render('admin/index.html.twig');
    
    }
    #[Route('/admin/listsp', name: 'list_product')]
    public function list_sp(EntityManagerInterface $em, Request $req): Response
    {
        $query = $em->createQuery('SELECT sp FROM App\Entity\SanPham sp');
        $lSp = $query->getResult();
        $message = $req->query->get('message');
        return $this->render('admin/list.html.twig', [
            "data"=>$lSp,
            "message"=>$message
        ]);
    }
    #[Route('/mnproduct', name: 'addpd')]
    public function addProduct(EntityManagerInterface $em, Request $req, FileUploader $fileUploader, Security $security): Response
    {
        $sp = new SanPham();
        $form = $this->createForm(SanPhamType::class, $sp);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $file = $form->get("photo")->getData();

            $fileName = $fileUploader->upload($file);
            $data->setPhoto($fileName);

            $em->persist($data);
            $em->flush();
            return new RedirectResponse($this->urlGenerator->generate('app_ds_san_pham'));
        }

        return $this->render('san_pham/index.html.twig', [
            'sp_form' => $form->createView(),
        ]);
    }

#[Route('/contact', name: 'view_contact')]
public function viewCate(EntityManagerInterface $em): Response
{
    $query = $em->createQuery('SELECT contact FROM App\Entity\Contact contact');
    $contact = $query->getResult();
                            
    return $this->render('admin/contact.html.twig', [
        'contact' => $contact,
    ]);
}

#[Route("/contact/delete/{id}", name:"delete_contact")]

public function deleteContact(EntityManagerInterface $em, int $id, Request $req): Response
{
   // Assuming you are using Doctrine ORM and the Contact entity
   $contact = $em->find(Contact::class, $id);
   $em->remove($contact);
   $em->flush();
   return new RedirectResponse($this->urlGenerator->generate('view_contact'));
}

    #[Route('/user', name: 'view_user')]
public function viewUser(EntityManagerInterface $em): Response
{
    $query = $em->createQuery('SELECT user FROM App\Entity\User user');
    $user = $query->getResult();
                            
    return $this->render('admin/user.html.twig', [
        'user' => $user,
    ]);
}
#[Route("/user/delete/{id}", name:"delete_user")]
    public function deleteUser(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('admin/user.html.twig');
    }
}

