<?php

namespace App\Controller;

use App\Entity\Syllabus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SyllabusImportController extends AbstractController {
    
    private const ALLOWED_EXTENSIONS = ['pdf', 'txt'];
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    private const UPLOAD_DIR = 'uploads/syllabus';

    #[Route('/api/syllabus/import', name: 'import_syllabus', methods: ['POST'])]
    public function import(Request $request, EntityManagerInterface $em): Response {
        
        $this->denyAccessUnlessGranted('ROLE_TEACHER');

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['error' => 'Aucun fichier envoyé'], 400);
        }

        // Validation du fichier
        $validation = $this->validateFile($file);
        if ($validation['valid'] === false) {
            return $this->json(['error' => $validation['message']], 400);
        }

        try {
            // Générer un nom sécurisé
            $filename = uniqid() . '_' . time() . '.' . $file->guessExtension();
            
            // Vérifier que le répertoire existe
            if (!is_dir(self::UPLOAD_DIR)) {
                mkdir(self::UPLOAD_DIR, 0755, true);
            }

            // Déplacer le fichier
            $file->move(self::UPLOAD_DIR, $filename);
            $filepath = self::UPLOAD_DIR . '/' . $filename;

            // Extraire le texte selon le type
            $text = null;
            if ($file->guessExtension() === 'pdf') {
                $text = $this->extractPdfText($filepath);
            } else {
                $text = file_get_contents($filepath);
            }

            if ($text === null || empty($text)) {
                return $this->json(['error' => 'Impossible d\'extraire le texte du fichier'], 400);
            }

            // Créer et persister le Syllabus
            $syllabus = new Syllabus();
            $syllabus->setFilename($filename);
            $syllabus->setRawText($text);

            $em->persist($syllabus);
            $em->flush();

            return $this->json([
                'message' => 'Syllabus importé avec succès',
                'id' => $syllabus->getId(),
                'filename' => $filename,
                'text_length' => strlen($text)
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de l\'import: ' . $e->getMessage()], 500);
        }
    }

    private function validateFile(UploadedFile $file): array {
        $extension = strtolower($file->guessExtension());
        
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return ['valid' => false, 'message' => 'Format de fichier non autorisé (PDF ou TXT)'];
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return ['valid' => false, 'message' => 'Le fichier est trop volumineux (max 10MB)'];
        }

        return ['valid' => true];
    }

    private function extractPdfText(string $filepath): ?string {
        // Vérifier que pdftotext est disponible
        if (!shell_command_exists('pdftotext')) {
            throw new \RuntimeException('L\'outil pdftotext n\'est pas disponible');
        }

        // Utiliser escapeshellarg pour sécuriser la commande
        $command = 'pdftotext ' . escapeshellarg($filepath) . ' -';
        $text = shell_exec($command);
        
        return $text ?: null;
    }
}

if (!function_exists('shell_command_exists')) {
    function shell_command_exists(string $command): bool {
        $isWindows = strtolower(PHP_OS_FAMILY) === 'windows';
        $whereOrCommand = $isWindows ? 'where' : 'command -v';
        $return = shell_exec("$whereOrCommand $command 2>/dev/null");
        return !empty($return);
    }
}