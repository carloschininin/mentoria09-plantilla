<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\UsuarioType;
use App\Repository\UsuarioRepository;
use CarlosChininin\Spreadsheet\Shared\DataFormat;
use CarlosChininin\Spreadsheet\Shared\DataType;
use CarlosChininin\Spreadsheet\Writer\PhpSpreadsheet\SpreadsheetWriter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/usuario')]
final class UsuarioController extends AbstractController
{
    #[Route(name: 'app_usuario_index', methods: ['GET'])]
    public function index(UsuarioRepository $usuarioRepository): Response
    {
        return $this->render('usuario/index.html.twig', [
            'usuarios' => $usuarioRepository->findAll(),
        ]);
    }

//    #[Route('/export', name: 'app_usuario_export', methods: ['GET'])]
//    public function export(UsuarioRepository $usuarioRepository): Response
//    {
//        $headers = [
//            'ID',
//            'NOMBRE DE USUARIO',
//            'FECHA',
//            'ESTADO',
//        ];
//
//        $usuarios = $usuarioRepository->findAll();
//        $items = [];
//        foreach ($usuarios as $usuario) {
//            $item = [];
//            $item[] = $usuario->getId();
//            $item[] = $usuario->getUsername();
//            $item[] = new \DateTime();
//            $item[] = 'Aceptado';
//
//            $items[] = $item;
//            unset($item);
//        }
//
//        $export = new SpreadsheetWriter($items, $headers);
//        $export->execute()->columnAutoSize();
//
//        return $export->download('usuarios_export');
//    }

    #[Route('/export', name: 'app_usuario_export', methods: ['GET'])]
    public function export(UsuarioRepository $usuarioRepository): Response
    {
        $headers = [
            'ID',
            'NOMBRE DE USUARIO',
            'FECHA',
            'ESTADO',
        ];

        $usuarios = $usuarioRepository->findAll();
        $export = new SpreadsheetWriter();
        $export->fromArray(1, 1, [$headers], $export->headerStyle());

        $row=2;
        foreach ($usuarios as $usuario) {
            $col = 1;
            $export->setCellValue($col++, $row, $usuario->getId());
            $export->setCellValue($col++, $row, $usuario->getUsername());
            $export->setCellValue($col++, $row, new \DateTime(), DataFormat::DATE_DMMINUS, DataType::DATE);
            $export->setCellValue($col++, $row, 'Aceptado');
            $row++;
        }

        return $export
            ->execute()
            ->columnAutoSize()
            ->download('usuarios_export');
    }

    #[Route('/new', name: 'app_usuario_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {
        $usuario = new Usuario();
        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $passwordEncrypted = $hasher->hashPassword($usuario, $usuario->getPassword());
            $usuario->setPassword($passwordEncrypted);
            $entityManager->persist($usuario);
            $entityManager->flush();

            return $this->redirectToRoute('app_usuario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuario/new.html.twig', [
            'usuario' => $usuario,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_usuario_show', methods: ['GET'])]
    public function show(Usuario $usuario): Response
    {
        return $this->render('usuario/show.html.twig', [
            'usuario' => $usuario,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_usuario_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Usuario $usuario,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $passwordDefault = $usuario->getPassword();
        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $usuario->getPasswordActual() && !password_verify($usuario->getPasswordActual(), $passwordDefault)) {
                return $this->render('usuario/edit.html.twig', [
                    'usuario' => $usuario,
                    'form' => $form,
                    'message' => 'Contraseña actual no coincide',
                ]);
            }

            if (null !== $usuario->getPassword()) {
                $passwordDefault = $hasher->hashPassword($usuario, $usuario->getPassword());
            }

            $usuario->setPassword($passwordDefault);
            $entityManager->flush();

            return $this->redirectToRoute('app_usuario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('usuario/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_usuario_delete', methods: ['POST'])]
    public function delete(Request $request, Usuario $usuario, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_usuario_index', [], Response::HTTP_SEE_OTHER);
    }
}
