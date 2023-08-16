<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserManagerController extends AbstractController
{
    public function __construct(private UrlGeneratorInterface $ug)
    {
    }
    
    #[Route('/listuser', name: 'app_user_manager')]
    public function index(EntityManagerInterface $em): Response
    {
        $query = $em->createQuery('SELECT u FROM App\Entity\User u');
        $lUser = $query->getResult();
        return $this->render('user_manager/index.html.twig', [
           'data' => $lUser
        ]);
    }

    #[Route('/user/manager/add', name: 'app_user_manager_add')]
    public function add(EntityManagerInterface $em, Request $req, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $u = new User();
        $form = $this->createForm(UserFormType::class, $u);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $u = $form->getData();
            $u->setPassword(
                $userPasswordHasher->hashPassword(
                    $u,
                    $form->get('password')->getData()
                )
            );
            $u->setRoles(["ROLE_USER"]);
            $em->persist($u);
            $em->flush();
            return new RedirectResponse($this->ug->generate('app_user_manager'));
        }

        return $this->render('user_manager/form.html.twig', [
            'u_form' => $form->createView(),
        ]);
    }
    #[Route('/user/manager/{id}', name: 'app_edit_user')]
        public function editUser(Request $request, User $user,int $id, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
        { 
            $sp = $entityManager->find(User::class, $id);
            $form = $this->createForm(UserFormType::class, $user);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // Handle password update
                if ($form->get('plainPassword')->getData()) {
                    
                    $hashedPassword = $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData());
                    $user->setPassword($hashedPassword);
                }
                $entityManager->flush();
                return $this->redirectToRoute('app_user_manager');
            }
            return $this->render('user_manager/form.html.twig', [
                'u_form' => $form->createView(),
            ]);
        }
        
    //     #[Route('/user/manager/delete', name: 'app_delete_user_manager')]
    // public function delete(EntityManagerInterface $em, int $id, Request $req,User $user): Response
    //     {
    //         $u = $em->find(User::class, $id); 
    //         $em->remove($u);
    //         $em->flush();
    //         return new RedirectResponse($this->urlGenerator->generate('app_user_manager'));
    //     }
        #[Route('/user/manager/{id}/delete', name: 'app_delete_user')]
    public function deletedw(EntityManagerInterface $em, int $id, Request $req): Response
        {
            $u = $em->find(User::class, $id); 
            $em->remove($u);
            $em->flush();
            return new RedirectResponse($this->urlGenerator->generate('app_user_manager'));
        }
        // #[Route('/san/pham/delete', name: 'app_delete_user')]
        // public function deleteuser(EntityManagerInterface $em,int $id,UserName $name, Request $req): Response
        // {
        //     $u = $em->find(SanPham::class,$id );
            
        //     $em->remove($sp);
        //     $em->flush();
        //     return new RedirectResponse($this->urlGenerator->generate('app_user_manager'));
        // }
}
