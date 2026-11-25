<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Translation;
use App\Form\ProjectType;
use App\Repository\LanguageRepository;
use App\Repository\ProjectRepository;
use App\Repository\TranslationRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/projects')]
class ProjectController extends AbstractController
{
    #[Route('', name: 'app_projects')]
    public function index(ProjectRepository $projectRepository): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $projects = $projectRepository->findAll();
        } elseif ($this->isGranted('ROLE_PROJECT_MANAGER')) {
            $owned      = $projectRepository->findByOwner($user);
            $translating = $projectRepository->findByTranslator($user);
            $ids = []; $projects = [];
            foreach (array_merge($owned, $translating) as $p) {
                if (!in_array($p->getId(), $ids, true)) {
                    $ids[]      = $p->getId();
                    $projects[] = $p;
                }
            }
        } else {
            $projects = $projectRepository->findVisibleForTranslator($user);
        }

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'app_project_new')]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function new(Request $request, EntityManagerInterface $em, NotificationService $notif): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project->setOwner($this->getUser());
            $em->persist($project);
            $em->flush();
            $notif->notifyAllTranslators(
                'Nouveau projet disponible : « ' . $project->getName() . ' »',
                '/projects/' . $project->getId()
            );
            $em->flush();
            $this->addFlash('success', 'Projet créé.');
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'app_project_show', requirements: ['id' => '\d+'])]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', ['project' => $project]);
    }

    #[Route('/{id}/export/{format}', name: 'app_project_export', requirements: ['id' => '\d+', 'format' => 'json|csv'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function export(Project $project, string $format, TranslationRepository $repo): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $translations = $repo->findBy(['project' => $project, 'status' => Translation::STATUS_APPROVED]);

        // Grouper par langue
        $data = [];
        foreach ($translations as $t) {
            $code = $t->getLanguage()->getCode();
            $data[$code][$t->getSourceKey()] = $t->getTranslatedText();
        }

        $filename = $project->getName() . '-traductions-' . date('Y-m-d');

        if ($format === 'json') {
            $response = new Response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.json"');
            return $response;
        }

        // CSV
        $response = new StreamedResponse(function () use ($data, $project) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Langue', 'Clé', 'Traduction']);
            foreach ($data as $lang => $strings) {
                foreach ($strings as $key => $value) {
                    fputcsv($handle, [$lang, $key, $value]);
                }
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');
        return $response;
    }

    #[Route('/{id}/edit', name: 'app_project_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function edit(Request $request, Project $project, EntityManagerInterface $em, NotificationService $notif): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $notif->notifyAllTranslators(
                'Le projet « ' . $project->getName() . ' » a été modifié.',
                '/projects/' . $project->getId()
            );
            $em->flush();
            $this->addFlash('success', 'Projet mis à jour.');
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/edit.html.twig', ['form' => $form, 'project' => $project]);
    }

    #[Route('/{id}/delete', name: 'app_project_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function delete(Request $request, Project $project, EntityManagerInterface $em): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->request->get('_token'))) {
            $em->remove($project);
            $em->flush();
            $this->addFlash('success', 'Projet supprimé.');
        }
        return $this->redirectToRoute('app_projects');
    }

    // Ajout d'une source (chef de projet)
    #[Route('/{id}/source/new', name: 'app_project_source_new', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function sourceNew(Request $request, Project $project, EntityManagerInterface $em, NotificationService $notif): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $error = null;

        if ($request->isMethod('POST')) {
            $key     = trim($request->request->get('source_key', ''));
            $content = trim($request->request->get('source_content', ''));

            if ($key === '' || $content === '') {
                $error = 'La clé et le contenu sont obligatoires.';
            } elseif (!preg_match('/^[a-z0-9_]+$/i', $key)) {
                $error = 'La clé ne doit contenir que des lettres, chiffres et underscores.';
            } else {
                $created = 0;
                foreach ($project->getTargetLanguages() as $language) {
                    $existing = $em->getRepository(Translation::class)->findOneBy([
                        'project'   => $project,
                        'language'  => $language,
                        'sourceKey' => $key,
                    ]);
                    if (!$existing) {
                        $translation = new Translation();
                        $translation->setProject($project);
                        $translation->setLanguage($language);
                        $translation->setSourceKey($key);
                        $translation->setSourceText($content);
                        $em->persist($translation);
                        $created++;
                    }
                }

                if ($created > 0) {
                    $em->flush();
                    $notif->notifyAllTranslators(
                        'Nouvelle chaîne à traduire dans « ' . $project->getName() . ' »',
                        '/projects/' . $project->getId()
                    );
                    $em->flush();
                    $this->addFlash('success', 'Source « ' . $key . ' » ajoutée pour ' . $created . ' langue(s).');
                } else {
                    $this->addFlash('error', 'Cette clé existe déjà dans toutes les langues cibles.');
                }

                if ($request->request->get('add_another')) {
                    return $this->redirectToRoute('app_project_source_new', ['id' => $project->getId()]);
                }
                return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
            }
        }

        return $this->render('project/source_new.html.twig', [
            'project' => $project,
            'error'   => $error,
        ]);
    }

    // Import de contenu (chef de projet)
    #[Route('/{id}/import', name: 'app_project_import', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function import(Request $request, Project $project, EntityManagerInterface $em, NotificationService $notif): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            $mode = $request->request->get('mode', 'text');

            if ($mode === 'file') {
                $file = $request->files->get('import_file');
                if ($file) {
                    $text = file_get_contents($file->getPathname());
                    $this->importBulk($text ?? '', $project, $em);
                }
            } else {
                $this->importBulk($request->request->get('bulk_text', ''), $project, $em);
            }

            $em->flush();
            $notif->notifyAllTranslators(
                'Nouveau contenu à traduire dans « ' . $project->getName() . ' »',
                '/projects/' . $project->getId()
            );
            $em->flush();
            $this->addFlash('success', 'Contenu importé avec succès.');
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/import.html.twig', ['project' => $project]);
    }

    private function importBulk(string $text, Project $project, EntityManagerInterface $em): void
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $i = 1;
        foreach ($lines as $line) {
            if ($line === '') continue;
            $key = 'string_' . str_pad((string)($i++), 3, '0', STR_PAD_LEFT);
            $this->createTranslationsForAllLanguages($key, $line, $project, $em);
        }
    }

    private function createTranslationsForAllLanguages(string $key, string $text, Project $project, EntityManagerInterface $em): void
    {
        foreach ($project->getTargetLanguages() as $language) {
            $translation = new Translation();
            $translation->setProject($project);
            $translation->setLanguage($language);
            $translation->setSourceKey($key);
            $translation->setSourceText($text);
            $em->persist($translation);
        }
    }

    // Révision (chef de projet)
    #[Route('/{id}/review', name: 'app_project_review', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function review(Project $project, TranslationRepository $repo): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $submitted = $repo->findBy(['project' => $project, 'status' => Translation::STATUS_SUBMITTED]);
        $approved  = $repo->findBy(['project' => $project, 'status' => Translation::STATUS_APPROVED]);
        $rejected  = $repo->findBy(['project' => $project, 'status' => Translation::STATUS_REJECTED]);

        return $this->render('project/review.html.twig', [
            'project'   => $project,
            'submitted' => $submitted,
            'approved'  => $approved,
            'rejected'  => $rejected,
        ]);
    }

    #[Route('/{id}/review/{translationId}/approve', name: 'app_translation_approve', methods: ['POST'], requirements: ['id' => '\d+', 'translationId' => '\d+'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function approve(Project $project, int $translationId, TranslationRepository $repo, EntityManagerInterface $em, NotificationService $notif): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $translation = $repo->findOneBy(['id' => $translationId, 'project' => $project]);
        if ($translation) {
            $translation->setStatus(Translation::STATUS_APPROVED);
            $translation->setIsApproved(true);
            $translation->setReviewComment(null);
            $em->flush();
            if ($translation->getTranslator()) {
                $notif->notify(
                    $translation->getTranslator(),
                    'Votre traduction dans « ' . $project->getName() . ' » a été approuvée ✓',
                    '/projects/' . $project->getId()
                );
                $em->flush();
            }
            $this->addFlash('success', 'Traduction approuvée.');
        }
        return $this->redirectToRoute('app_project_review', ['id' => $project->getId()]);
    }

    #[Route('/{id}/review/{translationId}/reject', name: 'app_translation_reject', methods: ['POST'], requirements: ['id' => '\d+', 'translationId' => '\d+'])]
    #[IsGranted('ROLE_PROJECT_MANAGER')]
    public function reject(Project $project, int $translationId, Request $request, TranslationRepository $repo, EntityManagerInterface $em, NotificationService $notif): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $project->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $translation = $repo->findOneBy(['id' => $translationId, 'project' => $project]);
        if ($translation) {
            $comment = $request->request->get('comment', '') ?: null;
            $translation->setStatus(Translation::STATUS_REJECTED);
            $translation->setIsApproved(false);
            $translation->setReviewComment($comment);
            $em->flush();
            if ($translation->getTranslator()) {
                $msg = 'Votre traduction dans « ' . $project->getName() . ' » a été rejetée.';
                if ($comment) $msg .= ' Motif : ' . $comment;
                $notif->notify(
                    $translation->getTranslator(),
                    $msg,
                    '/projects/' . $project->getId() . '/translate/' . $translation->getLanguage()->getId()
                );
                $em->flush();
            }
            $this->addFlash('success', 'Traduction rejetée.');
        }
        return $this->redirectToRoute('app_project_review', ['id' => $project->getId()]);
    }

    // Éditeur traducteur
    #[Route('/{id}/translate/{languageId}', name: 'app_project_translate', requirements: ['id' => '\d+', 'languageId' => '\d+'])]
    public function translate(
        Request $request,
        Project $project,
        int $languageId,
        LanguageRepository $languageRepository,
        TranslationRepository $translationRepository,
        EntityManagerInterface $em,
        NotificationService $notif
    ): Response {
        $language = $languageRepository->find($languageId);
        if (!$language) {
            throw $this->createNotFoundException('Langue introuvable.');
        }

        $allTranslations = $translationRepository->findByProjectAndLanguage($project, $language);

        // Priorité : rejected d'abord, puis pending, puis submitted
        usort($allTranslations, function($a, $b) {
            $order = [
                Translation::STATUS_REJECTED  => 0,
                Translation::STATUS_PENDING   => 1,
                Translation::STATUS_SUBMITTED => 2,
                Translation::STATUS_APPROVED  => 3,
            ];
            return ($order[$a->getStatus()] ?? 9) <=> ($order[$b->getStatus()] ?? 9);
        });

        // Index de la chaîne courante
        $currentIndex = max(0, (int)$request->query->get('i', 0));
        $total = count($allTranslations);
        $currentIndex = min($currentIndex, max(0, $total - 1));

        $current = $allTranslations[$currentIndex] ?? null;

        if ($request->isMethod('POST') && $current && $current->getStatus() !== Translation::STATUS_APPROVED) {
            $action = $request->request->get('action', 'save');
            $text   = trim($request->request->get('translated_text', ''));

            // Empêcher re-soumission sans modification si rejeté
            if (in_array($action, ['submit', 'submit_next'])
                && $current->getStatus() === Translation::STATUS_REJECTED
                && $text === $current->getTranslatedText()
            ) {
                $this->addFlash('error', 'Vous devez modifier la traduction avant de la soumettre à nouveau.');
                return $this->redirectToRoute('app_project_translate', [
                    'id'         => $project->getId(),
                    'languageId' => $languageId,
                    'i'          => $currentIndex,
                ]);
            }

            $current->setTranslatedText($text ?: null);
            $current->setTranslator($this->getUser());

            $submitted = false;
            if (in_array($action, ['submit', 'submit_next']) && $text !== '') {
                $current->setStatus(Translation::STATUS_SUBMITTED);
                $submitted = true;
            } elseif ($text !== '') {
                if ($current->getStatus() === Translation::STATUS_PENDING || $current->getStatus() === Translation::STATUS_REJECTED) {
                    $current->setStatus(Translation::STATUS_PENDING);
                }
            }

            $em->flush();

            if ($submitted) {
                $translator = $this->getUser();
                $name = $translator->getUsername() ?? $translator->getEmail();
                $notif->notify(
                    $project->getOwner(),
                    $name . ' a soumis une traduction dans « ' . $project->getName() . ' » pour révision.',
                    '/projects/' . $project->getId() . '/review'
                );
                $em->flush();
            }

            $next = $currentIndex + 1;
            if ($action === 'submit_next' && $submitted) {
                $next = $currentIndex + 1 < $total ? $currentIndex + 1 : $currentIndex;
            }

            return $this->redirectToRoute('app_project_translate', [
                'id'         => $project->getId(),
                'languageId' => $languageId,
                'i'          => $action === 'save' ? $currentIndex : min($next, $total - 1),
            ]);
        }

        return $this->render('project/translate.html.twig', [
            'project'       => $project,
            'language'      => $language,
            'translations'  => $allTranslations,
            'current'       => $current,
            'currentIndex'  => $currentIndex,
            'total'         => $total,
        ]);
    }
}
