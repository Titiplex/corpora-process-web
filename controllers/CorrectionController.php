<?php
require_once __DIR__ . '/../models/Correction.php';
require_once __DIR__ . '/../models/File.php';
require_once __DIR__ . '/../models/Corpus.php';
require_once __DIR__ . '/../config/Db.php';

use model\Corpus;
use model\File;
use model\Correction;

class CorrectionController
{

    // Action pour proposer une correction (via GET)
    /**
     * @throws Exception
     */
    public static function propose($fileId, $correctionText, $comment): void
    {
        $userId = $_SESSION['user_id'] ?? null;

        if (!$fileId || !$userId) {
            return;
        }
        echo "ici";

        // Récupération du fichier pour vérifier le propriétaire
        $file = File::selectById(Db::getConn(), $fileId);
        $accepted = false;
        $acceptedBy = null;
        $treatedAt = null;

        // Si l'utilisateur est propriétaire (uploaded_by), la correction est auto-acceptée
        if ((Corpus::findById(Db::getConn(), $file->getCorpusId()))->getCreatedBy() == $userId) {
            $accepted = true;
            $acceptedBy = $userId;
            $treatedAt = date('Y-m-d H:i:s');
        }

        $correction = new Correction(
            0,
            $fileId,
            $userId,
            $correctionText,
            $comment,
            date('Y-m-d H:i:s'),
            $accepted,
            $acceptedBy,
            $treatedAt
        );
        echo "maaaaarche";
        if ($correction->create(Db::getConn())) App::getLogger()->info("Correction proposée avec succès.");
        if ($accepted) {
            self::accept($correction->getId());
        }
    }

    // Action pour accepter une correction (accessible uniquement au propriétaire du fichier)

    /**
     * @throws Exception
     */
    public static function accept($id): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$id || !$userId) {
            echo "Correction ou utilisateur non défini.";
            return;
        }

        $correction = Correction::findById(Db::getConn(), $id);
        if (!$correction) {
            App::getLogger()->error("Correction $id introuvable.");
            return;
        }

        $file = File::selectById(Db::getConn(), $correction->getImageId());
        if ($file->getUploadedBy() != $userId) {
            echo "Vous n'êtes pas autorisé à traiter cette correction.";
            return;
        }

        $correction->setAccepted(true);
        $correction->setAcceptedBy($userId);
        $correction->setAcceptedAt(date('Y-m-d H:i:s'));
        $correction->update(Db::getConn());

        $file = File::selectById(Db::getConn(), $correction->getImageId());
        $file->setText($correction->getCorrection());
        var_dump($file);
        if ($file->update(Db::getConn())) App::getLogger()->info("File {$file->getId()} updated for correction!");
        else echo "PB";

        App::getLogger()->info("Correction $id acceptée.");
    }

    // Action pour refuser une correction (accessible uniquement au propriétaire)

    /**
     * @throws Exception
     */
    public static function refuse($id): void
    {

        $userId = $_SESSION['user_id'] ?? null;
        if (!$id || !$userId) {
            echo "Correction ou utilisateur non défini.";
            return;
        }

        $correction = Correction::findById(Db::getConn(), $id);
        if (!$correction) {
            echo "Correction introuvable.";
            return;
        }

        $file = File::selectById(Db::getConn(), $correction->getImageId());
        if ($file->getUploadedBy() != $userId) {
            echo "Vous n'êtes pas autorisé à traiter cette correction.";
            return;
        }

        $correction->setAccepted(false);
        $correction->setAcceptedBy($userId);
        $correction->setAcceptedAt(date('Y-m-d H:i:s'));
        $correction->update(Db::getConn());
        App::getLogger()->info("Correction $id refusée.");

    }

    // Affichage de l'historique des corrections (pour l'utilisateur courant)
    public static function history($id): array
    {
        return Correction::findAllByImageId(Db::getConn(), $id);
    }

    // Affichage des corrections en attente pour les fichiers possédés par l'utilisateur (propriétaire)
    public static function managePending($id): array
    {
        $pending = [];
        $all = Correction::findAllByImageId(Db::getConn(), $id);
        foreach ($all as $correction) {
            if ($correction->getAcceptedAt() != null) {
                $pending[] = $correction;
            }
        }
        return $pending;
    }
}