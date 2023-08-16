<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, EntityManagerInterface $em): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactFormType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
            $em->persist($contact);
            $em->flush();

            

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    

    
    // #[Route('/contact/success", name="contact_success')]    
    // public function contactSuccess(): Response
    // {
    //     return $this->render('contact/success.html.twig');
    // }
    }
    #[Route('/feedback/{id}/delete', name: 'app_delete_feedback')]
    public function delete(EntityManagerInterface $em, int $id,Request $req ): Response
    {
            $fb = $em->find(Contact::class, $id);
            $em->remove($fb);
            $em->flush();
            return new RedirectResponse($this->urlGenerator->generate('app_ds_feedback'));
    }  
    #[Route('/feedback/ds', name: 'app_ds_feedback')]
    public function list_fb(EntityManagerInterface $em): Response
    {
        $query = $em->createQuery('SELECT fb FROM App\Entity\Contact fb');
        $lSp = $query->getResult();
        return $this->render('contact/contact.html.twig', [
            "data"=>$lSp
        ]);
    }

}