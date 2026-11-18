<?php

/*
 * This file is part of the Leaky Blog demo, based on the Symfony Demo application.
 */

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\AdminAuditLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_ADMIN)]
final class AuditLogController extends AbstractController
{
    #[Route('/admin/audit-log', name: 'admin_audit_log', methods: ['GET'])]
    public function index(AdminAuditLog $auditLog): Response
    {
        return $this->render('admin/audit_log.html.twig', ['entries' => $auditLog->entries()]);
    }
}
